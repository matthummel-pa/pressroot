<?php

/**
 * Header behaviors: sticky, shrink-on-scroll, and transparent (overlay) header.
 * Controls live in the consolidated "Header Layout" section (prt_headerlayout_section).
 * Adds body classes + scoped CSS/JS. No template edits required.
 */

namespace App;

add_action('customize_register', function ($wp) {
    if (! $wp->get_panel('prt_theme_options')) {
        $wp->add_panel('prt_theme_options', ['title' => __('Theme Options', 'pressroot'), 'priority' => 30]);
    }

    $wp->add_setting('prt_header_sticky', ['default' => false, 'sanitize_callback' => 'wp_validate_boolean']);
    $wp->add_control('prt_header_sticky', ['label' => __('Sticky header', 'pressroot'), 'section' => 'prt_headerlayout_section', 'type' => 'checkbox']);

    $wp->add_setting('prt_header_shrink', ['default' => false, 'sanitize_callback' => 'wp_validate_boolean']);
    $wp->add_control('prt_header_shrink', ['label' => __('Shrink on scroll (needs sticky)', 'pressroot'), 'section' => 'prt_headerlayout_section', 'type' => 'checkbox']);

    $wp->add_setting('prt_header_transparent', ['default' => 'none', 'sanitize_callback' => 'sanitize_key']);
    $wp->add_control('prt_header_transparent', [
        'label'   => __('Transparent overlay header', 'pressroot'),
        'section' => 'prt_headerlayout_section',
        'type'    => 'select',
        'choices' => ['none' => __('Off', 'pressroot'), 'front' => __('Front page only', 'pressroot'), 'all' => __('All pages', 'pressroot')],
    ]);
}, 23);

add_filter('body_class', function ($c) {
    if (get_theme_mod('prt_header_sticky', false)) {
        $c[] = 'prt-sticky';
    }
    if (get_theme_mod('prt_header_sticky', false) && get_theme_mod('prt_header_shrink', false)) {
        $c[] = 'prt-shrink';
    }
    $tr = get_theme_mod('prt_header_transparent', 'none');
    if ($tr === 'all' || ($tr === 'front' && is_front_page())) {
        $c[] = 'prt-transparent';
    }
    return $c;
});

add_action('prt_head_end', function () {
    $sticky = get_theme_mod('prt_header_sticky', false);
    $tr     = get_theme_mod('prt_header_transparent', 'none');
    if (! $sticky && $tr === 'none') {
        return;
    }
    $css = '';
    if ($sticky) {
        $css .= 'body.prt-sticky .banner{position:sticky;top:0;z-index:50;background:var(--color-paper,#fbfaf7);transition:padding .2s ease,box-shadow .2s ease;}';
        $css .= 'body.prt-sticky.prt-scrolled .banner{box-shadow:0 4px 20px rgba(23,25,30,.08);}';
        $css .= 'body.prt-shrink.prt-scrolled .banner{padding-top:8px;padding-bottom:8px;}';
    }
    if ($tr !== 'none') {
        $css .= 'body.prt-transparent .banner{background:transparent;}';
        $css .= 'body.prt-transparent.prt-scrolled .banner{background:var(--color-paper,#fbfaf7);}';
    }
    echo "\n<style id=\"prt-headerbe\">" . $css . "</style>\n";
    echo "<script>(function(){function s(){document.body.classList.toggle('prt-scrolled',window.scrollY>10);}window.addEventListener('scroll',s,{passive:true});s();})();</script>";
}, 15);
