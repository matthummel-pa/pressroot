<?php

/**
 * Dark mode: a header toggle that flips the CSS-variable palette,
 * persists the choice (localStorage), and supports system-auto.
 */

namespace App;

add_action('customize_register', function ($wp) {
    if (! $wp->get_panel('prt_theme_options')) {
        $wp->add_panel('prt_theme_options', ['title' => __('Theme Options', 'pressroot'), 'priority' => 30]);
    }
    $wp->add_section('prt_dark_section', ['title' => __('Dark Mode', 'pressroot'), 'panel' => 'prt_theme_options']);
    $wp->add_setting('prt_dark_enable', ['default' => true, 'sanitize_callback' => 'wp_validate_boolean']);
    $wp->add_control('prt_dark_enable', ['label' => __('Show dark mode toggle', 'pressroot'), 'section' => 'prt_dark_section', 'type' => 'checkbox']);
    $wp->add_setting('prt_dark_default', ['default' => 'light', 'sanitize_callback' => 'sanitize_key']);
    $wp->add_control('prt_dark_default', ['label' => __('Default mode', 'pressroot'), 'section' => 'prt_dark_section', 'type' => 'select', 'choices' => ['light' => __('Light', 'pressroot'), 'dark' => __('Dark', 'pressroot'), 'auto' => __('Auto (system)', 'pressroot')]]);
}, 25);

/** No-flash: set the dark class as early as possible. */
add_action('wp_head', function () {
    if (! get_theme_mod('prt_dark_enable', true)) {
        return;
    }
    $def = esc_js(get_theme_mod('prt_dark_default', 'light'));
    echo "<script>(function(){try{var d='{$def}';var m=localStorage.getItem('prt-theme');if(!m){m=(d==='auto')?(matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light'):d;}if(m==='dark'){document.documentElement.classList.add('prt-dark');}}catch(e){}})();</script>\n";
}, 2);

/** Toggle behavior. */
add_action('wp_footer', function () {
    if (! get_theme_mod('prt_dark_enable', true)) {
        return;
    }
    echo "<script>(function(){var b=document.querySelector('.prt-theme-toggle');if(!b)return;function set(d){document.documentElement.classList.toggle('prt-dark',d);try{localStorage.setItem('prt-theme',d?'dark':'light');}catch(e){}b.setAttribute('aria-pressed',d?'true':'false');}b.setAttribute('aria-pressed',document.documentElement.classList.contains('prt-dark')?'true':'false');b.addEventListener('click',function(){set(!document.documentElement.classList.contains('prt-dark'));});})();</script>\n";
}, 60);

/**
 * Dark-mode navbar surface. By default the .banner is transparent and just blends
 * into the dark page background, so it doesn't read as a distinct bar. Give it an
 * explicit darker tone + guaranteed light text/icons in dark mode. Emitted at a
 * late prt_head_end priority and with !important so it also wins over the optional
 * sticky-header background (which is hard-coded light).
 */
add_action('prt_head_end', function () {
    if (! get_theme_mod('prt_dark_enable', true)) {
        return;
    }
    echo "\n<style id=\"prt-dark-navbar\">"
        . 'html.prt-dark .banner{background:#1c1f24!important;border-bottom:1px solid #2c2f36;}'
        . 'html.prt-dark .banner,html.prt-dark .banner .brand,html.prt-dark .banner .brand-name,'
        . 'html.prt-dark .banner .nav-primary .nav a,html.prt-dark .banner .menu-toggle,'
        . 'html.prt-dark .banner .prt-theme-toggle,html.prt-dark .banner .social a{color:#f3f1ea;}'
        . 'html.prt-dark .banner .brand small{color:#9a978d;}'
        . 'html.prt-dark .banner .nav-primary .nav a:hover{color:#fff;}'
        . "</style>\n";
}, 20);
