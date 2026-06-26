<?php

/**
 * Integrations & custom code:
 *  - Head / body-open / footer code injection (analytics, pixels, verification).
 *  - Live Custom CSS.
 *  - [prt_newsletter] shortcode (Mailchimp-compatible embedded form).
 *  - Cookie-consent notice (dismissible, stored locally).
 */

namespace App;

/** Admins with unfiltered_html keep raw markup; others get kses. */
function prt_sanitize_code($value)
{
    return current_user_can('unfiltered_html') ? $value : wp_kses_post($value);
}

add_action('customize_register', function ($wp) {
    if (! $wp->get_panel('prt_theme_options')) {
        $wp->add_panel('prt_theme_options', ['title' => __('Theme Options', 'pressroot'), 'priority' => 30]);
    }

    /* ---- Custom Code ---- */
    $wp->add_section('prt_code_section', ['title' => __('Custom Code', 'pressroot'), 'panel' => 'prt_theme_options', 'description' => __('Inject scripts/markup. Use for analytics, pixels, and verification tags.', 'pressroot')]);
    foreach ([
        'prt_code_head'   => __('Head code (before </head>)', 'pressroot'),
        'prt_code_body'   => __('Body code (after <body>)', 'pressroot'),
        'prt_code_footer' => __('Footer code (before </body>)', 'pressroot'),
        'prt_custom_css'  => __('Custom CSS', 'pressroot'),
    ] as $id => $label) {
        $wp->add_setting($id, ['default' => '', 'sanitize_callback' => __NAMESPACE__ . '\\prt_sanitize_code']);
        $wp->add_control($id, ['label' => $label, 'section' => 'prt_code_section', 'type' => 'textarea']);
    }

    /* ---- Newsletter ---- */
    $wp->add_section('prt_news_section', ['title' => __('Newsletter', 'pressroot'), 'panel' => 'prt_theme_options', 'description' => __('Settings for the [prt_newsletter] shortcode. Paste your Mailchimp form action URL.', 'pressroot')]);
    $wp->add_setting('prt_news_action', ['default' => '', 'sanitize_callback' => 'esc_url_raw']);
    $wp->add_control('prt_news_action', ['label' => __('Form action URL (Mailchimp)', 'pressroot'), 'section' => 'prt_news_section', 'type' => 'url']);
    $wp->add_setting('prt_news_heading', ['default' => __('Subscribe', 'pressroot'), 'sanitize_callback' => 'sanitize_text_field']);
    $wp->add_control('prt_news_heading', ['label' => __('Heading', 'pressroot'), 'section' => 'prt_news_section', 'type' => 'text']);
    $wp->add_setting('prt_news_note', ['default' => __('No spam. Unsubscribe anytime.', 'pressroot'), 'sanitize_callback' => 'sanitize_text_field']);
    $wp->add_control('prt_news_note', ['label' => __('Sub-note', 'pressroot'), 'section' => 'prt_news_section', 'type' => 'text']);
    $wp->add_setting('prt_news_button', ['default' => __('Subscribe', 'pressroot'), 'sanitize_callback' => 'sanitize_text_field']);
    $wp->add_control('prt_news_button', ['label' => __('Button text', 'pressroot'), 'section' => 'prt_news_section', 'type' => 'text']);

    /* ---- Cookie notice ---- */
    $wp->add_section('prt_cookie_section', ['title' => __('Cookie Notice', 'pressroot'), 'panel' => 'prt_theme_options']);
    $wp->add_setting('prt_cookie_enable', ['default' => false, 'sanitize_callback' => 'wp_validate_boolean']);
    $wp->add_control('prt_cookie_enable', ['label' => __('Show cookie notice', 'pressroot'), 'section' => 'prt_cookie_section', 'type' => 'checkbox']);
    $wp->add_setting('prt_cookie_text', ['default' => __('We use cookies to improve your experience.', 'pressroot'), 'sanitize_callback' => 'sanitize_text_field']);
    $wp->add_control('prt_cookie_text', ['label' => __('Message', 'pressroot'), 'section' => 'prt_cookie_section', 'type' => 'text']);
    $wp->add_setting('prt_cookie_btn', ['default' => __('Got it', 'pressroot'), 'sanitize_callback' => 'sanitize_text_field']);
    $wp->add_control('prt_cookie_btn', ['label' => __('Accept button', 'pressroot'), 'section' => 'prt_cookie_section', 'type' => 'text']);
    $wp->add_setting('prt_cookie_lurl', ['default' => '', 'sanitize_callback' => 'esc_url_raw']);
    $wp->add_control('prt_cookie_lurl', ['label' => __('Policy link URL', 'pressroot'), 'section' => 'prt_cookie_section', 'type' => 'url']);
    $wp->add_setting('prt_cookie_ltext', ['default' => __('Learn more', 'pressroot'), 'sanitize_callback' => 'sanitize_text_field']);
    $wp->add_control('prt_cookie_ltext', ['label' => __('Policy link text', 'pressroot'), 'section' => 'prt_cookie_section', 'type' => 'text']);
}, 27);

/* ---- Code injection ---- */
add_action('wp_head', function () {
    $c = get_theme_mod('prt_code_head', '');
    if ($c) {
        echo "\n" . $c . "\n"; // phpcs:ignore -- intentional raw injection by admin
    }
}, 99);

function prt_inject_body()
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    $c = get_theme_mod('prt_code_body', '');
    if ($c) {
        echo "\n" . $c . "\n"; // phpcs:ignore
    }
}
add_action('wp_body_open', __NAMESPACE__ . '\\prt_inject_body', 20);
add_action('get_header', __NAMESPACE__ . '\\prt_inject_body', 20);

add_action('wp_footer', function () {
    $c = get_theme_mod('prt_code_footer', '');
    if ($c) {
        echo "\n" . $c . "\n"; // phpcs:ignore
    }
}, 99);

/* ---- Custom CSS (last, so it wins) ---- */
add_action('prt_head_end', function () {
    $css = trim((string) get_theme_mod('prt_custom_css', ''));
    if ($css !== '') {
        echo "\n<style id=\"prt-custom-css\">" . wp_strip_all_tags($css) . "</style>\n";
    }
}, 99);

/* ---- Newsletter shortcode ---- */
add_shortcode('prt_newsletter', function ($atts) {
    $a = shortcode_atts(['heading' => '', 'button' => '', 'note' => ''], $atts);
    $action  = get_theme_mod('prt_news_action', '');
    $heading = $a['heading'] ?: get_theme_mod('prt_news_heading', __('Subscribe', 'pressroot'));
    $button  = $a['button'] ?: get_theme_mod('prt_news_button', __('Subscribe', 'pressroot'));
    $note    = $a['note'] ?: get_theme_mod('prt_news_note', '');

    $out  = '<div class="prt-news">';
    if ($heading) {
        $out .= '<h3 class="prt-news-h">' . esc_html($heading) . '</h3>';
    }
    $out .= '<form class="prt-news-form" action="' . esc_url($action) . '" method="post" target="_blank" novalidate>';
    $out .= '<input type="email" name="EMAIL" required placeholder="' . esc_attr__('you@example.com', 'pressroot') . '" aria-label="' . esc_attr__('Email address', 'pressroot') . '">';
    // honeypot (Mailchimp anti-bot)
    $out .= '<div style="position:absolute;left:-5000px" aria-hidden="true"><input type="text" name="b_honeypot" tabindex="-1" value=""></div>';
    $out .= '<button type="submit" class="btn">' . esc_html($button) . '</button>';
    $out .= '</form>';
    if ($note) {
        $out .= '<p class="prt-news-note">' . esc_html($note) . '</p>';
    }
    $out .= '</div>';
    return $out;
});

/* ---- Cookie notice ---- */
add_action('wp_footer', function () {
    if (! get_theme_mod('prt_cookie_enable', false)) {
        return;
    }
    $text = esc_html(get_theme_mod('prt_cookie_text', ''));
    $btn  = esc_html(get_theme_mod('prt_cookie_btn', __('Got it', 'pressroot')));
    $lurl = esc_url(get_theme_mod('prt_cookie_lurl', ''));
    $ltxt = esc_html(get_theme_mod('prt_cookie_ltext', ''));
    $link = ($lurl && $ltxt) ? ' <a href="' . $lurl . '">' . $ltxt . '</a>' : '';
    echo '<div class="prt-cookie" role="dialog" aria-label="' . esc_attr__('Cookie notice', 'pressroot') . '"><p>' . $text . $link . '</p><button class="btn prt-cookie-ok">' . $btn . '</button></div>';
    echo '<style>.prt-cookie{position:fixed;left:16px;right:16px;bottom:16px;max-width:560px;margin:0 auto;background:var(--color-ink,#17191e);color:#fff;padding:14px 16px;border-radius:12px;display:flex;gap:14px;align-items:center;justify-content:space-between;z-index:95;box-shadow:0 10px 30px rgba(0,0,0,.25);font-size:14px;}.prt-cookie p{margin:0;}.prt-cookie a{color:#fff;text-decoration:underline;}.prt-cookie.is-hidden{display:none;}</style>';
    echo "<script>(function(){var b=document.querySelector('.prt-cookie');if(!b)return;var k='prt-cookie-ok';try{if(localStorage.getItem(k)==='1'){b.classList.add('is-hidden');return;}}catch(e){}var ok=b.querySelector('.prt-cookie-ok');if(ok)ok.addEventListener('click',function(){b.classList.add('is-hidden');try{localStorage.setItem(k,'1');}catch(e){}});})();</script>";
}, 60);

add_action('prt_head_end', function () {
    echo "\n<style id=\"prt-news-css\">.prt-news{max-width:460px;}.prt-news-h{margin:0 0 10px;}.prt-news-form{display:flex;gap:8px;flex-wrap:wrap;}.prt-news-form input[type=email]{flex:1 1 200px;padding:11px 14px;border:1px solid var(--color-line,#e6e2d9);border-radius:8px;font:inherit;}.prt-news-note{font-size:12px;color:var(--color-muted,#5c636c);margin:8px 0 0;}</style>\n";
}, 19);
