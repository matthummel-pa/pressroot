<?php

/**
 * Full admin "Theme Settings" page â€” premium-style tabbed options panel.
 * Reads/writes the SAME theme mods the Customizer uses (single source of truth).
 */

namespace App;

/** Combined defaults from the Customizer + layout modules. */
function prt_admin_defaults()
{
    $base = function_exists('App\\prt_defaults') ? prt_defaults() : [];
    if (function_exists('App\\prt_layout_defaults')) {
        foreach (prt_layout_defaults() as $t => $v) {
            $base["prt_layout_{$t}_width"]    = $v['width'];
            $base["prt_layout_{$t}_maxwidth"] = $v['maxwidth'];
            $base["prt_layout_{$t}_sidebar"]  = $v['sidebar'];
        }
    }
    $base = array_merge($base, [
        'prt_topbar_enable' => false, 'prt_topbar_contact' => '', 'prt_topbar_show_social' => true,
        'prt_topbar_cta_text' => '', 'prt_topbar_cta_url' => '', 'prt_topbar_bg' => 'ink', 'prt_topbar_text' => 'white',
        'prt_nav_fullwidth' => false, 'prt_header_width' => 1180, 'prt_header_height' => 0, 'prt_header_gap' => 28,
        'prt_logo_order' => 1, 'prt_nav_order' => 2, 'prt_social_order' => 3, 'prt_cta_order' => 4,
        'prt_popout_desktop' => false, 'prt_popout_tablet' => true, 'prt_popout_mobile' => true,
        'prt_footer_social' => true, 'prt_footer_cols' => 3, 'prt_footer_bg' => 'paper', 'prt_footer_bg_custom' => '',
        'prt_footer_textc' => 'body', 'prt_footer_text_custom' => '',
        'prt_proj_owner' => 'matthummel-pa', 'prt_gh_token' => '', 'prt_proj_cache_hours' => 6, 'prt_gh_client_id' => '',
    ]);
    return $base;
}

function prt_admin_get($key)
{
    $d = prt_admin_defaults();
    return get_theme_mod($key, $d[$key] ?? '');
}

/** Tabbed field schema. */
function prt_admin_schema()
{
    $fonts       = function_exists('App\\prt_fonts') ? array_keys(prt_fonts()) : ['Space Grotesk', 'Inter'];
    $fontChoices = array_combine($fonts, $fonts);
    $w           = function_exists('App\\prt_width_choices') ? prt_width_choices() : ['default' => 'Default'];
    $labels      = function_exists('App\\prt_layout_labels') ? prt_layout_labels() : ['page' => 'Pages'];

    $layoutFields = [];
    foreach ($labels as $t => $lab) {
        $layoutFields[] = ['key' => "prt_layout_{$t}_width", 'label' => "{$lab} â€” width preset", 'type' => 'select', 'choices' => $w];
        $layoutFields[] = ['key' => "prt_layout_{$t}_maxwidth", 'label' => "{$lab} â€” custom width", 'type' => 'select', 'choices' => prt_width_options(true)];
        $layoutFields[] = ['key' => "prt_layout_{$t}_sidebar", 'label' => "{$lab} â€” show sidebar", 'type' => 'checkbox'];
    }

    return [
        'general' => ['icon' => 'dashicons-admin-settings', 'label' => __('General', 'pressroot'), 'fields' => [
            ['key' => 'prt_cta_text', 'label' => __('Header button text', 'pressroot'), 'type' => 'text'],
            ['key' => 'prt_cta_url', 'label' => __('Header button URL', 'pressroot'), 'type' => 'text'],
            ['key' => 'prt_show_cta', 'label' => __('Show header button', 'pressroot'), 'type' => 'checkbox'],
            ['key' => 'prt_footer_text', 'label' => __('Footer tagline', 'pressroot'), 'type' => 'textarea'],
        ]],
        'design' => ['icon' => 'dashicons-art', 'label' => __('Design', 'pressroot'), 'fields' => [
            ['key' => 'prt_color_action', 'label' => __('Brand / buttons', 'pressroot'), 'type' => 'color'],
            ['key' => 'prt_color_paper', 'label' => __('Background', 'pressroot'), 'type' => 'color'],
            ['key' => 'prt_color_ink', 'label' => __('Headings', 'pressroot'), 'type' => 'color'],
            ['key' => 'prt_color_body', 'label' => __('Body text', 'pressroot'), 'type' => 'color'],
            ['key' => 'prt_font_heading', 'label' => __('Heading font', 'pressroot'), 'type' => 'select', 'choices' => $fontChoices],
            ['key' => 'prt_font_body', 'label' => __('Body font', 'pressroot'), 'type' => 'select', 'choices' => $fontChoices],
            ['key' => 'prt_container', 'label' => __('Default content width', 'pressroot'), 'type' => 'select', 'choices' => prt_width_options()],
        ]],
        'layout' => ['icon' => 'dashicons-screenoptions', 'label' => __('Layout', 'pressroot'), 'fields' => $layoutFields],
        'header' => ['icon' => 'dashicons-editor-kitchensink', 'label' => __('Header', 'pressroot'), 'fields' => [
            ['key' => 'prt_nav_fullwidth', 'label' => __('Full-width menu', 'pressroot'), 'type' => 'checkbox'],
            ['key' => 'prt_header_width', 'label' => __('Header width', 'pressroot'), 'type' => 'select', 'choices' => prt_width_options()],
            ['key' => 'prt_header_height', 'label' => __('Header height (px, 0 = auto)', 'pressroot'), 'type' => 'number'],
            ['key' => 'prt_header_gap', 'label' => __('Header gap (px)', 'pressroot'), 'type' => 'number'],
            ['key' => 'prt_logo_order', 'label' => __('Logo position', 'pressroot'), 'type' => 'number'],
            ['key' => 'prt_nav_order', 'label' => __('Menu position', 'pressroot'), 'type' => 'number'],
            ['key' => 'prt_social_order', 'label' => __('Social links position', 'pressroot'), 'type' => 'number'],
            ['key' => 'prt_cta_order', 'label' => __('Button position', 'pressroot'), 'type' => 'number'],
            ['key' => 'prt_topbar_enable', 'label' => __('Enable top bar', 'pressroot'), 'type' => 'checkbox'],
            ['key' => 'prt_topbar_contact', 'label' => __('Top bar contact text', 'pressroot'), 'type' => 'text'],
            ['key' => 'prt_topbar_show_social', 'label' => __('Top bar social links', 'pressroot'), 'type' => 'checkbox'],
            ['key' => 'prt_topbar_cta_text', 'label' => __('Top bar button text', 'pressroot'), 'type' => 'text'],
            ['key' => 'prt_topbar_cta_url', 'label' => __('Top bar button URL', 'pressroot'), 'type' => 'text'],
            ['key' => 'prt_topbar_bg', 'label' => __('Top bar background', 'pressroot'), 'type' => 'select', 'choices' => prt_palette_choices()],
            ['key' => 'prt_topbar_text', 'label' => __('Top bar text color', 'pressroot'), 'type' => 'select', 'choices' => prt_palette_choices()],
            ['key' => 'prt_popout_desktop', 'label' => __('Menu icon on desktop', 'pressroot'), 'type' => 'checkbox'],
            ['key' => 'prt_popout_tablet', 'label' => __('Menu icon on tablet', 'pressroot'), 'type' => 'checkbox'],
            ['key' => 'prt_popout_mobile', 'label' => __('Menu icon on mobile', 'pressroot'), 'type' => 'checkbox'],
        ]],
        'footer' => ['icon' => 'dashicons-editor-insertmore', 'label' => __('Footer', 'pressroot'), 'fields' => [
            ['key' => 'prt_footer_social', 'label' => __('Show social icons in footer', 'pressroot'), 'type' => 'checkbox'],
            ['key' => 'prt_footer_cols', 'label' => __('Footer columns', 'pressroot'), 'type' => 'select', 'choices' => ['1' => '1', '2' => '2', '3' => '3', '4' => '4']],
            ['key' => 'prt_footer_bg', 'label' => __('Footer background', 'pressroot'), 'type' => 'select', 'choices' => prt_palette_choices()],
            ['key' => 'prt_footer_bg_custom', 'label' => __('Footer background (custom hex)', 'pressroot'), 'type' => 'color'],
            ['key' => 'prt_footer_textc', 'label' => __('Footer text color', 'pressroot'), 'type' => 'select', 'choices' => prt_palette_choices()],
            ['key' => 'prt_footer_text_custom', 'label' => __('Footer text (custom hex)', 'pressroot'), 'type' => 'color'],
            ['key' => 'prt_footer_text', 'label' => __('Footer tagline', 'pressroot'), 'type' => 'textarea'],
        ], 'note' => __('Footer columns map to block widget areas under Appearance > Widgets.', 'pressroot')],
        'projects' => ['icon' => 'dashicons-portfolio', 'label' => __('Projects', 'pressroot'), 'fields' => [
            ['key' => 'prt_proj_owner', 'label' => __('Default GitHub owner', 'pressroot'), 'type' => 'text', 'desc' => __('Used for the live repo data when a project has no owner set.', 'pressroot')],
            ['key' => 'prt_gh_token', 'label' => __('GitHub API token (optional)', 'pressroot'), 'type' => 'text', 'desc' => __('A read-only token raises the GitHub API rate limit. Stored as a theme setting.', 'pressroot')],
            ['key' => 'prt_proj_cache_hours', 'label' => __('GitHub data cache (hours)', 'pressroot'), 'type' => 'number'],
            ['key' => 'prt_gh_client_id', 'label' => __('GitHub OAuth Client ID', 'pressroot'), 'type' => 'text', 'desc' => __('Public Client ID from your GitHub OAuth App (Device Flow enabled). Needed for "Connect with GitHub".', 'pressroot')],
            ['key' => 'prt_gh_connect', 'label' => __('GitHub connection', 'pressroot'), 'type' => 'github_connect'],
        ], 'note' => __('Per-project owner/repo, eyebrow and demo URL are set on each project via the Project Details box.', 'pressroot')],
    ];
}

/** Top-level admin menu. */
add_action('admin_menu', function () {
    add_menu_page(
        __('Theme Settings', 'pressroot'),
        __('Theme Settings', 'pressroot'),
        'manage_options',
        'prt-theme-settings',
        'App\\prt_render_settings_page',
        'dashicons-admin-customizer',
        59
    );
});

/** Color picker on our page only. */
add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'toplevel_page_prt-theme-settings') {
        return;
    }
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    wp_add_inline_script('wp-color-picker', 'jQuery(function($){$(".prt-color").wpColorPicker();});');
});

function prt_render_field($f)
{
    $key  = $f['key'];
    $type = $f['type'];
    $val  = prt_admin_get($key);
    echo '<div class="prt-field prt-field-' . esc_attr($type) . '">';
    echo '<label for="' . esc_attr($key) . '">' . esc_html($f['label']) . '</label>';
    echo '<div class="prt-field-control">';
    switch ($type) {
        case 'github_connect':
            if (function_exists('App\\prt_gh_connect_widget')) {
                prt_gh_connect_widget();
            }
            break;
        case 'textarea':
            echo '<textarea id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" rows="3">' . esc_textarea($val) . '</textarea>';
            break;
        case 'checkbox':
            echo '<label class="prt-switch"><input type="checkbox" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" value="1" ' . checked((bool) $val, true, false) . '> <span>' . esc_html__('Enabled', 'pressroot') . '</span></label>';
            break;
        case 'select':
            echo '<select id="' . esc_attr($key) . '" name="' . esc_attr($key) . '">';
            foreach ($f['choices'] as $k => $lab) {
                echo '<option value="' . esc_attr($k) . '" ' . selected($val, $k, false) . '>' . esc_html($lab) . '</option>';
            }
            echo '</select>';
            break;
        case 'color':
            echo '<input type="text" class="prt-color" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" value="' . esc_attr($val) . '">';
            break;
        case 'number':
            echo '<input type="number" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" value="' . esc_attr($val) . '" min="0" step="10">';
            break;
        default:
            echo '<input type="text" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" value="' . esc_attr($val) . '">';
    }
    if (! empty($f['desc'])) {
        echo '<p class="prt-field-desc">' . esc_html($f['desc']) . '</p>';
    }
    echo '</div></div>';
}

function prt_render_settings_page()
{
    if (! current_user_can('manage_options')) {
        return;
    }
    $schema = prt_admin_schema();
    $first  = array_key_first($schema);
    ?>
    <style>
      .prt-admin{max-width:1120px}
      .prt-admin-head{display:flex;align-items:center;gap:16px;background:#fff;border:1px solid #e2e2e2;border-radius:12px;padding:18px 22px;margin:20px 0}
      .prt-admin-logo svg{width:44px;height:44px;display:block}
      .prt-admin-head h1{margin:0;font-size:20px;padding:0}
      .prt-admin-head p{margin:2px 0 0;color:#646970}
      .prt-admin-head .button{margin-left:auto}
      .prt-admin-body{display:grid;grid-template-columns:220px 1fr;gap:20px;align-items:start}
      .prt-admin-tabs{display:flex;flex-direction:column;gap:4px;background:#fff;border:1px solid #e2e2e2;border-radius:12px;padding:10px;position:sticky;top:46px}
      .prt-tab-btn{display:flex;align-items:center;gap:8px;text-align:left;background:none;border:0;padding:10px 12px;border-radius:8px;cursor:pointer;font-size:14px;color:#1d2327}
      .prt-tab-btn:hover{background:#f0f0f1}
      .prt-tab-btn.is-active{background:#2f6b4e;color:#fff}
      .prt-tab-btn.is-active .dashicons{color:#fff}
      .prt-admin-form{background:#fff;border:1px solid #e2e2e2;border-radius:12px;padding:8px 24px 24px}
      .prt-tab-panel{display:none}
      .prt-tab-panel.is-active{display:block}
      .prt-tab-panel>h2{font-size:18px;margin:18px 0 4px}
      .prt-note{background:#f6f7f7;border-left:3px solid #2f6b4e;padding:10px 14px;color:#50575e;border-radius:0 6px 6px 0}
      .prt-field{display:grid;grid-template-columns:230px 1fr;gap:16px;align-items:start;padding:14px 0;border-bottom:1px solid #f0f0f1}
      .prt-field>label{font-weight:600;padding-top:6px}
      .prt-field-control input[type=text],.prt-field-control input[type=number],.prt-field-control select,.prt-field-control textarea{width:100%;max-width:430px}
      .prt-field-desc{color:#646970;font-size:12px;margin:6px 0 0}
      .prt-admin-save{margin-top:18px}
      .prt-switch{display:inline-flex;align-items:center;gap:8px}
      @media(max-width:782px){.prt-admin-body{grid-template-columns:1fr}.prt-admin-tabs{flex-direction:row;flex-wrap:wrap;position:static}}
    </style>

    <div class="wrap prt-admin">
      <div class="prt-admin-head">
        <span class="prt-admin-logo" aria-hidden="true">
          <svg viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg"><rect width="120" height="120" rx="30" fill="#2f6b4e"/><text x="60" y="80" text-anchor="middle" fill="#fff" font-family="'Space Grotesk',Arial,sans-serif" font-size="56" font-weight="700">MH</text></svg>
        </span>
        <div>
          <h1><?php esc_html_e('Theme Settings', 'pressroot'); ?></h1>
          <p><?php esc_html_e('Matt Hummel â€” Sage theme options', 'pressroot'); ?></p>
        </div>
        <a class="button" href="<?php echo esc_url(admin_url('customize.php')); ?>"><?php esc_html_e('Open Customizer', 'pressroot'); ?></a>
      </div>

      <?php if (isset($_GET['updated'])) : ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Settings saved.', 'pressroot'); ?></p></div>
      <?php endif; ?>

      <div class="prt-admin-body">
        <nav class="prt-admin-tabs" aria-label="<?php esc_attr_e('Settings sections', 'pressroot'); ?>">
          <?php foreach ($schema as $id => $tab) : ?>
            <button type="button" class="prt-tab-btn <?php echo $id === $first ? 'is-active' : ''; ?>" data-tab="<?php echo esc_attr($id); ?>">
              <span class="dashicons <?php echo esc_attr($tab['icon']); ?>"></span> <?php echo esc_html($tab['label']); ?>
            </button>
          <?php endforeach; ?>
        </nav>

        <form class="prt-admin-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
          <input type="hidden" name="action" value="prt_save_theme_settings">
          <?php wp_nonce_field('prt_save_theme_settings'); ?>
          <?php foreach ($schema as $id => $tab) : ?>
            <section class="prt-tab-panel <?php echo $id === $first ? 'is-active' : ''; ?>" data-tab="<?php echo esc_attr($id); ?>">
              <h2><?php echo esc_html($tab['label']); ?></h2>
              <?php if (! empty($tab['note'])) : ?><p class="prt-note"><?php echo esc_html($tab['note']); ?></p><?php endif; ?>
              <?php foreach ($tab['fields'] as $f) {
                  prt_render_field($f);
              } ?>
            </section>
          <?php endforeach; ?>
          <p class="prt-admin-save"><button class="button button-primary button-large"><?php esc_html_e('Save changes', 'pressroot'); ?></button></p>
        </form>
      </div>
    </div>

    <script>
      (function(){
        var btns = document.querySelectorAll('.prt-tab-btn');
        var panels = document.querySelectorAll('.prt-tab-panel');
        btns.forEach(function(b){
          b.addEventListener('click', function(){
            var t = b.getAttribute('data-tab');
            btns.forEach(function(x){ x.classList.toggle('is-active', x === b); });
            panels.forEach(function(p){ p.classList.toggle('is-active', p.getAttribute('data-tab') === t); });
          });
        });
      })();
    </script>
    <?php
}

/** Save handler. */
add_action('admin_post_prt_save_theme_settings', function () {
    if (! current_user_can('manage_options')) {
        wp_die(__('You do not have permission to do this.', 'pressroot'));
    }
    check_admin_referer('prt_save_theme_settings');

    foreach (prt_admin_schema() as $tab) {
        foreach ($tab['fields'] as $f) {
            $key  = $f['key'];
            $type = $f['type'];
            if ($type === 'github_connect') {
                continue;
            }
            if ($type === 'checkbox') {
                set_theme_mod($key, isset($_POST[$key]));
                continue;
            }
            $raw = isset($_POST[$key]) ? wp_unslash($_POST[$key]) : '';
            switch ($type) {
                case 'color':
                    $val = sanitize_hex_color($raw);
                    break;
                case 'number':
                    $val = absint($raw);
                    break;
                case 'textarea':
                    $val = wp_kses_post($raw);
                    break;
                case 'select':
                    $val = sanitize_text_field($raw);
                    break;
                default:
                    $val = (substr($key, -4) === '_url') ? esc_url_raw($raw) : sanitize_text_field($raw);
            }
            set_theme_mod($key, $val);
        }
    }

    wp_safe_redirect(add_query_arg(['page' => 'prt-theme-settings', 'updated' => '1'], admin_url('admin.php')));
    exit;
});
