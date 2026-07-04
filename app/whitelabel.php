<?php

/**
 * White-label + onboarding:
 *  - Branded login screen (logo, colors, link).
 *  - Admin footer credit + "Get started" dashboard widget (onboarding checklist).
 *  - One-click "Create starter pages" (Home/About/Projects/Contact + primary menu).
 */

namespace App;

/**
 * Register the "White Label" Customizer section (login logo/background,
 * admin footer text, dashboard-widget toggle) under the shared Theme Options
 * panel, creating that panel if no other file has registered it first.
 * Priority 29 just needs to land before prt_theme_options is read elsewhere;
 * there's no dependency on the other sections in this panel.
 */
add_action('customize_register', function ($wp) {
    // Shared guarded helper — see prt_ensure_theme_options_panel() in app/customizer.php.
    prt_ensure_theme_options_panel($wp);
    $wp->add_section('prt_wl_section', ['title' => __('White Label', 'pressroot'), 'panel' => 'prt_theme_options', 'description' => __('Brand the login screen and admin.', 'pressroot')]);

    $wp->add_setting('prt_wl_login', ['default' => '', 'sanitize_callback' => 'esc_url_raw']);
    $wp->add_control(new \WP_Customize_Image_Control($wp, 'prt_wl_login', ['label' => __('Login logo', 'pressroot'), 'section' => 'prt_wl_section']));

    $wp->add_setting('prt_wl_login_bg', ['default' => '#FFFDF7', 'sanitize_callback' => 'sanitize_hex_color']);
    $wp->add_control(new \WP_Customize_Color_Control($wp, 'prt_wl_login_bg', ['label' => __('Login background', 'pressroot'), 'section' => 'prt_wl_section']));

    $wp->add_setting('prt_wl_footer', ['default' => '', 'sanitize_callback' => 'sanitize_text_field']);
    $wp->add_control('prt_wl_footer', ['label' => __('Admin footer text', 'pressroot'), 'section' => 'prt_wl_section', 'type' => 'text']);

    $wp->add_setting('prt_wl_dash', ['default' => true, 'sanitize_callback' => 'wp_validate_boolean']);
    $wp->add_control('prt_wl_dash', ['label' => __('Show "Get started" dashboard widget', 'pressroot'), 'section' => 'prt_wl_section', 'type' => 'checkbox']);
}, 29);

/**
 * ---- Login screen ----
 * Prints inline <style> to re-skin wp-login.php: swaps the logo, background,
 * and primary-button color to match the site's brand instead of the default
 * WordPress mark. Falls back to the SEO logo (prt_seo_logo, set elsewhere)
 * when no dedicated login logo has been uploaded, so sites still look branded
 * out of the box without a second logo upload.
 */
add_action('login_enqueue_scripts', function () {
    $logo = get_theme_mod('prt_wl_login', '');
    if (! $logo && get_theme_mod('prt_seo_logo', '')) {
        $logo = get_theme_mod('prt_seo_logo', '');
    }
    $bg    = sanitize_hex_color(get_theme_mod('prt_wl_login_bg', '#FFFDF7')) ?: '#FFFDF7';
    $green = sanitize_hex_color(get_theme_mod('prt_color_action', '#7C5CFF')) ?: '#7C5CFF';

    echo '<style>';
    echo 'body.login{background:' . esc_attr($bg) . ';}';
    if ($logo) {
        echo '.login h1 a{background-image:url(' . esc_url($logo) . ');background-size:contain;background-position:center;width:auto;max-width:280px;height:72px;}';
    }
    echo '.login #backtoblog a,.login #nav a{color:#5c636c;}';
    echo '.wp-core-ui .button-primary{background:' . esc_attr($green) . ';border-color:' . esc_attr($green) . ';}';
    echo '.login form{border-radius:14px;border:1px solid #e6e2d9;}';
    echo '.login input[type=text]:focus,.login input[type=password]:focus{border-color:' . esc_attr($green) . ';box-shadow:0 0 0 1px ' . esc_attr($green) . ';}';
    echo '</style>';
});
// Point the login logo link at the site home instead of WordPress.org, and
// its title-attribute text at the site name instead of "WordPress" — both
// small but necessary parts of removing WordPress branding from the screen.
add_filter('login_headerurl', function () {
    return home_url('/');
});
add_filter('login_headertext', function () {
    return get_bloginfo('name');
});

/**
 * ---- Admin footer ----
 * Replaces the default "Thank you for creating with WordPress" footer text
 * with the site owner's custom credit line, when one has been set.
 */
add_filter('admin_footer_text', function ($text) {
    $custom = get_theme_mod('prt_wl_footer', '');
    return $custom ? esc_html($custom) : $text;
});

/**
 * ---- Dashboard "Get started" widget ----
 * Adds a checklist dashboard widget linking to the key setup screens (Style
 * Kit, identity, social, SEO, menu, performance) so a new site owner has a
 * guided first-run path instead of having to discover these settings on
 * their own. Gated on edit_theme_options so lower-privileged dashboard users
 * don't see setup steps they can't act on.
 */
add_action('wp_dashboard_setup', function () {
    if (! get_theme_mod('prt_wl_dash', true) || ! current_user_can('edit_theme_options')) {
        return;
    }
    wp_add_dashboard_widget('prt_get_started', __('Matt Hummel theme — Get started', 'pressroot'), __NAMESPACE__ . '\\prt_dashboard_widget');
});

/**
 * Renders the "Get started" dashboard widget body: the checklist links plus
 * the "Create starter pages + menu" form (posts to prt_starter_pages below).
 */
function prt_dashboard_widget()
{
    $tools = admin_url('themes.php?page=prt-theme-tools');
    $items = [
        [__('Pick a Style Kit', 'pressroot'), $tools],
        [__('Set your logo & site identity', 'pressroot'), admin_url('customize.php?autofocus[section]=title_tagline')],
        [__('Add your social links', 'pressroot'), admin_url('customize.php?autofocus[section]=prt_popout_section')],
        [__('Configure SEO & schema', 'pressroot'), admin_url('customize.php?autofocus[section]=prt_seo_section')],
        [__('Build your menu', 'pressroot'), admin_url('nav-menus.php')],
        [__('Tune performance', 'pressroot'), admin_url('customize.php?autofocus[section]=prt_perf_section')],
    ];
    echo '<p>' . esc_html__('A few steps to make the site yours:', 'pressroot') . '</p><ol style="margin-left:18px">';
    foreach ($items as $it) {
        echo '<li style="margin:6px 0"><a href="' . esc_url($it[1]) . '">' . esc_html($it[0]) . '</a></li>';
    }
    echo '</ol>';
    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" onsubmit="return confirm(\'' . esc_js(__('Create Home, About, Projects and Contact pages and a primary menu?', 'pressroot')) . '\');">';
    echo '<input type="hidden" name="action" value="prt_starter_pages">';
    wp_nonce_field('prt_starter_pages');
    echo '<button class="button button-primary">' . esc_html__('Create starter pages + menu', 'pressroot') . '</button>';
    echo ' <a class="button" href="' . esc_url($tools) . '">' . esc_html__('Theme Tools', 'pressroot') . '</a>';
    echo '</form>';
}

/**
 * ---- One-click starter pages + menu ----
 * Creates Home/About/Projects/Contact pages (skipping any that already exist
 * by slug, so this is safe to run more than once), sets Home as the static
 * front page, and builds a "Primary" nav menu from those pages — assigned to
 * the primary_navigation location registered in app/setup.php. This exists so
 * a brand-new install isn't a blank site: one click gives the owner a
 * navigable structure to start editing instead of an empty page list and menu.
 */
add_action('admin_post_prt_starter_pages', function () {
    if (! current_user_can('edit_theme_options') || ! check_admin_referer('prt_starter_pages')) {
        wp_die('Not allowed');
    }

    $defs = [
        'home'     => __('Home', 'pressroot'),
        'about'    => __('About', 'pressroot'),
        'projects' => __('Projects', 'pressroot'),
        'contact'  => __('Contact', 'pressroot'),
    ];
    $ids = [];
    foreach ($defs as $slug => $title) {
        $existing = get_page_by_path($slug);
        if ($existing) {
            $ids[$slug] = $existing->ID;
            continue;
        }
        $ids[$slug] = wp_insert_post([
            'post_title'   => $title,
            'post_name'    => $slug,
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_content' => '<!-- wp:paragraph --><p>' . esc_html($title) . '</p><!-- /wp:paragraph -->',
        ]);
    }

    // Front page = Home
    if (! empty($ids['home'])) {
        update_option('show_on_front', 'page');
        update_option('page_on_front', $ids['home']);
    }

    // Primary menu
    $menu_name = __('Primary', 'pressroot');
    $menu = wp_get_nav_menu_object($menu_name);
    $menu_id = $menu ? $menu->term_id : wp_create_nav_menu($menu_name);
    if (! is_wp_error($menu_id)) {
        $existing_items = wp_get_nav_menu_items($menu_id) ?: [];
        if (empty($existing_items)) {
            foreach (['home', 'about', 'projects', 'contact'] as $slug) {
                if (! empty($ids[$slug])) {
                    wp_update_nav_menu_item($menu_id, 0, [
                        'menu-item-title'     => $defs[$slug],
                        'menu-item-object'    => 'page',
                        'menu-item-object-id' => $ids[$slug],
                        'menu-item-type'      => 'post_type',
                        'menu-item-status'    => 'publish',
                    ]);
                }
            }
        }
        $locations = get_theme_mod('nav_menu_locations', []);
        $locations['primary_navigation'] = $menu_id;
        set_theme_mod('nav_menu_locations', $locations);
    }

    wp_safe_redirect(admin_url('index.php?prt_starter=done'));
    exit;
});

// Success banner for the redirect at the end of the handler above
// (?prt_starter=done); read-only query flag, nothing to nonce-check here.
add_action('admin_notices', function () {
    if (isset($_GET['prt_starter']) && $_GET['prt_starter'] === 'done') {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Starter pages and primary menu created.', 'pressroot') . '</p></div>';
    }
});
