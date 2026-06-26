<?php

/**
 * Bars -> widgets consolidation. The top bar, message bar, and navbar are now
 * built from their widget areas (Appearance -> Widgets) using the Bar blocks.
 * This strips the now-redundant / conflicting Customizer controls so there's a
 * single, clean way to configure each bar's content. The underlying settings
 * remain (harmless) so nothing fatals; the controls are simply removed.
 */

namespace App;

add_action('customize_register', function ($wp) {
    // Top bar -> pure widget area (remove the whole section + its controls).
    foreach ([
        'prt_topbar_enable', 'prt_topbar_contact', 'prt_topbar_show_social',
        'prt_topbar_cta_text', 'prt_topbar_cta_url', 'prt_topbar_bg', 'prt_topbar_bg_custom',
        'prt_topbar_text', 'prt_topbar_text_custom', 'prt_topbar_align', 'prt_topbar_width',
    ] as $c) {
        $wp->remove_control($c);
    }
    $wp->remove_section('prt_topbar_section');

    // Message bar -> content via the "Message bar" widget area.
    // Kept: enable, background, text color, dismiss, schedule (not expressible as blocks).
    foreach (['prt_ann_text', 'prt_ann_ltext', 'prt_ann_lurl', 'prt_msgbar_align', 'prt_msgbar_width'] as $c) {
        $wp->remove_control($c);
    }

    // Navbar social + CTA -> "Navigation bar" widget area (Bar blocks).
    // Removes the placement toggle + legacy/duplicate social & CTA controls.
    foreach (['prt_social_location', 'prt_social_align', 'prt_social_order', 'prt_show_cta', 'prt_cta_text', 'prt_cta_url'] as $c) {
        $wp->remove_control($c);
    }
}, 999);
