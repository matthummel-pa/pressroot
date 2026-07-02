<?php

/**
 * Off-canvas popout menu + social icon system.
 * - Hamburger toggle, shown per breakpoint (desktop/tablet/mobile).
 * - Slide-in panel with solid or gradient background + text color.
 * - Social icons are rendered as inline SVGs via Blade Icons (Simple Icons),
 *   each with a custom URL. (Font Awesome removed.)
 */

namespace App;

/** Available social/icon networks: key => [label, default url]. */
function prt_socials_map()
{
    return [
        'linkedin'  => ['LinkedIn', 'https://www.linkedin.com/in/matthummel'],
        'github'    => ['GitHub', 'https://github.com/matthummel-pa'],
        'devto'     => ['Dev.to', 'https://dev.to/mattbuildsapps'],
        'x'         => ['X', ''],
        'bluesky'   => ['Bluesky', ''],
        'youtube'   => ['YouTube', ''],
        'instagram' => ['Instagram', ''],
        'facebook'  => ['Facebook', ''],
        'mastodon'  => ['Mastodon', ''],
        'email'     => ['Email', ''],
        'rss'       => ['RSS', ''],
    ];
}

/** Resolved list of social links the user has filled in. */
function prt_social_links()
{
    $out = [];
    foreach (prt_socials_map() as $key => $info) {
        $url = get_theme_mod("prt_social_{$key}", $info[1]);
        if (! empty($url)) {
            $out[] = [
                'key'   => $key,
                'label' => $info[0],
                'url'   => $url,
                'icon'  => prt_social_icon_name($key),
            ];
        }
    }
    return $out;
}

/** Resolved popout appearance. */
function prt_popout()
{
    $type = get_theme_mod('prt_popout_bgtype', 'solid');
    $c1   = get_theme_mod('prt_popout_bg', '#1B1830');
    $c2   = get_theme_mod('prt_popout_grad2', '#7C5CFF');
    $ang  = absint(get_theme_mod('prt_popout_angle', 160));
    $bg   = $type === 'gradient' ? "linear-gradient({$ang}deg, {$c1}, {$c2})" : $c1;

    return [
        'bg'   => $bg,
        'text' => get_theme_mod('prt_popout_text', '#ffffff'),
    ];
}

/** Which breakpoints show the hamburger instead of the inline nav. */
add_filter('body_class', function ($c) {
    if (get_theme_mod('prt_popout_desktop', false)) {
        $c[] = 'prt-ham-desktop';
    }
    if (get_theme_mod('prt_popout_tablet', true)) {
        $c[] = 'prt-ham-tablet';
    }
    if (get_theme_mod('prt_popout_mobile', true)) {
        $c[] = 'prt-ham-mobile';
    }
    return $c;
});

/** Customizer: Menu & Popout. */
add_action('customize_register', function ($wp) {
    if (! $wp->get_panel('prt_theme_options')) {
        $wp->add_panel('prt_theme_options', ['title' => __('Theme Options', 'pressroot'), 'priority' => 30]);
    }

    $wp->add_section('prt_popout_section', [
        'title'       => __('Menu & Popout', 'pressroot'),
        'panel'       => 'prt_theme_options',
        'description' => __('Replace the inline nav with a menu icon that opens an off-canvas panel. Choose where it appears, its colors, and your social icons.', 'pressroot'),
    ]);

    // Breakpoints
    foreach ([['prt_popout_desktop', __('Use menu icon on desktop', 'pressroot'), false], ['prt_popout_tablet', __('Use menu icon on tablet', 'pressroot'), true], ['prt_popout_mobile', __('Use menu icon on mobile', 'pressroot'), true]] as $bp) {
        $wp->add_setting($bp[0], ['default' => $bp[2], 'sanitize_callback' => 'wp_validate_boolean']);
        $wp->add_control($bp[0], ['label' => $bp[1], 'section' => 'prt_popout_section', 'type' => 'checkbox']);
    }

    // Background type
    $wp->add_setting('prt_popout_bgtype', ['default' => 'solid', 'sanitize_callback' => 'sanitize_key']);
    $wp->add_control('prt_popout_bgtype', ['label' => __('Panel background', 'pressroot'), 'section' => 'prt_popout_section', 'type' => 'select', 'choices' => ['solid' => __('Solid', 'pressroot'), 'gradient' => __('Gradient', 'pressroot')]]);

    foreach ([['prt_popout_bg', __('Background / gradient start', 'pressroot'), '#1B1830'], ['prt_popout_grad2', __('Gradient end', 'pressroot'), '#7C5CFF'], ['prt_popout_text', __('Text / icon color', 'pressroot'), '#ffffff']] as $col) {
        $wp->add_setting($col[0], ['default' => $col[2], 'sanitize_callback' => 'sanitize_hex_color']);
        $wp->add_control(new \WP_Customize_Color_Control($wp, $col[0], ['label' => $col[1], 'section' => 'prt_popout_section']));
    }

    $wp->add_setting('prt_popout_angle', ['default' => 160, 'sanitize_callback' => 'absint']);
    $wp->add_control('prt_popout_angle', ['label' => __('Gradient angle (deg)', 'pressroot'), 'section' => 'prt_popout_section', 'type' => 'number', 'input_attrs' => ['min' => 0, 'max' => 360, 'step' => 5]]);

    // Social URL fields — each shows its Blade icon next to the network name.
    foreach (prt_socials_map() as $key => $info) {
        $preview = prt_social_icon($key, 'prt-soc-admin', ['width' => 16, 'height' => 16]);
        $wp->add_setting("prt_social_{$key}", ['default' => $info[1], 'sanitize_callback' => 'esc_url_raw']);
        $wp->add_control("prt_social_{$key}", [
            'label'       => sprintf(__('%s URL', 'pressroot'), $info[0]),
            'description' => '<span class="prt-soc-admin-row" style="display:inline-flex;align-items:center;gap:6px;color:#50575e">' . $preview . esc_html($info[0]) . '</span>',
            'section'     => 'prt_popout_section',
            'type'        => 'url',
        ]);
    }
}, 22);

/** Inline toggle JS (no build needed). */
add_action('wp_footer', function () {
    ?>
    <script>
    (function(){
      var t = document.querySelector('.menu-toggle');
      if (!t) return;
      var b = document.body;
      var overlay = document.querySelector('.prt-popout-overlay');
      var closeBtn = document.querySelector('.prt-popout-close');
      function open(){ b.classList.add('prt-popout-open'); t.setAttribute('aria-expanded','true'); var p=document.getElementById('prt-popout'); if(p){var f=p.querySelector('a,button'); if(f) f.focus();} }
      function close(){ b.classList.remove('prt-popout-open'); t.setAttribute('aria-expanded','false'); t.focus(); }
      t.addEventListener('click', function(){ b.classList.contains('prt-popout-open') ? close() : open(); });
      if (overlay) overlay.addEventListener('click', close);
      if (closeBtn) closeBtn.addEventListener('click', close);
      document.addEventListener('keydown', function(e){ if((e.key==='Escape'||e.keyCode===27) && b.classList.contains('prt-popout-open')) close(); });
    })();
    </script>
    <?php
}, 50);
