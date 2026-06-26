<?php

/**
 * Advanced typography: assign fonts + weights per element (headings, body, nav,
 * buttons), nav casing, body letter-spacing, and responsive base font sizes.
 * Reuses \App\prt_fonts() for the font list + CSS stacks and loads any extra
 * Google families the nav/buttons need.
 */

namespace App;

function prt_type_font_choices()
{
    $choices = ['Default' => __('Default (inherit)', 'pressroot')];
    if (function_exists('App\\prt_fonts')) {
        foreach (array_keys(prt_fonts()) as $n) {
            $choices[$n] = $n;
        }
    }
    return $choices;
}

add_action('customize_register', function ($wp) {
    if (! $wp->get_panel('prt_theme_options')) {
        $wp->add_panel('prt_theme_options', ['title' => __('Theme Options', 'pressroot'), 'priority' => 30]);
    }
    $wp->add_section('prt_type_adv', [
        'title' => __('Typography (advanced)', 'pressroot'),
        'panel' => 'prt_theme_options',
        'description' => __('Fine-grained control per element. Heading and body font families live in the main Typography section; this adds nav/buttons, weights, and responsive sizes.', 'pressroot'),
    ]);

    $fontChoices = prt_type_font_choices();
    $sel = function ($wp, $id, $label, $choices, $default) {
        $wp->add_setting($id, ['default' => $default, 'sanitize_callback' => 'sanitize_text_field']);
        $wp->add_control($id, ['label' => $label, 'section' => 'prt_type_adv', 'type' => 'select', 'choices' => $choices]);
    };

    $sel($wp, 'prt_font_nav', __('Navigation font', 'pressroot'), $fontChoices, 'Default');
    $sel($wp, 'prt_font_button', __('Button font', 'pressroot'), $fontChoices, 'Default');

    $weights = ['300' => '300 Light', '400' => '400 Regular', '500' => '500 Medium', '600' => '600 Semibold', '700' => '700 Bold', '800' => '800 Extrabold'];
    $sel($wp, 'prt_weight_heading', __('Heading weight', 'pressroot'), $weights, '600');
    $sel($wp, 'prt_weight_body', __('Body weight', 'pressroot'), ['300' => '300 Light', '400' => '400 Regular', '500' => '500 Medium'], '400');
    $sel($wp, 'prt_weight_nav', __('Nav weight', 'pressroot'), $weights, '500');
    $sel($wp, 'prt_weight_button', __('Button weight', 'pressroot'), $weights, '600');

    $sel($wp, 'prt_nav_case', __('Nav letter case', 'pressroot'), ['none' => __('Normal', 'pressroot'), 'uppercase' => __('UPPERCASE', 'pressroot'), 'lowercase' => __('lowercase', 'pressroot')], 'none');
    $sel($wp, 'prt_ls_body', __('Body letter spacing', 'pressroot'), ['-0.01em' => 'Tight', '0' => 'Normal', '0.01em' => 'Loose'], '0');

    $px = ['auto' => __('Auto (use base)', 'pressroot'), '14' => '14px', '15' => '15px', '16' => '16px', '17' => '17px', '18' => '18px'];
    $sel($wp, 'prt_base_tablet', __('Base font on tablet', 'pressroot'), $px, 'auto');
    $sel($wp, 'prt_base_mobile', __('Base font on mobile', 'pressroot'), $px, 'auto');
}, 24);

/** Load extra Google families for nav/button if they differ. */
add_action('wp_enqueue_scripts', function () {
    if (! function_exists('App\\prt_fonts')) {
        return;
    }
    $fonts = prt_fonts();
    $want  = [];
    foreach (['prt_font_nav', 'prt_font_button'] as $k) {
        $name = get_theme_mod($k, 'Default');
        if ($name !== 'Default' && isset($fonts[$name]) && ! empty($fonts[$name][0])) {
            $want[$fonts[$name][0]] = true;
        }
    }
    if ($want) {
        wp_enqueue_style(
            'prt-fonts-extra',
            'https://fonts.googleapis.com/css2?family=' . implode('&family=', array_keys($want)) . '&display=swap',
            [],
            null
        );
    }
}, 9);

add_action('prt_head_end', function () {
    $fonts = function_exists('App\\prt_fonts') ? prt_fonts() : [];
    $stack = function ($name) use ($fonts) {
        return ($name !== 'Default' && isset($fonts[$name][1])) ? $fonts[$name][1] : '';
    };
    $css = '';

    $nav = $stack(get_theme_mod('prt_font_nav', 'Default'));
    $btn = $stack(get_theme_mod('prt_font_button', 'Default'));
    $navCss = ($nav ? 'font-family:' . $nav . ';' : '') . 'font-weight:' . absint(get_theme_mod('prt_weight_nav', 500)) . ';';
    $case = get_theme_mod('prt_nav_case', 'none');
    if (in_array($case, ['uppercase', 'lowercase'], true)) {
        $navCss .= 'text-transform:' . $case . ';' . ($case === 'uppercase' ? 'letter-spacing:.04em;' : '');
    }
    $css .= '.nav a,.nav-primary a{' . $navCss . '}';

    $btnCss = ($btn ? 'font-family:' . $btn . ';' : '') . 'font-weight:' . absint(get_theme_mod('prt_weight_button', 600)) . ';';
    $css .= '.btn,.btn-outline,.wp-element-button,.wp-block-button__link,button.btn{' . $btnCss . '}';

    $css .= 'h1,h2,h3,h4,h5,h6{font-weight:' . absint(get_theme_mod('prt_weight_heading', 600)) . ';}';

    $ls = preg_replace('/[^0-9.\-em]/', '', (string) get_theme_mod('prt_ls_body', '0'));
    $css .= 'body{font-weight:' . absint(get_theme_mod('prt_weight_body', 400)) . ';letter-spacing:' . ($ls === '' ? '0' : $ls) . ';}';

    $t = get_theme_mod('prt_base_tablet', 'auto');
    $m = get_theme_mod('prt_base_mobile', 'auto');
    if ($t !== 'auto') {
        $css .= '@media(max-width:1024px){body{font-size:' . absint($t) . 'px;}}';
    }
    if ($m !== 'auto') {
        $css .= '@media(max-width:600px){body{font-size:' . absint($m) . 'px;}}';
    }

    echo "\n<style id=\"prt-typography\">" . $css . "</style>\n";
}, 17);
