<?php

/**
 * Theme Tools: Style Kit presets + Import / Export / Reset of all theme settings.
 * Appearance -> Theme Tools. All actions are nonce-protected and capability-gated.
 */

namespace App;

/** Every theme mod that belongs to this theme (prt_* keys). */
function prt_owned_mods()
{
    $mods = get_theme_mods() ?: [];
    $out  = [];
    foreach ($mods as $k => $v) {
        if (strpos($k, 'prt_') === 0) {
            $out[$k] = $v;
        }
    }
    return $out;
}

/** One-click design presets (palette + fonts + radius). */
function prt_style_kits()
{
    return apply_filters('matthummel/style_kits', [
        'paper_space' => [
            'label' => __('Paper + Space', 'pressroot'),
            'desc'  => __('Bold paper + purple/orange/lime with Outfit. The theme default.', 'pressroot'),
            'mods'  => [
                'prt_color_action' => '#7C5CFF', 'prt_color_paper' => '#FFFDF7',
                'prt_color_ink' => '#1B1830', 'prt_color_body' => '#4A4660',
                'prt_font_heading' => 'Outfit', 'prt_font_body' => 'Outfit',
                'prt_btn_radius' => '999', 'prt_card_radius' => '20',
            ],
        ],
        'editorial' => [
            'label' => __('Editorial (green)', 'pressroot'),
            'desc'  => __('Clean paper + green — the previous theme default.', 'pressroot'),
            'mods'  => [
                'prt_color_action' => '#2f6b4e', 'prt_color_paper' => '#fbfaf7',
                'prt_color_ink' => '#17191e', 'prt_color_body' => '#2b2f36',
                'prt_font_heading' => 'Geist', 'prt_font_body' => 'Inter',
                'prt_btn_radius' => '8', 'prt_card_radius' => '16',
            ],
        ],
        'sage_classic' => [
            'label' => __('Sage Classic', 'pressroot'),
            'desc'  => __('Khaki + serif — matches matthummel.com.', 'pressroot'),
            'mods'  => [
                'prt_color_action' => '#4e6b4a', 'prt_color_paper' => '#dccfa6',
                'prt_color_ink' => '#2a303b', 'prt_color_body' => '#3a3d44',
                'prt_font_heading' => 'Fraunces', 'prt_font_body' => 'Inter',
                'prt_btn_radius' => '4', 'prt_card_radius' => '14',
            ],
        ],
        'warm_sand' => [
            'label' => __('Warm Sand', 'pressroot'),
            'desc'  => __('Terracotta + warm neutrals.', 'pressroot'),
            'mods'  => [
                'prt_color_action' => '#b4612f', 'prt_color_paper' => '#faf6ef',
                'prt_color_ink' => '#2a2422', 'prt_color_body' => '#44403c',
                'prt_font_heading' => 'Fraunces', 'prt_font_body' => 'Inter',
                'prt_btn_radius' => '12', 'prt_card_radius' => '18',
            ],
        ],
        'midnight' => [
            'label' => __('Midnight', 'pressroot'),
            'desc'  => __('Dark canvas, soft blue accent.', 'pressroot'),
            'mods'  => [
                'prt_color_action' => '#6ea8fe', 'prt_color_paper' => '#0f1420',
                'prt_color_ink' => '#f5f7fb', 'prt_color_body' => '#c7ccd6',
                'prt_font_heading' => 'Space Grotesk', 'prt_font_body' => 'Inter',
                'prt_btn_radius' => '8', 'prt_card_radius' => '16',
            ],
        ],
        'mono_slate' => [
            'label' => __('Mono Slate', 'pressroot'),
            'desc'  => __('Sharp, near-black, minimal.', 'pressroot'),
            'mods'  => [
                'prt_color_action' => '#111827', 'prt_color_paper' => '#f7f7f8',
                'prt_color_ink' => '#0b0c0e', 'prt_color_body' => '#3a3d44',
                'prt_font_heading' => 'Inter Tight', 'prt_font_body' => 'Inter',
                'prt_btn_radius' => '4', 'prt_card_radius' => '8',
            ],
        ],
    ]);
}

/** Admin page registration. */
add_action('admin_menu', function () {
    add_theme_page(
        __('Theme Tools', 'pressroot'),
        __('Theme Tools', 'pressroot'),
        'edit_theme_options',
        'prt-theme-tools',
        __NAMESPACE__ . '\\prt_tools_render'
    );
});

function prt_tools_render()
{
    if (! current_user_can('edit_theme_options')) {
        return;
    }
    $notice = isset($_GET['prt_done']) ? sanitize_key($_GET['prt_done']) : '';
    $post   = admin_url('admin-post.php');
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Theme Tools', 'pressroot'); ?></h1>

        <?php if ($notice === 'kit') : ?>
            <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Style kit applied.', 'pressroot'); ?></p></div>
        <?php elseif ($notice === 'import') : ?>
            <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Settings imported.', 'pressroot'); ?></p></div>
        <?php elseif ($notice === 'reset') : ?>
            <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Theme settings reset to defaults.', 'pressroot'); ?></p></div>
        <?php elseif ($notice === 'importerr') : ?>
            <div class="notice notice-error is-dismissible"><p><?php esc_html_e('Could not import that file. Make sure it is a JSON export from this theme.', 'pressroot'); ?></p></div>
        <?php endif; ?>

        <h2 style="margin-top:24px"><?php esc_html_e('Style Kits', 'pressroot'); ?></h2>
        <p class="description"><?php esc_html_e('One click applies a full palette + font + radius set. You can still fine-tune everything afterward in the Customizer.', 'pressroot'); ?></p>
        <div style="display:flex;flex-wrap:wrap;gap:14px;margin:16px 0 32px">
            <?php foreach (prt_style_kits() as $id => $kit) :
                $m = $kit['mods']; ?>
                <form method="post" action="<?php echo esc_url($post); ?>" style="width:230px;border:1px solid #dcdcde;border-radius:10px;padding:14px;background:#fff">
                    <div style="display:flex;gap:6px;margin-bottom:10px">
                        <?php foreach (['prt_color_paper', 'prt_color_action', 'prt_color_ink', 'prt_color_body'] as $ck) : ?>
                            <span style="width:26px;height:26px;border-radius:50%;border:1px solid rgba(0,0,0,.1);background:<?php echo esc_attr($m[$ck]); ?>"></span>
                        <?php endforeach; ?>
                    </div>
                    <strong style="font-size:14px"><?php echo esc_html($kit['label']); ?></strong>
                    <p style="margin:4px 0 10px;color:#646970;font-size:12px"><?php echo esc_html($kit['desc']); ?></p>
                    <p style="margin:0 0 12px;font-size:12px;color:#646970"><?php echo esc_html($m['prt_font_heading'] . ' / ' . $m['prt_font_body']); ?></p>
                    <input type="hidden" name="action" value="prt_apply_kit">
                    <input type="hidden" name="kit" value="<?php echo esc_attr($id); ?>">
                    <?php wp_nonce_field('prt_apply_kit'); ?>
                    <button class="button button-primary" style="width:100%"><?php esc_html_e('Apply', 'pressroot'); ?></button>
                </form>
            <?php endforeach; ?>
        </div>

        <hr>

        <h2 style="margin-top:24px"><?php esc_html_e('Export settings', 'pressroot'); ?></h2>
        <p class="description"><?php esc_html_e('Download all theme settings as a JSON file you can re-import on another site.', 'pressroot'); ?></p>
        <form method="post" action="<?php echo esc_url($post); ?>" style="margin:12px 0 28px">
            <input type="hidden" name="action" value="prt_export_settings">
            <?php wp_nonce_field('prt_export_settings'); ?>
            <button class="button"><?php esc_html_e('Download export (.json)', 'pressroot'); ?></button>
        </form>

        <h2><?php esc_html_e('Import settings', 'pressroot'); ?></h2>
        <p class="description"><?php esc_html_e('Upload a JSON export to apply those settings here.', 'pressroot'); ?></p>
        <form method="post" enctype="multipart/form-data" action="<?php echo esc_url($post); ?>" style="margin:12px 0 28px">
            <input type="file" name="prt_import_file" accept="application/json,.json" required>
            <input type="hidden" name="action" value="prt_import_settings">
            <?php wp_nonce_field('prt_import_settings'); ?>
            <button class="button button-primary"><?php esc_html_e('Import file', 'pressroot'); ?></button>
        </form>

        <hr>

        <h2 style="color:#b32d2e"><?php esc_html_e('Reset', 'pressroot'); ?></h2>
        <p class="description"><?php esc_html_e('Remove all of this theme\'s settings and return to defaults. This cannot be undone, so export first.', 'pressroot'); ?></p>
        <form method="post" action="<?php echo esc_url($post); ?>" style="margin:12px 0" onsubmit="return confirm('<?php echo esc_js(__('Reset all theme settings to defaults?', 'pressroot')); ?>');">
            <input type="hidden" name="action" value="prt_reset_settings">
            <?php wp_nonce_field('prt_reset_settings'); ?>
            <button class="button" style="border-color:#b32d2e;color:#b32d2e"><?php esc_html_e('Reset theme settings', 'pressroot'); ?></button>
        </form>
    </div>
    <?php
}

/** Apply a style kit. */
add_action('admin_post_prt_apply_kit', function () {
    if (! current_user_can('edit_theme_options') || ! check_admin_referer('prt_apply_kit')) {
        wp_die('Not allowed');
    }
    $kits = prt_style_kits();
    $id   = isset($_POST['kit']) ? sanitize_key($_POST['kit']) : '';
    if (isset($kits[$id])) {
        foreach ($kits[$id]['mods'] as $k => $v) {
            set_theme_mod($k, $v);
        }
    }
    wp_safe_redirect(admin_url('themes.php?page=prt-theme-tools&prt_done=kit'));
    exit;
});

/** Export all prt_* theme mods as JSON download. */
add_action('admin_post_prt_export_settings', function () {
    if (! current_user_can('edit_theme_options') || ! check_admin_referer('prt_export_settings')) {
        wp_die('Not allowed');
    }
    $payload = [
        'theme'   => 'pressroot',
        'version' => wp_get_theme()->get('Version'),
        'date'    => gmdate('c'),
        'mods'    => prt_owned_mods(),
    ];
    nocache_headers();
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename=matthummel-settings-' . gmdate('Ymd-His') . '.json');
    echo wp_json_encode($payload, JSON_PRETTY_PRINT);
    exit;
});

/** Import settings from an uploaded JSON file. */
add_action('admin_post_prt_import_settings', function () {
    if (! current_user_can('edit_theme_options') || ! check_admin_referer('prt_import_settings')) {
        wp_die('Not allowed');
    }
    $ok = false;
    if (! empty($_FILES['prt_import_file']['tmp_name']) && is_uploaded_file($_FILES['prt_import_file']['tmp_name'])) {
        $raw  = file_get_contents($_FILES['prt_import_file']['tmp_name']);
        $data = json_decode($raw, true);
        if (is_array($data) && isset($data['mods']) && is_array($data['mods'])) {
            foreach ($data['mods'] as $k => $v) {
                if (is_string($k) && strpos($k, 'prt_') === 0 && (is_scalar($v) || is_array($v))) {
                    set_theme_mod($k, $v);
                }
            }
            $ok = true;
        }
    }
    wp_safe_redirect(admin_url('themes.php?page=prt-theme-tools&prt_done=' . ($ok ? 'import' : 'importerr')));
    exit;
});

/** Reset: remove all prt_* theme mods. */
add_action('admin_post_prt_reset_settings', function () {
    if (! current_user_can('edit_theme_options') || ! check_admin_referer('prt_reset_settings')) {
        wp_die('Not allowed');
    }
    foreach (array_keys(prt_owned_mods()) as $k) {
        remove_theme_mod($k);
    }
    wp_safe_redirect(admin_url('themes.php?page=prt-theme-tools&prt_done=reset'));
    exit;
});
