<?php

/**
 * Customizer housekeeping (runs last, priority 999).
 * Reorganises the Theme Options panel for a cleaner, more discoverable UI without
 * touching the source modules that register each control:
 *   - splits the overloaded "Navigation" section into focused sections
 *     (Navigation Â· Social Icons Â· Responsive), and re-parents stray controls,
 *   - orders every section logically,
 *   - adds a short description to each section.
 */

namespace App;

add_action('customize_register', function ($wp) {
    if (! $wp->get_panel('prt_theme_options')) {
        return;
    }

    // --- New grouping sections ---
    if (! $wp->get_section('prt_social_section')) {
        $wp->add_section('prt_social_section', [
            'title'       => __('Social Icons', 'pressroot'),
            'panel'       => 'prt_theme_options',
            'description' => __('Where social icons appear, their style, and your account URLs.', 'pressroot'),
        ]);
    }
    if (! $wp->get_section('prt_responsive_section')) {
        $wp->add_section('prt_responsive_section', [
            'title'       => __('Responsive (mobile & tablet)', 'pressroot'),
            'panel'       => 'prt_theme_options',
            'description' => __('Per-device visibility and widths. Mobile â‰¤640px Â· tablet 641â€“1024px.', 'pressroot'),
        ]);
    }

    // --- Re-parent controls into better homes (runtime only) ---
    $move = [
        'prt_social_section' => [
            'prt_nav_social', 'prt_nav_social_align', 'prt_social_style', 'prt_social_size', 'prt_social_shape',
            'prt_social_color', 'prt_social_bg', 'prt_social_hover',
            'prt_social_linkedin', 'prt_social_github', 'prt_social_devto', 'prt_social_x', 'prt_social_bluesky',
            'prt_social_youtube', 'prt_social_instagram', 'prt_social_facebook', 'prt_social_mastodon', 'prt_social_email', 'prt_social_rss',
        ],
        'prt_responsive_section' => [
            'prt_social_nav_hide_mobile', 'prt_social_nav_hide_desktop', 'prt_social_top_hide_mobile',
            'prt_topcta_hide_mobile', 'prt_navcta_hide_mobile', 'prt_navcta_hide_tablet',
            'prt_topbar_oneline_tablet', 'prt_logo_shrink_mobile', 'prt_menu_label_hide_mobile',
            'prt_topbar_width_tablet', 'prt_topbar_width_mobile', 'prt_nav_width_tablet', 'prt_nav_width_mobile',
            'prt_msgbar_width_tablet', 'prt_msgbar_width_mobile',
        ],
        'prt_headerlayout_section' => [
            'prt_logo_align', 'prt_darkicon_align', 'prt_popbtn_align', 'prt_cta_align',
            'prt_bar_ann', 'prt_bar_top', 'prt_bar_nav',
        ],
        'prt_popout_section' => [
            'prt_popout_width', 'prt_popout_cols', 'prt_popout_block_cols',
            'prt_pop_align', 'prt_pop_pad_y', 'prt_pop_font', 'prt_pop_weight', 'prt_pop_transform', 'prt_pop_gap',
        ],
    ];
    foreach ($move as $section => $ids) {
        $base = 30; // place moved controls after a section's native controls
        foreach ($ids as $i => $id) {
            $c = $wp->get_control($id);
            if ($c) {
                $c->section  = $section;
                $c->priority = $base + $i;
            }
        }
    }

    // --- Logical section order within Theme Options ---
    $order = [
        'prt_colors' => 20, 'prt_type' => 30, 'prt_type_adv' => 35,
        'prt_headerlayout_section' => 40, 'prt_nav_section' => 45, 'prt_popout_section' => 50,
        'prt_social_section' => 55, 'prt_topbar_section' => 60, 'prt_ann_section' => 65,
        'prt_hero_section' => 70, 'prt_anim_section' => 75, 'prt_footer_section' => 80,
        'prt_content_section' => 85, 'prt_layout_section' => 90, 'prt_responsive_section' => 95,
        'prt_dark_section' => 100, 'prt_seo_section' => 110, 'prt_perf_section' => 120,
        'prt_code_section' => 130, 'prt_news_section' => 140, 'prt_cookie_section' => 150,
        'prt_extras_section' => 160, 'prt_wl_section' => 170,
    ];
    foreach ($order as $sid => $prio) {
        $s = $wp->get_section($sid);
        if ($s) {
            $s->priority = $prio;
        }
    }

    // --- Short, dev-friendly section descriptions (only if one isn't already set) ---
    $desc = [
        'prt_nav_section'         => __('Primary menu layout (flexbox) and link styling.', 'pressroot'),
        'prt_popout_section'      => __('Off-canvas menu: breakpoints, panel style, columns and item styling.', 'pressroot'),
        'prt_topbar_section'      => __('Slim utility bar above the main navigation.', 'pressroot'),
        'prt_ann_section'         => __('Site-wide announcement bar â€” schedulable and dismissible.', 'pressroot'),
        'prt_headerlayout_section' => __('Header sizing, element placement, sticky/transparent behaviour and bar order.', 'pressroot'),
        'prt_layout_section'      => __('Content width and sidebar per content type.', 'pressroot'),
        'prt_content_section'     => __('Editable copy for the CTA band and page intros.', 'pressroot'),
        'prt_anim_section'        => __('On-scroll reveal animations applied site-wide.', 'pressroot'),
    ];
    foreach ($desc as $sid => $d) {
        $s = $wp->get_section($sid);
        if ($s && empty($s->description)) {
            $s->description = $d;
        }
    }
}, 999);
