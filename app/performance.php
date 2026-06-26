<?php

/**
 * Performance & bloat control.
 * A Customizer "Performance" section toggles common WordPress front-end
 * optimizations: disable emojis/embeds/jQuery-migrate/XML-RPC/dashicons,
 * clean wp_head, defer scripts, and add preconnect resource hints.
 */

namespace App;

/** Defaults. */
function prt_perf_defaults()
{
    return [
        'prt_perf_emojis'    => true,   // remove emoji detection script + styles
        'prt_perf_embeds'    => false,  // remove wp-embed.js + oEmbed discovery
        'prt_perf_migrate'   => true,   // drop jquery-migrate
        'prt_perf_xmlrpc'    => true,   // disable XML-RPC + pingback
        'prt_perf_dashicons' => true,   // dequeue dashicons for logged-out visitors
        'prt_perf_headclean' => true,   // remove generator/rsd/wlw/shortlink/rest links
        'prt_perf_defer'     => false,  // defer non-critical front-end scripts
        'prt_perf_preconnect' => 'https://fonts.googleapis.com, https://fonts.gstatic.com',
    ];
}

function prt_perf($key)
{
    $d = prt_perf_defaults();
    return get_theme_mod($key, $d[$key] ?? null);
}

/** Customizer controls. */
add_action('customize_register', function ($wp) {
    if (! $wp->get_panel('prt_theme_options')) {
        $wp->add_panel('prt_theme_options', ['title' => __('Theme Options', 'pressroot'), 'priority' => 30]);
    }
    $wp->add_section('prt_perf_section', [
        'title'       => __('Performance', 'pressroot'),
        'panel'       => 'prt_theme_options',
        'description' => __('Trim WordPress front-end bloat. Safe defaults are pre-selected; toggle anything that conflicts with a plugin.', 'pressroot'),
    ]);

    $d = prt_perf_defaults();
    $bools = [
        'prt_perf_emojis'    => __('Disable emoji script & styles', 'pressroot'),
        'prt_perf_embeds'    => __('Disable oEmbed / wp-embed.js', 'pressroot'),
        'prt_perf_migrate'   => __('Remove jQuery Migrate', 'pressroot'),
        'prt_perf_xmlrpc'    => __('Disable XML-RPC & pingbacks', 'pressroot'),
        'prt_perf_dashicons' => __('Dequeue Dashicons for logged-out visitors', 'pressroot'),
        'prt_perf_headclean' => __('Clean wp_head (generator, RSD, shortlink, REST links)', 'pressroot'),
        'prt_perf_defer'     => __('Defer non-critical front-end scripts', 'pressroot'),
    ];
    foreach ($bools as $id => $label) {
        $wp->add_setting($id, ['default' => $d[$id], 'sanitize_callback' => 'wp_validate_boolean']);
        $wp->add_control($id, ['label' => $label, 'section' => 'prt_perf_section', 'type' => 'checkbox']);
    }

    $wp->add_setting('prt_perf_preconnect', ['default' => $d['prt_perf_preconnect'], 'sanitize_callback' => 'sanitize_text_field']);
    $wp->add_control('prt_perf_preconnect', [
        'label'       => __('Preconnect domains', 'pressroot'),
        'description' => __('Comma-separated origins to preconnect (speeds up fonts/CDNs).', 'pressroot'),
        'section'     => 'prt_perf_section',
        'type'        => 'text',
    ]);
}, 26);

/* ---- Apply (front-end only) ---- */

add_action('init', function () {
    if (is_admin()) {
        return;
    }

    if (prt_perf('prt_perf_emojis')) {
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('admin_print_styles', 'print_emoji_styles');
        add_filter('tiny_mce_plugins', function ($p) {
            return is_array($p) ? array_diff($p, ['wpemoji']) : $p;
        });
        add_filter('emoji_svg_url', '__return_false');
    }

    if (prt_perf('prt_perf_embeds')) {
        add_action('wp_footer', function () {
            wp_deregister_script('wp-embed');
        });
        remove_action('wp_head', 'wp_oembed_add_discovery_links');
        remove_action('wp_head', 'wp_oembed_add_host_js');
    }

    if (prt_perf('prt_perf_xmlrpc')) {
        add_filter('xmlrpc_enabled', '__return_false');
        add_filter('wp_headers', function ($h) {
            unset($h['X-Pingback']);
            return $h;
        });
    }

    if (prt_perf('prt_perf_headclean')) {
        remove_action('wp_head', 'rsd_link');
        remove_action('wp_head', 'wlwmanifest_link');
        remove_action('wp_head', 'wp_generator');
        remove_action('wp_head', 'wp_shortlink_wp_head');
        remove_action('wp_head', 'rest_output_link_wp_head');
        remove_action('wp_head', 'wp_oembed_add_discovery_links');
        remove_action('template_redirect', 'rest_output_link_header', 11);
    }
});

/** Remove jQuery Migrate. */
add_action('wp_default_scripts', function ($scripts) {
    if (is_admin() || ! prt_perf('prt_perf_migrate')) {
        return;
    }
    if (! empty($scripts->registered['jquery'])) {
        $deps = $scripts->registered['jquery']->deps;
        $scripts->registered['jquery']->deps = array_diff($deps, ['jquery-migrate']);
    }
});

/** Dequeue dashicons for logged-out visitors. */
add_action('wp_enqueue_scripts', function () {
    if (prt_perf('prt_perf_dashicons') && ! is_user_logged_in()) {
        wp_dequeue_style('dashicons');
        wp_deregister_style('dashicons');
    }
}, 100);

/** Preconnect resource hints. */
add_filter('wp_resource_hints', function ($hints, $relation) {
    if ($relation !== 'preconnect') {
        return $hints;
    }
    $raw = (string) prt_perf('prt_perf_preconnect');
    foreach (array_filter(array_map('trim', explode(',', $raw))) as $url) {
        $hints[] = ['href' => esc_url($url), 'crossorigin'];
    }
    return $hints;
}, 10, 2);

/** Defer non-critical scripts. */
add_filter('script_loader_tag', function ($tag, $handle) {
    if (is_admin() || ! prt_perf('prt_perf_defer')) {
        return $tag;
    }
    // Keep these synchronous to avoid breakage.
    $skip = ['jquery-core', 'jquery'];
    if (in_array($handle, $skip, true) || strpos($tag, ' defer') !== false || strpos($tag, ' async') !== false) {
        return $tag;
    }
    return str_replace(' src=', ' defer src=', $tag);
}, 10, 2);
