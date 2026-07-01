<?php

/**
 * Theme Options - colors, fonts, layout width, header CTA, footer text.
 * Emits CSS-variable overrides after app.css so changes apply without a rebuild.
 */

namespace App;

function prt_defaults()
{
    return [
        'prt_color_action' => '#2f6b4e',
        'prt_color_paper'  => '#fbfaf7',
        'prt_color_ink'    => '#17191e',
        'prt_color_body'   => '#2b2f36',
        'prt_font_heading' => 'Geist',
        'prt_font_body'    => 'Inter',
        'prt_container'    => 1180,
        'prt_show_cta'     => true,
        'prt_cta_text'     => 'Find me on Dev.to',
        'prt_cta_url'      => 'https://dev.to/mattbuildsapps',
        'prt_footer_text'  => '',
    ];
}

function prt_mod($key)
{
    $d = prt_defaults();
    return get_theme_mod($key, $d[$key] ?? '');
}

/**
 * Available font options.
 * Format: 'Display Name' => ['Google Fonts CSS2 family param (null = system)', 'CSS font stack']
 * The wp_enqueue_scripts hook below auto-loads any selected font from Google Fonts.
 */
function prt_fonts()
{
    return apply_filters('matthummel/fonts', [

        /* ── Modern Sans ───────────────────────────────────────────────── */
        'Geist'               => ['Geist:wght@400;500;600;700',                        '"Geist", system-ui, sans-serif'],
        'Inter'               => ['Inter:wght@400;500;600;700',                        '"Inter", system-ui, sans-serif'],
        'Inter Tight'         => ['Inter+Tight:wght@400;500;600;700',                  '"Inter Tight", system-ui, sans-serif'],
        'DM Sans'             => ['DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,700',   '"DM Sans", system-ui, sans-serif'],
        'Plus Jakarta Sans'   => ['Plus+Jakarta+Sans:wght@400;500;600;700',            '"Plus Jakarta Sans", system-ui, sans-serif'],
        'Outfit'              => ['Outfit:wght@400;500;600;700',                        '"Outfit", system-ui, sans-serif'],
        'Nunito'              => ['Nunito:wght@400;500;600;700',                        '"Nunito", system-ui, sans-serif'],
        'Nunito Sans'         => ['Nunito+Sans:wght@400;500;600;700',                  '"Nunito Sans", system-ui, sans-serif'],

        /* ── Geometric / Grotesk ────────────────────────────────────────── */
        'Space Grotesk'       => ['Space+Grotesk:wght@400;500;600;700',                '"Space Grotesk", system-ui, sans-serif'],
        'Schibsted Grotesk'   => ['Schibsted+Grotesk:wght@400;500;600;700',            '"Schibsted Grotesk", system-ui, sans-serif'],
        'Bricolage Grotesque' => ['Bricolage+Grotesque:opsz,wght@12..96,400..800',     '"Bricolage Grotesque", system-ui, sans-serif'],
        'Sora'                => ['Sora:wght@400;500;600;700',                          '"Sora", system-ui, sans-serif'],
        'Poppins'             => ['Poppins:wght@400;500;600;700',                       '"Poppins", system-ui, sans-serif'],
        'Montserrat'          => ['Montserrat:wght@400;500;600;700',                    '"Montserrat", system-ui, sans-serif'],
        'Raleway'             => ['Raleway:wght@400;500;600;700',                       '"Raleway", system-ui, sans-serif'],

        /* ── Humanist Sans ──────────────────────────────────────────────── */
        'Work Sans'           => ['Work+Sans:wght@400;500;600;700',                    '"Work Sans", system-ui, sans-serif'],
        'Lato'                => ['Lato:wght@400;700',                                  '"Lato", system-ui, sans-serif'],
        'Open Sans'           => ['Open+Sans:wght@400;500;600;700',                    '"Open Sans", system-ui, sans-serif'],
        'Source Sans 3'       => ['Source+Sans+3:wght@400;500;600;700',                '"Source Sans 3", system-ui, sans-serif'],
        'Roboto'              => ['Roboto:wght@400;500;700',                            '"Roboto", system-ui, sans-serif'],
        'Roboto Condensed'    => ['Roboto+Condensed:wght@400;500;700',                 '"Roboto Condensed", system-ui, sans-serif'],
        'Noto Sans'           => ['Noto+Sans:wght@400;500;600;700',                    '"Noto Sans", system-ui, sans-serif'],

        /* ── Classic Serif ──────────────────────────────────────────────── */
        'Fraunces'            => ['Fraunces:opsz,wght@9..144,400..700',                '"Fraunces", Georgia, serif'],
        'Playfair Display'    => ['Playfair+Display:wght@400;500;600;700',             '"Playfair Display", Georgia, serif'],
        'Lora'                => ['Lora:wght@400;500;600;700',                          '"Lora", Georgia, serif'],
        'Merriweather'        => ['Merriweather:wght@400;700',                          '"Merriweather", Georgia, serif'],
        'Cormorant Garamond'  => ['Cormorant+Garamond:wght@400;500;600;700',           '"Cormorant Garamond", Georgia, serif'],
        'EB Garamond'         => ['EB+Garamond:wght@400;500;600;700',                  '"EB Garamond", Georgia, serif'],
        'DM Serif Display'    => ['DM+Serif+Display',                                   '"DM Serif Display", Georgia, serif'],
        'Crimson Pro'         => ['Crimson+Pro:wght@400;500;600;700',                  '"Crimson Pro", Georgia, serif'],
        'Libre Baskerville'   => ['Libre+Baskerville:wght@400;700',                    '"Libre Baskerville", Georgia, serif'],
        'PT Serif'            => ['PT+Serif:wght@400;700',                              '"PT Serif", Georgia, serif'],

        /* ── Display / Expressive ───────────────────────────────────────── */
        'Bebas Neue'          => ['Bebas+Neue',                                          '"Bebas Neue", system-ui, sans-serif'],
        'Oswald'              => ['Oswald:wght@400;500;600;700',                        '"Oswald", system-ui, sans-serif'],
        'Anton'               => ['Anton',                                               '"Anton", system-ui, sans-serif'],
        'Abril Fatface'       => ['Abril+Fatface',                                       '"Abril Fatface", Georgia, serif'],

        /* ── Monospace ──────────────────────────────────────────────────── */
        'JetBrains Mono'      => ['JetBrains+Mono:wght@400;500;700',                   '"JetBrains Mono", "Courier New", monospace'],
        'Fira Code'           => ['Fira+Code:wght@400;500;700',                         '"Fira Code", "Courier New", monospace'],
        'Source Code Pro'     => ['Source+Code+Pro:wght@400;500;700',                  '"Source Code Pro", "Courier New", monospace'],
        'IBM Plex Mono'       => ['IBM+Plex+Mono:wght@400;500;700',                    '"IBM Plex Mono", "Courier New", monospace'],
        'Roboto Mono'         => ['Roboto+Mono:wght@400;500;700',                       '"Roboto Mono", "Courier New", monospace'],

        /* ── System ─────────────────────────────────────────────────────── */
        'System'              => [null, 'system-ui, -apple-system, sans-serif'],
    ]);
}

add_action('customize_register', function ($wp) {
    $d = prt_defaults();

    $wp->add_panel('prt_theme_options', [
        'title'    => __('Theme Options', 'pressroot'),
        'priority' => 30,
    ]);

    /* Colors */
    $wp->add_section('prt_colors', ['title' => __('Colors', 'pressroot'), 'panel' => 'prt_theme_options']);
    $colors = [
        'prt_color_action' => __('Brand / buttons', 'pressroot'),
        'prt_color_paper'  => __('Page background', 'pressroot'),
        'prt_color_ink'    => __('Headings', 'pressroot'),
        'prt_color_body'   => __('Body text', 'pressroot'),
    ];
    foreach ($colors as $id => $label) {
        $wp->add_setting($id, ['default' => $d[$id], 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'refresh']);
        $wp->add_control(new \WP_Customize_Color_Control($wp, $id, ['label' => $label, 'section' => 'prt_colors']));
    }

    /* Typography */
    $wp->add_section('prt_type', ['title' => __('Typography', 'pressroot'), 'panel' => 'prt_theme_options']);
    $choices = array_combine(array_keys(prt_fonts()), array_keys(prt_fonts()));
    $wp->add_setting('prt_font_heading', ['default' => $d['prt_font_heading'], 'sanitize_callback' => 'sanitize_text_field']);
    $wp->add_control('prt_font_heading', ['label' => __('Heading font', 'pressroot'), 'section' => 'prt_type', 'type' => 'select', 'choices' => $choices]);
    $wp->add_setting('prt_font_body', ['default' => $d['prt_font_body'], 'sanitize_callback' => 'sanitize_text_field']);
    $wp->add_control('prt_font_body', ['label' => __('Body font', 'pressroot'), 'section' => 'prt_type', 'type' => 'select', 'choices' => $choices]);

    /* Layout width */
    $wp->add_setting('prt_container', ['default' => $d['prt_container'], 'sanitize_callback' => 'absint']);
    $wp->add_control('prt_container', ['label' => __('Content width', 'pressroot'), 'section' => 'prt_layout_section', 'type' => 'select', 'choices' => prt_width_options()]);

    /* Header */
    $wp->add_setting('prt_show_cta', ['default' => $d['prt_show_cta'], 'sanitize_callback' => 'wp_validate_boolean']);
    $wp->add_control('prt_show_cta', ['label' => __('Show header button', 'pressroot'), 'section' => 'prt_headerlayout_section', 'type' => 'checkbox']);
    $wp->add_setting('prt_cta_text', ['default' => $d['prt_cta_text'], 'sanitize_callback' => 'sanitize_text_field']);
    $wp->add_control('prt_cta_text', ['label' => __('Button text', 'pressroot'), 'section' => 'prt_headerlayout_section', 'type' => 'text']);
    $wp->add_setting('prt_cta_url', ['default' => $d['prt_cta_url'], 'sanitize_callback' => 'esc_url_raw']);
    $wp->add_control('prt_cta_url', ['label' => __('Button URL', 'pressroot'), 'section' => 'prt_headerlayout_section', 'type' => 'url']);

    /* Footer */
    $wp->add_setting('prt_footer_text', ['default' => $d['prt_footer_text'], 'sanitize_callback' => 'wp_kses_post']);
    $wp->add_control('prt_footer_text', ['label' => __('Footer tagline', 'pressroot'), 'section' => 'prt_footer_section', 'type' => 'textarea']);
});

/* Wire values into the theme's existing filter hooks */
add_filter('matthummel/header_cta_label', fn () => prt_mod('prt_cta_text'));
add_filter('matthummel/header_cta_url', fn () => prt_mod('prt_cta_url'));
add_filter('matthummel/show_header_cta', fn () => (bool) prt_mod('prt_show_cta'));
add_filter('matthummel/footer_text', fn () => prt_mod('prt_footer_text'));

/* Load the selected heading + body fonts from Google Fonts */
add_action('wp_enqueue_scripts', function () {
    $fonts    = prt_fonts();
    $picked   = array_unique([prt_mod('prt_font_heading'), prt_mod('prt_font_body')]);
    $families = [];
    foreach ($picked as $p) {
        if (! empty($fonts[$p][0])) {
            $families[] = $fonts[$p][0];
        }
    }
    if ($families) {
        wp_enqueue_style(
            'matthummel-fonts-custom',
            'https://fonts.googleapis.com/css2?family=' . implode('&family=', $families) . '&display=swap',
            [],
            null
        );
    }
}, 6);

/* Emit CSS-variable overrides AFTER app.css (fires via prt_head_end in the layout) */
add_action('prt_head_end', function () {
    $fonts = prt_fonts();
    $h = $fonts[prt_mod('prt_font_heading')][1] ?? $fonts['Geist'][1];
    $b = $fonts[prt_mod('prt_font_body')][1] ?? $fonts['Inter'][1];

    $css = ':root{'
        . '--color-green:' . prt_mod('prt_color_action') . ';'
        . '--color-khaki:' . prt_mod('prt_color_paper') . ';'
        . '--color-ink:' . prt_mod('prt_color_ink') . ';'
        . '--color-heading:' . prt_mod('prt_color_ink') . ';'
        . '--color-body:' . prt_mod('prt_color_body') . ';'
        . '--font-display:' . $h . ';'
        . '--font-body:' . $b . ';'
        . '}'
        . '.container,.rule,.banner{max-width:' . absint(prt_mod('prt_container')) . 'px}';

    echo "\n<style id=\"prt-customizer\">" . $css . "</style>\n";
});


/** Standard content-width options (px) for select controls. */
function prt_width_options($include_preset = false)
{
    $opts = [];
    if ($include_preset) {
        $opts['0'] = __('Use preset (default)', 'pressroot');
    }
    return $opts + [
        '720'  => '720px (narrow)',
        '960'  => '960px (small)',
        '1080' => '1080px (medium)',
        '1140' => '1140px (standard)',
        '1180' => '1180px (default)',
        '1200' => '1200px',
        '1280' => '1280px (large)',
        '1320' => '1320px',
        '1440' => '1440px (extra wide)',
        '1600' => '1600px (max)',
    ];
}
