<?php

/**
 * Announcement bar: scheduled (optional start/end date), dismissible, themable.
 * Renders at the very top of <body> via wp_body_open/get_header (guarded once).
 */

namespace App;

function prt_ann_defaults()
{
    return [
        'prt_ann_enable'  => false,
        'prt_ann_text'    => '',
        'prt_ann_lurl'    => '',
        'prt_ann_ltext'   => '',
        'prt_ann_bg'      => '#17191e',
        'prt_ann_color'   => '#ffffff',
        'prt_ann_dismiss' => true,
        'prt_ann_hide_mobile' => false,
        'prt_ann_start'   => '',
        'prt_ann_end'     => '',
    ];
}

function prt_ann($k)
{
    $d = prt_ann_defaults();
    return get_theme_mod($k, $d[$k] ?? null);
}

add_action('customize_register', function ($wp) {
    if (! $wp->get_panel('prt_theme_options')) {
        $wp->add_panel('prt_theme_options', ['title' => __('Theme Options', 'pressroot'), 'priority' => 30]);
    }
    $wp->add_section('prt_ann_section', [
        'title' => __('Announcement Bar', 'pressroot'),
        'panel' => 'prt_theme_options',
        'description' => __('A site-wide bar at the very top. Optionally schedule it with a start/end date.', 'pressroot'),
    ]);

    $wp->add_setting('prt_ann_enable', ['default' => false, 'sanitize_callback' => 'wp_validate_boolean']);
    $wp->add_control('prt_ann_enable', ['label' => __('Show announcement bar', 'pressroot'), 'section' => 'prt_ann_section', 'type' => 'checkbox']);

    $wp->add_setting('prt_ann_text', ['default' => '', 'sanitize_callback' => 'wp_kses_post']);
    $wp->add_control('prt_ann_text', ['label' => __('Message', 'pressroot'), 'section' => 'prt_ann_section', 'type' => 'text']);

    $wp->add_setting('prt_ann_ltext', ['default' => '', 'sanitize_callback' => 'sanitize_text_field']);
    $wp->add_control('prt_ann_ltext', ['label' => __('Link text', 'pressroot'), 'section' => 'prt_ann_section', 'type' => 'text']);
    $wp->add_setting('prt_ann_lurl', ['default' => '', 'sanitize_callback' => 'esc_url_raw']);
    $wp->add_control('prt_ann_lurl', ['label' => __('Link URL', 'pressroot'), 'section' => 'prt_ann_section', 'type' => 'url']);

    foreach ([['prt_ann_bg', __('Background', 'pressroot'), '#17191e'], ['prt_ann_color', __('Text color', 'pressroot'), '#ffffff']] as $col) {
        $wp->add_setting($col[0], ['default' => $col[2], 'sanitize_callback' => 'sanitize_hex_color']);
        $wp->add_control(new \WP_Customize_Color_Control($wp, $col[0], ['label' => $col[1], 'section' => 'prt_ann_section']));
    }

    $wp->add_setting('prt_ann_dismiss', ['default' => true, 'sanitize_callback' => 'wp_validate_boolean']);
    $wp->add_control('prt_ann_dismiss', ['label' => __('Allow visitors to dismiss', 'pressroot'), 'section' => 'prt_ann_section', 'type' => 'checkbox']);

    $wp->add_setting('prt_ann_hide_mobile', ['default' => false, 'sanitize_callback' => 'wp_validate_boolean']);
    $wp->add_control('prt_ann_hide_mobile', ['label' => __('Hide on mobile', 'pressroot'), 'description' => __('Hides the bar on screens 640px and narrower.', 'pressroot'), 'section' => 'prt_ann_section', 'type' => 'checkbox']);

    $wp->add_setting('prt_ann_start', ['default' => '', 'sanitize_callback' => 'sanitize_text_field']);
    $wp->add_control('prt_ann_start', ['label' => __('Start date (optional)', 'pressroot'), 'section' => 'prt_ann_section', 'type' => 'date']);
    $wp->add_setting('prt_ann_end', ['default' => '', 'sanitize_callback' => 'sanitize_text_field']);
    $wp->add_control('prt_ann_end', ['label' => __('End date (optional)', 'pressroot'), 'section' => 'prt_ann_section', 'type' => 'date']);
}, 21);

function prt_ann_render()
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    $text = trim((string) prt_ann('prt_ann_text'));
    if (! prt_ann('prt_ann_enable') || $text === '') {
        return;
    }
    $today = current_time('Y-m-d');
    $start = prt_ann('prt_ann_start');
    $end   = prt_ann('prt_ann_end');
    if ($start && $today < $start) {
        return;
    }
    if ($end && $today > $end) {
        return;
    }

    $bg      = sanitize_hex_color(prt_ann('prt_ann_bg')) ?: '#17191e';
    $col     = sanitize_hex_color(prt_ann('prt_ann_color')) ?: '#ffffff';
    $dismiss = (bool) prt_ann('prt_ann_dismiss');
    $hideMob = (bool) prt_ann('prt_ann_hide_mobile');
    $ltext   = (string) prt_ann('prt_ann_ltext');
    $lurl    = (string) prt_ann('prt_ann_lurl');
    $ver     = substr(md5((string) $start . (string) $end . $text . $ltext . $lurl), 0, 8);

    $cls = 'prt-ann' . ($hideMob ? ' prt-ann--hide-mobile' : '');
    echo '<div class="' . esc_attr($cls) . '" data-ver="' . esc_attr($ver) . '" style="background:' . esc_attr($bg) . ';color:' . esc_attr($col) . '">';
    echo '<div class="prt-ann-inner">';
    echo '<span class="prt-ann-msg">' . wp_kses_post($text) . '</span>';
    if ($lurl !== '' && $ltext !== '') {
        echo ' <a class="prt-ann-link" href="' . esc_url($lurl) . '" style="color:inherit;text-decoration:underline;">' . esc_html($ltext) . ' &rarr;</a>';
    }
    echo '</div>';
    if ($dismiss) {
        echo '<button class="prt-ann-x" aria-label="' . esc_attr__('Dismiss', 'pressroot') . '" style="color:' . esc_attr($col) . '">&times;</button>';
    }
    echo '</div>';
    echo '<style>.prt-ann{position:relative;font-size:14px;}.prt-ann-inner{max-width:1180px;margin:0 auto;padding:8px 40px;text-align:center;}.prt-ann-inner *{color:inherit;}.prt-ann-x{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:0;font-size:20px;line-height:1;cursor:pointer;opacity:.8;}.prt-ann-x:hover{opacity:1;}.prt-ann.is-hidden{display:none;}@media(max-width:640px){.prt-ann--hide-mobile{display:none!important;}}</style>';
    if ($dismiss) {
        echo "<script>(function(){var b=document.querySelector('.prt-ann');if(!b)return;var k='prt-ann-'+b.getAttribute('data-ver');try{if(localStorage.getItem(k)==='1'){b.classList.add('is-hidden');}}catch(e){}var x=b.querySelector('.prt-ann-x');if(x)x.addEventListener('click',function(){b.classList.add('is-hidden');try{localStorage.setItem(k,'1');}catch(e){}});})();</script>";
    }
}
add_action('wp_body_open', __NAMESPACE__ . '\\prt_ann_render', 5);
add_action('get_header', __NAMESPACE__ . '\\prt_ann_render', 5);
