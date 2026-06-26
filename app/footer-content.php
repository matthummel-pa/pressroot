<?php

/**
 * Footer builder (colors, social, sticky header) + content controls
 * (global CTA + per-template intros), all in the Customizer.
 */

namespace App;

function prt_footer()
{
    return [
        'bg'          => prt_palette_value(get_theme_mod('prt_footer_bg', 'paper'), get_theme_mod('prt_footer_bg_custom', '')),
        'text'        => prt_palette_value(get_theme_mod('prt_footer_textc', 'body'), get_theme_mod('prt_footer_text_custom', '')),
        'show_social' => (bool) get_theme_mod('prt_footer_social', true),
    ];
}

/** Wire the global CTA to Customizer values (cta.blade uses these filters). */
add_filter('matthummel/cta_heading', function ($d) { $v = get_theme_mod('prt_cta_heading', ''); return $v !== '' ? $v : $d; });
add_filter('matthummel/cta_text', function ($d) { $v = get_theme_mod('prt_cta_body', ''); return $v !== '' ? $v : $d; });
add_filter('matthummel/cta_label', function ($d) { $v = get_theme_mod('prt_cta_btn_label', ''); return $v !== '' ? $v : $d; });
add_filter('matthummel/cta_url', function ($d) { $v = get_theme_mod('prt_cta_btn_url', ''); return $v !== '' ? $v : $d; });

/** Customizer sections. */
add_action('customize_register', function ($wp) {
    if (! $wp->get_panel('prt_theme_options')) {
        $wp->add_panel('prt_theme_options', ['title' => __('Theme Options', 'pressroot'), 'priority' => 30]);
    }

    /* Footer */
    $wp->add_section('prt_footer_section', ['title' => __('Footer & Header', 'pressroot'), 'panel' => 'prt_theme_options']);

    // (Sticky header lives in Header Layout â†’ prt_header_sticky; the old duplicate here was removed.)
    $wp->add_setting('prt_footer_social', ['default' => true, 'sanitize_callback' => 'wp_validate_boolean']);
    $wp->add_control('prt_footer_social', ['label' => __('Show social icons in footer', 'pressroot'), 'section' => 'prt_footer_section', 'type' => 'checkbox']);

    $wp->add_setting('prt_footer_bg', ['default' => 'paper', 'sanitize_callback' => 'sanitize_key']);
    $wp->add_control('prt_footer_bg', ['label' => __('Footer background', 'pressroot'), 'section' => 'prt_footer_section', 'type' => 'select', 'choices' => prt_palette_choices()]);
    $wp->add_setting('prt_footer_bg_custom', ['default' => '', 'sanitize_callback' => 'sanitize_hex_color']);
    $wp->add_control(new \WP_Customize_Color_Control($wp, 'prt_footer_bg_custom', ['label' => __('Footer background (custom)', 'pressroot'), 'section' => 'prt_footer_section']));

    $wp->add_setting('prt_footer_textc', ['default' => 'body', 'sanitize_callback' => 'sanitize_key']);
    $wp->add_control('prt_footer_textc', ['label' => __('Footer text', 'pressroot'), 'section' => 'prt_footer_section', 'type' => 'select', 'choices' => prt_palette_choices()]);
    $wp->add_setting('prt_footer_text_custom', ['default' => '', 'sanitize_callback' => 'sanitize_hex_color']);
    $wp->add_control(new \WP_Customize_Color_Control($wp, 'prt_footer_text_custom', ['label' => __('Footer text (custom)', 'pressroot'), 'section' => 'prt_footer_section']));

    /* CTA & intros */
    $wp->add_section('prt_content_section', ['title' => __('CTA & Intros', 'pressroot'), 'panel' => 'prt_theme_options', 'description' => __('Edit the global project CTA and the intro text on the Projects and Contact templates.', 'pressroot')]);

    $content = [
        ['prt_cta_heading', __('CTA heading', 'pressroot'), 'text'],
        ['prt_cta_body', __('CTA text', 'pressroot'), 'textarea'],
        ['prt_cta_btn_label', __('CTA button label', 'pressroot'), 'text'],
        ['prt_cta_btn_url', __('CTA button URL', 'pressroot'), 'url'],
        ['prt_projects_intro', __('Projects archive intro', 'pressroot'), 'textarea'],
        ['prt_contact_intro', __('Contact intro (above form)', 'pressroot'), 'textarea'],
    ];
    foreach ($content as $c) {
        $san = $c[2] === 'url' ? 'esc_url_raw' : ($c[2] === 'textarea' ? 'wp_kses_post' : 'sanitize_text_field');
        $wp->add_setting($c[0], ['default' => '', 'sanitize_callback' => $san]);
        $wp->add_control($c[0], ['label' => $c[1], 'section' => 'prt_content_section', 'type' => $c[2]]);
    }
}, 23);

/** Emit Customizer-driven theme colors as CSS variables (keeps markup free of inline styles). */
add_action('prt_head_end', function () {
    $tb = function_exists('App\\prt_topbar') ? prt_topbar() : ['bg' => 'var(--color-ink)', 'text' => '#fff'];
    $po = function_exists('App\\prt_popout') ? prt_popout() : ['bg' => '#17191e', 'text' => '#fff'];
    $ft = prt_footer();
    $css = ':root{'
        . '--prt-topbar-bg:' . $tb['bg'] . ';--prt-topbar-text:' . $tb['text'] . ';'
        . '--prt-popout-bg:' . $po['bg'] . ';--prt-popout-text:' . $po['text'] . ';'
        . '--prt-footer-bg:' . $ft['bg'] . ';--prt-footer-text:' . $ft['text'] . ';'
        . '}';
    echo "\n<style id=\"prt-theme-vars\">" . $css . "</style>\n";
}, 11);

/** Footer column widget areas (block-based widgets). */
add_action('widgets_init', function () {
    for ($i = 1; $i <= 4; $i++) {
        register_sidebar([
            'name'          => sprintf(__('Footer Column %d', 'pressroot'), $i),
            'id'            => "footer-{$i}",
            'description'   => __('Drop any blocks here. Shown when "Footer columns" includes this column.', 'pressroot'),
            'before_widget' => '<section class="widget %2$s">',
            'after_widget'  => '</section>',
            'before_title'  => '<h2 class="footer-widget-title">',
            'after_title'   => '</h2>',
        ]);
    }
});

/** Footer column count control (added to the Footer & Header section). */
add_action('customize_register', function ($wp) {
    if ($wp->get_section('prt_footer_section')) {
        $wp->add_setting('prt_footer_cols', ['default' => 3, 'sanitize_callback' => 'absint']);
        $wp->add_control('prt_footer_cols', [
            'label'       => __('Footer columns', 'pressroot'),
            'description' => __('Add blocks to each column in Appearance > Widgets (Footer Column 1-4).', 'pressroot'),
            'section'     => 'prt_footer_section',
            'type'        => 'select',
            'choices'     => [1 => '1 column', 2 => '2 columns', 3 => '3 columns', 4 => '4 columns'],
        ]);
    }
}, 24);
