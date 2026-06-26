<?php

/**
 * Layout engine: per-content-type width preset + custom width + sidebar,
 * driven by theme mods, exposed in the Customizer (Theme Options -> Layout),
 * and applied via body classes + a precise inline width override.
 */

namespace App;

function prt_layout_defaults()
{
    return [
        'page'    => ['width' => 'default', 'maxwidth' => 0, 'sidebar' => false],
        'post'    => ['width' => 'narrow',  'maxwidth' => 0, 'sidebar' => false],
        'archive' => ['width' => 'default', 'maxwidth' => 0, 'sidebar' => false],
        'project' => ['width' => 'default', 'maxwidth' => 0, 'sidebar' => false],
    ];
}

function prt_width_choices()
{
    return [
        'default' => __('Default', 'pressroot'),
        'full'    => __('Full width', 'pressroot'),
        'narrow'  => __('Narrow', 'pressroot'),
        'boxed'   => __('Boxed', 'pressroot'),
    ];
}

function prt_layout_labels()
{
    return [
        'page'    => __('Pages', 'pressroot'),
        'post'    => __('Posts', 'pressroot'),
        'archive' => __('Archives', 'pressroot'),
        'project' => __('Projects', 'pressroot'),
    ];
}

/** Which layout bucket the current view falls into. Projects target the CPT only. */
function prt_current_layout_type()
{
    if (is_singular('projects') || is_post_type_archive('projects')) {
        return 'project';
    }
    if (is_singular('post')) {
        return 'post';
    }
    if (is_page()) {
        return 'page';
    }
    if (is_search() || is_home() || is_archive()) {
        return 'archive';
    }
    return 'page';
}

function prt_active_layout()
{
    $d = prt_layout_defaults();
    $t = prt_current_layout_type();
    return [
        'type'     => $t,
        'width'    => get_theme_mod("prt_layout_{$t}_width", $d[$t]['width']),
        'maxwidth' => absint(get_theme_mod("prt_layout_{$t}_maxwidth", $d[$t]['maxwidth'])),
        'sidebar'  => (bool) get_theme_mod("prt_layout_{$t}_sidebar", $d[$t]['sidebar']),
    ];
}

/** Layout classes on <body>. */
add_filter('body_class', function ($classes) {
    $l = prt_active_layout();
    $classes[] = 'prt-w-' . sanitize_html_class($l['width']);
    $classes[] = 'prt-type-' . sanitize_html_class($l['type']);
    if ($l['sidebar']) {
        $classes[] = 'prt-has-sidebar';
    }
    return $classes;
});

/** Per-type custom width override (emitted after app.css via the prt_head_end hook). */
add_action('prt_head_end', function () {
    $d = prt_layout_defaults();
    $css = '';
    foreach (array_keys($d) as $type) {
        $w = absint(get_theme_mod("prt_layout_{$type}_maxwidth", $d[$type]['maxwidth']));
        if ($w > 0) {
            $css .= "body.prt-type-{$type} .main .container{max-width:{$w}px}";
        }
    }
    if ($css !== '') {
        echo "\n<style id=\"prt-layout-widths\">" . $css . "</style>\n";
    }
});

/** Primary (right) sidebar widget area used when a layout enables it. */
add_action('widgets_init', function () {
    register_sidebar([
        'name'          => __('Primary Sidebar', 'pressroot'),
        'id'            => 'sidebar-primary',
        'description'   => __('Shown on the right when a layout has its sidebar enabled.', 'pressroot'),
        'before_widget' => '<section class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ]);
});

/** Customizer: Theme Options -> Layout. */
add_action('customize_register', function ($wp) {
    if (! $wp->get_panel('prt_theme_options')) {
        $wp->add_panel('prt_theme_options', [
            'title'    => __('Theme Options', 'pressroot'),
            'priority' => 30,
        ]);
    }

    $wp->add_section('prt_layout_section', [
        'title'       => __('Layout', 'pressroot'),
        'panel'       => 'prt_theme_options',
        'description' => __('Set a width preset and/or an exact custom width per content type. Standard widths: 1140, 1200, 1280, 1320, 1440 px.', 'pressroot'),
    ]);

    $d = prt_layout_defaults();
    foreach (prt_layout_labels() as $type => $label) {
        $wp->add_setting("prt_layout_{$type}_width", [
            'default'           => $d[$type]['width'],
            'sanitize_callback' => 'sanitize_key',
        ]);
        $wp->add_control("prt_layout_{$type}_width", [
            'label'   => sprintf(__('%s â€” width preset', 'pressroot'), $label),
            'section' => 'prt_layout_section',
            'type'    => 'select',
            'choices' => prt_width_choices(),
        ]);

        $wp->add_setting("prt_layout_{$type}_maxwidth", [
            'default'           => $d[$type]['maxwidth'],
            'sanitize_callback' => 'absint',
        ]);
        $wp->add_control("prt_layout_{$type}_maxwidth", [
            'label'       => sprintf(__('%s â€” custom width', 'pressroot'), $label),
            'description' => __('Overrides the preset above when set. "Use preset" follows the width preset.', 'pressroot'),
            'section'     => 'prt_layout_section',
            'type'        => 'select',
            'choices'     => \App\prt_width_options(true),
        ]);

        $wp->add_setting("prt_layout_{$type}_sidebar", [
            'default'           => $d[$type]['sidebar'],
            'sanitize_callback' => 'wp_validate_boolean',
        ]);
        $wp->add_control("prt_layout_{$type}_sidebar", [
            'label'   => sprintf(__('%s â€” show sidebar', 'pressroot'), $label),
            'section' => 'prt_layout_section',
            'type'    => 'checkbox',
        ]);
    }
}, 20);

/* ----------------------------------------------------------------------
   Top utility bar (above the main nav)
---------------------------------------------------------------------- */

/** Map a palette choice to a CSS value (global token or custom hex). */
function prt_palette_value($choice, $custom = '')
{
    $map = [
        'ink'    => 'var(--color-ink)',
        'green'  => 'var(--color-green)',
        'cream'  => 'var(--color-cream)',
        'paper'  => 'var(--color-khaki)',
        'body'   => 'var(--color-body)',
        'white'  => '#ffffff',
    ];
    if ($choice === 'custom') {
        return $custom !== '' ? $custom : 'transparent';
    }
    return $map[$choice] ?? 'transparent';
}

function prt_palette_choices()
{
    return [
        'ink'    => __('Ink (dark)', 'pressroot'),
        'green'  => __('Brand green', 'pressroot'),
        'cream'  => __('Cream', 'pressroot'),
        'paper'  => __('Paper', 'pressroot'),
        'white'  => __('White', 'pressroot'),
        'custom' => __('Customâ€¦', 'pressroot'),
    ];
}

function prt_topbar()
{
    return [
        'enable'      => (bool) get_theme_mod('prt_topbar_enable', false),
        'contact'     => get_theme_mod('prt_topbar_contact', ''),
        'show_social' => (bool) get_theme_mod('prt_topbar_show_social', true),
        'cta_text'    => get_theme_mod('prt_topbar_cta_text', ''),
        'cta_url'     => get_theme_mod('prt_topbar_cta_url', ''),
        'bg'          => prt_palette_value(get_theme_mod('prt_topbar_bg', 'ink'), get_theme_mod('prt_topbar_bg_custom', '')),
        'text'        => prt_palette_value(get_theme_mod('prt_topbar_text', 'white'), get_theme_mod('prt_topbar_text_custom', '')),
    ];
}

add_action('customize_register', function ($wp) {
    if (! $wp->get_panel('prt_theme_options')) {
        $wp->add_panel('prt_theme_options', ['title' => __('Theme Options', 'pressroot'), 'priority' => 30]);
    }
    $wp->add_section('prt_topbar_section', [
        'title'       => __('Top Bar', 'pressroot'),
        'panel'       => 'prt_theme_options',
        'description' => __('A slim utility bar above the main nav for contact info, social links, and a CTA.', 'pressroot'),
    ]);

    $controls = [
        ['prt_topbar_enable', __('Enable top bar', 'pressroot'), 'checkbox', false, 'wp_validate_boolean'],
        ['prt_topbar_contact', __('Contact text (left)', 'pressroot'), 'text', '', 'sanitize_text_field'],
        ['prt_topbar_show_social', __('Show social links (right)', 'pressroot'), 'checkbox', true, 'wp_validate_boolean'],
        ['prt_topbar_cta_text', __('CTA text (right)', 'pressroot'), 'text', '', 'sanitize_text_field'],
        ['prt_topbar_cta_url', __('CTA URL', 'pressroot'), 'text', '', 'esc_url_raw'],
    ];
    foreach ($controls as $c) {
        $wp->add_setting($c[0], ['default' => $c[3], 'sanitize_callback' => $c[4]]);
        $wp->add_control($c[0], ['label' => $c[1], 'section' => 'prt_topbar_section', 'type' => $c[2]]);
    }

    foreach (['bg' => __('Background color', 'pressroot'), 'text' => __('Text color', 'pressroot')] as $slug => $label) {
        $def = $slug === 'bg' ? 'ink' : 'white';
        $wp->add_setting("prt_topbar_{$slug}", ['default' => $def, 'sanitize_callback' => 'sanitize_key']);
        $wp->add_control("prt_topbar_{$slug}", ['label' => $label, 'section' => 'prt_topbar_section', 'type' => 'select', 'choices' => prt_palette_choices()]);
        $wp->add_setting("prt_topbar_{$slug}_custom", ['default' => '', 'sanitize_callback' => 'sanitize_hex_color']);
        $wp->add_control(new \WP_Customize_Color_Control($wp, "prt_topbar_{$slug}_custom", ['label' => $label . ' ' . __('(custom)', 'pressroot'), 'section' => 'prt_topbar_section']));
    }
}, 21);
