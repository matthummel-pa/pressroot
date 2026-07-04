<?php

/**
 * "Connect with GitHub" using the OAuth Device Flow (no redirect URL, no client
 * secret). The user registers a GitHub OAuth App once (Device Flow enabled),
 * pastes the public Client ID, clicks Connect, authorizes on github.com, and the
 * resulting token is stored in prt_gh_token (used by the live repo data engine to
 * raise the API rate limit). Token is requested with NO scope = public read only.
 */

namespace App;

/**
 * Step 1 of the Device Flow: request a device_code + user_code pair from
 * GitHub for the configured OAuth App. The device_code and client_id are kept
 * server-side in a transient keyed by user ID (never sent to the browser);
 * only the short-lived user_code and verification URL go back to the admin
 * screen for the human to enter on github.com. `scope => ''` deliberately
 * requests no OAuth scopes — this integration only needs the higher
 * unauthenticated-vs-authenticated GitHub API rate limit, not write access.
 */
add_action('wp_ajax_prt_gh_device_start', function () {
    check_ajax_referer('prt_gh_connect', 'nonce');
    if (! current_user_can('manage_options')) {
        wp_send_json_error(['message' => __('Not allowed.', 'pressroot')]);
    }
    $client_id = trim((string) get_theme_mod('prt_gh_client_id', ''));
    if ($client_id === '') {
        wp_send_json_error(['message' => __('Enter your GitHub OAuth App Client ID and save first.', 'pressroot')]);
    }

    $r = wp_remote_post('https://github.com/login/device/code', [
        'timeout' => 15,
        'headers' => ['Accept' => 'application/json'],
        'body'    => ['client_id' => $client_id, 'scope' => ''],
    ]);
    if (is_wp_error($r)) {
        wp_send_json_error(['message' => $r->get_error_message()]);
    }
    $j = json_decode(wp_remote_retrieve_body($r), true);
    if (empty($j['device_code']) || empty($j['user_code'])) {
        $msg = $j['error_description'] ?? __('GitHub did not return a device code. Make sure Device Flow is enabled on your OAuth App.', 'pressroot');
        wp_send_json_error(['message' => $msg]);
    }

    // Keep the device_code server-side; the browser never needs it.
    set_transient('prt_gh_device_' . get_current_user_id(), [
        'device_code' => $j['device_code'],
        'client_id'   => $client_id,
        'interval'    => (int) ($j['interval'] ?? 5),
    ], (int) ($j['expires_in'] ?? 900));

    wp_send_json_success([
        'user_code'        => $j['user_code'],
        'verification_uri' => $j['verification_uri'] ?? 'https://github.com/login/device',
        'interval'         => (int) ($j['interval'] ?? 5),
        'expires_in'       => (int) ($j['expires_in'] ?? 900),
    ]);
});

/**
 * Step 2 of the Device Flow: called repeatedly by the admin-screen JS (see
 * prt_gh_connect_widget() below) while the user authorizes on github.com.
 * Exchanges the pending device_code for an access token once GitHub reports
 * authorization is complete; until then it echoes GitHub's "still waiting" /
 * "slow down" responses back to the poller as a success with a status field,
 * rather than as an error, so the JS polling loop keeps retrying instead of
 * giving up.
 */
add_action('wp_ajax_prt_gh_device_poll', function () {
    check_ajax_referer('prt_gh_connect', 'nonce');
    if (! current_user_can('manage_options')) {
        wp_send_json_error(['message' => __('Not allowed.', 'pressroot')]);
    }
    $pending = get_transient('prt_gh_device_' . get_current_user_id());
    if (! $pending) {
        wp_send_json_error(['message' => __('Connection expired. Start again.', 'pressroot')]);
    }

    $r = wp_remote_post('https://github.com/login/oauth/access_token', [
        'timeout' => 15,
        'headers' => ['Accept' => 'application/json'],
        'body'    => [
            'client_id'   => $pending['client_id'],
            'device_code' => $pending['device_code'],
            'grant_type'  => 'urn:ietf:params:oauth:grant-type:device_code',
        ],
    ]);
    if (is_wp_error($r)) {
        wp_send_json_error(['message' => $r->get_error_message()]);
    }
    $j = json_decode(wp_remote_retrieve_body($r), true);

    if (! empty($j['access_token'])) {
        set_theme_mod('prt_gh_token', sanitize_text_field($j['access_token']));
        delete_transient('prt_gh_device_' . get_current_user_id());
        wp_send_json_success(['status' => 'connected']);
    }

    $err = $j['error'] ?? 'authorization_pending';
    if ($err === 'authorization_pending') {
        wp_send_json_success(['status' => 'pending']);
    }
    if ($err === 'slow_down') {
        wp_send_json_success(['status' => 'pending', 'interval' => (int) ($j['interval'] ?? 10)]);
    }
    // expired_token, access_denied, etc.
    delete_transient('prt_gh_device_' . get_current_user_id());
    wp_send_json_error(['message' => $j['error_description'] ?? $err]);
});

/**
 * Disconnect: clear the stored token and any in-flight device-flow transient.
 * Doesn't call GitHub to revoke the token remotely — it just stops this site
 * from using it, which is enough since the token was scope-less to begin with.
 */
add_action('wp_ajax_prt_gh_disconnect', function () {
    check_ajax_referer('prt_gh_connect', 'nonce');
    if (! current_user_can('manage_options')) {
        wp_send_json_error(['message' => __('Not allowed.', 'pressroot')]);
    }
    set_theme_mod('prt_gh_token', '');
    delete_transient('prt_gh_device_' . get_current_user_id());
    wp_send_json_success(['status' => 'disconnected']);
});

/**
 * Renders the connection widget (called from the admin Projects tab): shows
 * connected state + Disconnect button, or the Connect button plus the setup
 * instructions and a code/status panel that the inline script below fills in
 * as the device flow progresses. The JS here (rather than a separate enqueued
 * asset) drives the whole start -> poll -> reload loop against the AJAX
 * actions registered above, since this widget is only ever rendered once per
 * page and doesn't need a shared/cacheable script file.
 */
function prt_gh_connect_widget()
{
    $connected = trim((string) get_theme_mod('prt_gh_token', '')) !== '';
    $client_id = trim((string) get_theme_mod('prt_gh_client_id', ''));
    $nonce     = wp_create_nonce('prt_gh_connect');
    $ajax      = admin_url('admin-ajax.php');
    ?>
    <div class="prt-gh-connect" data-nonce="<?php echo esc_attr($nonce); ?>" data-ajax="<?php echo esc_url($ajax); ?>">
        <?php if ($connected) : ?>
            <p class="prt-gh-state is-on"><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Connected to GitHub. The live repo data is using your authenticated rate limit.', 'pressroot'); ?></p>
            <button type="button" class="button prt-gh-disconnect"><?php esc_html_e('Disconnect', 'pressroot'); ?></button>
        <?php else : ?>
            <p class="description" style="margin-bottom:8px">
                <?php esc_html_e('One-time setup: on GitHub, go to Settings → Developer settings → OAuth Apps → New, tick "Enable Device Flow", then paste the Client ID above and save. Then click Connect.', 'pressroot'); ?>
            </p>
            <button type="button" class="button button-primary prt-gh-start" <?php disabled($client_id === ''); ?>><span class="dashicons dashicons-github" style="vertical-align:text-bottom"></span> <?php esc_html_e('Connect with GitHub', 'pressroot'); ?></button>
            <?php if ($client_id === '') : ?><span class="description" style="margin-left:8px"><?php esc_html_e('Enter a Client ID above and save first.', 'pressroot'); ?></span><?php endif; ?>
            <div class="prt-gh-step" style="display:none;margin-top:12px;padding:14px;border:1px solid #dcdcde;border-radius:8px;background:#fff">
                <p style="margin:0 0 6px"><?php esc_html_e('1. Your one-time code:', 'pressroot'); ?> <strong class="prt-gh-code" style="font-size:20px;letter-spacing:2px"></strong></p>
                <p style="margin:0 0 6px"><?php esc_html_e('2. Authorize at', 'pressroot'); ?> <a class="prt-gh-uri" href="#" target="_blank" rel="noopener">github.com/login/device</a></p>
                <p class="prt-gh-status" style="margin:0;color:#646970"><?php esc_html_e('Waiting for authorization…', 'pressroot'); ?></p>
            </div>
        <?php endif; ?>
        <p class="prt-gh-error" style="color:#b32d2e;margin-top:8px;display:none"></p>
    </div>
    <script>
    (function(){
      var box = document.currentScript.previousElementSibling;
      if(!box || !box.classList.contains('prt-gh-connect')) box = document.querySelector('.prt-gh-connect');
      if(!box) return;
      var ajax = box.getAttribute('data-ajax'), nonce = box.getAttribute('data-nonce');
      function post(action, extra){
        var d = new FormData(); d.append('action', action); d.append('nonce', nonce);
        if(extra){ Object.keys(extra).forEach(function(k){ d.append(k, extra[k]); }); }
        return fetch(ajax, {method:'POST', credentials:'same-origin', body:d}).then(function(r){return r.json();});
      }
      function err(m){ var e=box.querySelector('.prt-gh-error'); if(e){ e.textContent=m; e.style.display='block'; } }
      var start = box.querySelector('.prt-gh-start');
      if(start) start.addEventListener('click', function(){
        err(''); start.disabled = true; start.textContent = 'Starting…';
        post('prt_gh_device_start').then(function(res){
          if(!res.success){ err(res.data && res.data.message || 'Failed'); start.disabled=false; start.textContent='Connect with GitHub'; return; }
          var step = box.querySelector('.prt-gh-step'); step.style.display='block';
          box.querySelector('.prt-gh-code').textContent = res.data.user_code;
          var uri = box.querySelector('.prt-gh-uri'); uri.href = res.data.verification_uri; uri.textContent = res.data.verification_uri;
          window.open(res.data.verification_uri, '_blank');
          var interval = (res.data.interval || 5) * 1000;
          var poll = function(){
            post('prt_gh_device_poll').then(function(p){
              if(p.success && p.data.status === 'connected'){ box.querySelector('.prt-gh-status').textContent='Connected! Reloading…'; setTimeout(function(){ location.reload(); }, 800); return; }
              if(p.success && p.data.status === 'pending'){ if(p.data.interval) interval = p.data.interval*1000; setTimeout(poll, interval); return; }
              err(p.data && p.data.message || 'Authorization failed'); start.disabled=false; start.textContent='Connect with GitHub'; step.style.display='none';
            });
          };
          setTimeout(poll, interval);
        });
      });
      var dc = box.querySelector('.prt-gh-disconnect');
      if(dc) dc.addEventListener('click', function(){ dc.disabled=true; post('prt_gh_disconnect').then(function(){ location.reload(); }); });
    })();
    </script>
    <?php
}
