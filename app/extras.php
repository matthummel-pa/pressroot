<?php

/**
 * Extras Customizer: base font size, line heights, link underline,
 * button + card radius, text-selection color, and a scroll-to-top button.
 * Emitted as CSS via prt_head_end (no rebuild).
 */

namespace App;

add_action('customize_register', function ($wp) {
    if (! $wp->get_panel('prt_theme_options')) {
        $wp->add_panel('prt_theme_options', ['title' => __('Theme Options', 'pressroot'), 'priority' => 30]);
    }
    $wp->add_section('prt_extras_section', ['title' => __('Extras', 'pressroot'), 'panel' => 'prt_theme_options']);

    $select = function ($wp, $id, $label, $choices, $default, $section = 'prt_extras_section') {
        $wp->add_setting($id, ['default' => $default, 'sanitize_callback' => 'sanitize_text_field']);
        $wp->add_control($id, ['label' => $label, 'section' => $section, 'type' => 'select', 'choices' => $choices]);
    };
    $bool = function ($wp, $id, $label, $default, $section = 'prt_extras_section') {
        $wp->add_setting($id, ['default' => $default, 'sanitize_callback' => 'wp_validate_boolean']);
        $wp->add_control($id, ['label' => $label, 'section' => $section, 'type' => 'checkbox']);
    };

    $select($wp, 'prt_base_font', __('Base font size', 'pressroot'), ['15' => '15px', '16' => '16px', '17' => '17px (default)', '18' => '18px', '19' => '19px'], '17', 'prt_type');
    $select($wp, 'prt_body_lh', __('Body line height', 'pressroot'), ['1.5' => 'Tight (1.5)', '1.6' => '1.6', '1.7' => '1.7 (default)', '1.8' => 'Relaxed (1.8)', '2' => 'Loose (2.0)'], '1.7', 'prt_type');
    $select($wp, 'prt_head_lh', __('Heading line height', 'pressroot'), ['1' => '1.0', '1.1' => '1.1', '1.12' => '1.12 (default)', '1.2' => '1.2', '1.3' => '1.3'], '1.12', 'prt_type');
    $select($wp, 'prt_head_spacing', __('Heading letter spacing', 'pressroot'), ['-0.03em' => 'Tighter', '-0.02em' => 'Tight (default)', '0' => 'Normal', '0.02em' => 'Wide'], '-0.02em', 'prt_type');
    $bool($wp, 'prt_link_underline', __('Underline content links', 'pressroot'), false);
    $select($wp, 'prt_btn_radius', __('Button corner radius', 'pressroot'), ['0' => 'Square', '4' => '4px', '8' => '8px (default)', '12' => '12px', '999' => 'Pill'], '8');
    $select($wp, 'prt_card_radius', __('Card corner radius', 'pressroot'), ['6' => '6px', '10' => '10px', '14' => '14px', '16' => '16px (default)', '20' => '20px'], '16');
    $bool($wp, 'prt_scrolltop', __('Show scroll-to-top button', 'pressroot'), true);

    $wp->add_setting('prt_selection', ['default' => '', 'sanitize_callback' => 'sanitize_hex_color']);
    $wp->add_control(new \WP_Customize_Color_Control($wp, 'prt_selection', ['label' => __('Text selection color', 'pressroot'), 'section' => 'prt_extras_section']));
}, 28);

add_action('prt_head_end', function () {
    $g = function ($k, $d) { return get_theme_mod($k, $d); };
    $css  = 'body{font-size:' . absint($g('prt_base_font', 17)) . 'px;line-height:' . floatval($g('prt_body_lh', '1.7')) . ';}';
    $ls   = preg_replace('/[^0-9.\-em]/', '', (string) $g('prt_head_spacing', '-0.02em'));
    $css .= 'h1,h2,h3,h4{line-height:' . floatval($g('prt_head_lh', '1.12')) . ';letter-spacing:' . $ls . ';}';
    if ($g('prt_link_underline', false)) {
        $css .= '.post-prose a,.entry-content a{text-decoration:underline;text-underline-offset:2px;}';
    }
    $css .= '.btn,.btn-outline{border-radius:' . absint($g('prt_btn_radius', 8)) . 'px;}';
    $css .= '.mini-card,.project-card,.cta-card,.service-card{border-radius:' . absint($g('prt_card_radius', 16)) . 'px;}';
    $sel = sanitize_hex_color($g('prt_selection', ''));
    if ($sel) {
        $css .= '::selection{background:' . $sel . ';color:#fff;}';
    }
    if ($g('prt_scrolltop', true)) {
        $css .= '.prt-totop{position:fixed;right:20px;bottom:20px;width:44px;height:44px;border-radius:50%;border:0;background:var(--color-green);color:#fff;font-size:20px;line-height:1;cursor:pointer;opacity:0;visibility:hidden;transition:opacity .2s ease,visibility .2s ease;z-index:90;box-shadow:0 6px 20px rgba(23,25,30,.18);}.prt-totop.is-visible{opacity:1;visibility:visible;}.prt-totop:hover{background:var(--color-green-ink);}';
    }
    echo "\n<style id=\"prt-extras\">" . $css . "</style>\n";
}, 14);

add_action('wp_footer', function () {
    if (! get_theme_mod('prt_scrolltop', true)) {
        return;
    }
    echo '<button class="prt-totop" type="button" aria-label="' . esc_attr__('Back to top', 'pressroot') . '">&uarr;</button>';
    echo "<script>(function(){var b=document.querySelector('.prt-totop');if(!b)return;function t(){b.classList.toggle('is-visible',window.scrollY>400);}window.addEventListener('scroll',t,{passive:true});t();b.addEventListener('click',function(){window.scrollTo({top:0,behavior:'smooth'});});})();</script>";
}, 55);
