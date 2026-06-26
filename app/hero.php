<?php

/**
 * Hero layout + site-wide scroll animations.
 *  - Hero: columns (1â€“3), content flex position (H/V), side image + 2nd image,
 *    background cover (with overlay + min-height), and an entrance animation.
 *  - Animations: on-scroll reveal for sections site-wide (IntersectionObserver),
 *    with a choice of effect + speed. Respects prefers-reduced-motion and degrades
 *    gracefully without JS (content only hides once <html> is marked anim-ready).
 */

namespace App;

/** Shared list of animation effects. */
function prt_anim_effects()
{
    return [
        'none'        => __('None', 'pressroot'),
        'fade-up'     => __('Fade up', 'pressroot'),
        'fade-in'     => __('Fade in', 'pressroot'),
        'zoom-in'     => __('Zoom in', 'pressroot'),
        'pop'         => __('Pop', 'pressroot'),
        'blur-in'     => __('Blur in', 'pressroot'),
        'slide-left'  => __('Slide from left', 'pressroot'),
        'slide-right' => __('Slide from right', 'pressroot'),
    ];
}

add_action('customize_register', function ($wp) {
    if (! $wp->get_panel('prt_theme_options')) {
        $wp->add_panel('prt_theme_options', ['title' => __('Theme Options', 'pressroot'), 'priority' => 30]);
    }

    $sel = function ($id, $label, $choices, $default, $section) use ($wp) {
        $wp->add_setting($id, ['default' => $default, 'sanitize_callback' => 'sanitize_text_field']);
        $wp->add_control($id, ['label' => $label, 'section' => $section, 'type' => 'select', 'choices' => $choices]);
    };

    /* ---- Hero ---- */
    $wp->add_section('prt_hero_section', [
        'title'       => __('Hero', 'pressroot'),
        'panel'       => 'prt_theme_options',
        'description' => __('Homepage hero: columns, content position, images, background cover and entrance animation.', 'pressroot'),
    ]);

    // Editable hero copy (defaults match the built-in text so nothing changes until edited).
    $wp->add_setting('prt_hero_eyebrow', ['default' => __('Web Â· WordPress Â· Power Platform', 'pressroot'), 'sanitize_callback' => 'sanitize_text_field']);
    $wp->add_control('prt_hero_eyebrow', ['label' => __('Eyebrow (small label above title)', 'pressroot'), 'section' => 'prt_hero_section', 'type' => 'text']);
    $wp->add_setting('prt_hero_title', ['default' => __('Clean, fast software for the web and Microsoft 365.', 'pressroot'), 'sanitize_callback' => 'sanitize_text_field']);
    $wp->add_control('prt_hero_title', ['label' => __('Hero title (H1)', 'pressroot'), 'section' => 'prt_hero_section', 'type' => 'textarea']);
    $wp->add_setting('prt_hero_subtext', ['default' => __("I'm Matt Hummel, a full-stack developer. I write about web development, WordPress, and the Power Platform, and share the tools I build on GitHub.", 'pressroot'), 'sanitize_callback' => 'sanitize_textarea_field']);
    $wp->add_control('prt_hero_subtext', ['label' => __('Hero subtext (paragraph)', 'pressroot'), 'section' => 'prt_hero_section', 'type' => 'textarea']);

    $sel('prt_hero_cols', __('Columns', 'pressroot'), ['1' => '1', '2' => '2', '3' => '3'], '1', 'prt_hero_section');
    $sel('prt_hero_align_h', __('Content horizontal position', 'pressroot'), ['left' => __('Left', 'pressroot'), 'center' => __('Center', 'pressroot'), 'right' => __('Right', 'pressroot')], 'center', 'prt_hero_section');
    $sel('prt_hero_align_v', __('Content vertical position', 'pressroot'), ['top' => __('Top', 'pressroot'), 'center' => __('Center', 'pressroot'), 'bottom' => __('Bottom', 'pressroot')], 'center', 'prt_hero_section');
    $sel('prt_hero_content_maxw', __('Content max width', 'pressroot'), ['0' => __('Default', 'pressroot'), '420' => '420px', '480' => '480px', '560' => '560px', '640' => '640px', '760' => '760px', 'full' => __('Full', 'pressroot')], '0', 'prt_hero_section');
    $sel('prt_hero_content_gap', __('Content spacing', 'pressroot'), ['0' => __('Default', 'pressroot'), '8' => __('Tight', 'pressroot'), '16' => __('Normal', 'pressroot'), '26' => __('Roomy', 'pressroot'), '40' => __('Extra', 'pressroot')], '0', 'prt_hero_section');
    $cw = ['0' => __('Default', 'pressroot'), '320' => '320px', '380' => '380px', '420' => '420px', '480' => '480px', '560' => '560px', '640' => '640px', 'full' => __('Full', 'pressroot')];
    $sel('prt_hero_content_maxw_tablet', __('Content max width (tablet)', 'pressroot'), $cw, '0', 'prt_hero_section');
    $sel('prt_hero_content_maxw_mobile', __('Content max width (mobile)', 'pressroot'), $cw, '0', 'prt_hero_section');

    // Advanced flexbox controls for the hero layout container.
    $sel('prt_hero_flex_dir', __('Flexbox: direction', 'pressroot'), ['0' => __('Default', 'pressroot'), 'row' => __('Row', 'pressroot'), 'row-reverse' => __('Row reverse', 'pressroot'), 'column' => __('Column', 'pressroot'), 'column-reverse' => __('Column reverse', 'pressroot')], '0', 'prt_hero_section');
    $sel('prt_hero_flex_justify', __('Flexbox: justify (main axis)', 'pressroot'), ['0' => __('Default', 'pressroot'), 'flex-start' => __('Start', 'pressroot'), 'center' => __('Center', 'pressroot'), 'flex-end' => __('End', 'pressroot'), 'space-between' => __('Space between', 'pressroot'), 'space-around' => __('Space around', 'pressroot'), 'space-evenly' => __('Space evenly', 'pressroot')], '0', 'prt_hero_section');
    $sel('prt_hero_flex_align', __('Flexbox: align (cross axis)', 'pressroot'), ['0' => __('Default', 'pressroot'), 'flex-start' => __('Start', 'pressroot'), 'center' => __('Center', 'pressroot'), 'flex-end' => __('End', 'pressroot'), 'stretch' => __('Stretch', 'pressroot'), 'baseline' => __('Baseline', 'pressroot')], '0', 'prt_hero_section');
    $sel('prt_hero_flex_wrap', __('Flexbox: wrap', 'pressroot'), ['0' => __('Default', 'pressroot'), 'wrap' => __('Wrap', 'pressroot'), 'nowrap' => __('No wrap', 'pressroot'), 'wrap-reverse' => __('Wrap reverse', 'pressroot')], '0', 'prt_hero_section');
    $sel('prt_hero_flex_gap', __('Flexbox: gap', 'pressroot'), ['0' => __('Default', 'pressroot'), '16' => '16px', '24' => '24px', '32' => '32px', '48' => '48px', '64' => '64px'], '0', 'prt_hero_section');
    $sel('prt_hero_img_side', __('Image side (2 columns)', 'pressroot'), ['right' => __('Right', 'pressroot'), 'left' => __('Left', 'pressroot')], 'right', 'prt_hero_section');

    $wp->add_setting('prt_hero_img', ['default' => '', 'sanitize_callback' => 'esc_url_raw']);
    $wp->add_control(new \WP_Customize_Image_Control($wp, 'prt_hero_img', ['label' => __('Side image / illustration', 'pressroot'), 'section' => 'prt_hero_section']));
    $wp->add_setting('prt_hero_img2', ['default' => '', 'sanitize_callback' => 'esc_url_raw']);
    $wp->add_control(new \WP_Customize_Image_Control($wp, 'prt_hero_img2', ['label' => __('Second image (3 columns)', 'pressroot'), 'section' => 'prt_hero_section']));

    $wp->add_setting('prt_hero_bg', ['default' => '', 'sanitize_callback' => 'esc_url_raw']);
    $wp->add_control(new \WP_Customize_Image_Control($wp, 'prt_hero_bg', ['label' => __('Background cover image', 'pressroot'), 'section' => 'prt_hero_section']));
    $wp->add_setting('prt_hero_overlay', ['default' => 45, 'sanitize_callback' => 'absint']);
    $wp->add_control('prt_hero_overlay', ['label' => __('Background overlay (%)', 'pressroot'), 'section' => 'prt_hero_section', 'type' => 'number', 'input_attrs' => ['min' => 0, 'max' => 90, 'step' => 5]]);
    $sel('prt_hero_minh', __('Hero min height', 'pressroot'), ['0' => __('Default', 'pressroot'), '420' => '420px', '520' => '520px', '640' => '640px', '100vh' => __('Full screen', 'pressroot')], '0', 'prt_hero_section');

    $sel('prt_hero_anim', __('Hero entrance animation', 'pressroot'), prt_anim_effects(), 'zoom-in', 'prt_hero_section');

    /* ---- Animations (scroll reveal) ---- */
    $wp->add_section('prt_anim_section', [
        'title'       => __('Animations', 'pressroot'),
        'panel'       => 'prt_theme_options',
        'description' => __('On-scroll reveal animations applied to sections site-wide.', 'pressroot'),
    ]);
    $wp->add_setting('prt_scroll_enable', ['default' => true, 'sanitize_callback' => 'wp_validate_boolean']);
    $wp->add_control('prt_scroll_enable', ['label' => __('Enable on-scroll animations (site-wide)', 'pressroot'), 'section' => 'prt_anim_section', 'type' => 'checkbox']);
    $sel('prt_scroll_effect', __('Scroll animation effect', 'pressroot'), prt_anim_effects(), 'zoom-in', 'prt_anim_section');
    $sel('prt_scroll_speed', __('Animation speed', 'pressroot'), ['fast' => __('Fast', 'pressroot'), 'normal' => __('Normal', 'pressroot'), 'slow' => __('Slow', 'pressroot')], 'normal', 'prt_anim_section');
}, 24);

/** No-flash: mark <html> anim-ready early so animated elements only hide when JS will reveal them. */
add_action('wp_head', function () {
    $heroAnim = get_theme_mod('prt_hero_anim', 'zoom-in');
    if (! get_theme_mod('prt_scroll_enable', true) && $heroAnim === 'none') {
        return;
    }
    echo "<script>document.documentElement.classList.add('prt-anim');</script>\n";
}, 3);

/** Hero layout CSS (dynamic from mods). */
add_action('prt_head_end', function () {
    $cols = max(1, min(3, (int) get_theme_mod('prt_hero_cols', 1)));
    $ah   = get_theme_mod('prt_hero_align_h', 'center');
    $av   = get_theme_mod('prt_hero_align_v', 'center');
    $bg   = esc_url(get_theme_mod('prt_hero_bg', ''));
    $ov   = max(0, min(90, absint(get_theme_mod('prt_hero_overlay', 45))));
    $minh = (string) get_theme_mod('prt_hero_minh', '0');

    $css  = '.prt-hero{position:relative;}';
    $css .= '.prt-hero .prt-hero-inner{position:relative;z-index:2;}';

    if ($cols >= 2) {
        $css .= '.prt-hero .prt-hero-inner{display:flex;gap:48px;flex-wrap:wrap;align-items:center;text-align:left;}';
        $css .= '.prt-hero .prt-hero-content{flex:1 1 360px;min-width:0;}';
        $css .= '.prt-hero .prt-hero-media{flex:1 1 300px;}';
        $css .= '.prt-hero .prt-hero-media img{width:100%;height:auto;display:block;border-radius:18px;box-shadow:0 24px 60px rgba(16,18,24,.16);}';
        if (get_theme_mod('prt_hero_img_side', 'right') === 'left') {
            $css .= '.prt-hero .prt-hero-inner{flex-direction:row-reverse;}';
        }
    }

    // Content is a flex column so the alignment moves EVERY item (eyebrow, title,
    // lead, buttons) â€” not just the buttons. We also neutralise the children's own
    // auto-margins / text-align (e.g. .lead has margin:0 auto + text-align:center).
    $ai = $ah === 'left' ? 'flex-start' : ($ah === 'right' ? 'flex-end' : 'center');
    $ta = $ah === 'left' ? 'left' : ($ah === 'right' ? 'right' : 'center');
    $css .= '.prt-hero .prt-hero-content{display:flex;flex-direction:column;align-items:' . $ai . ';}';
    $css .= '.prt-hero .prt-hero-content > *{margin-left:0;margin-right:0;text-align:' . $ta . ';max-width:100%;}';
    $css .= '.prt-hero .btn-row{display:flex;flex-wrap:wrap;gap:12px;justify-content:' . $ai . ';}';

    // Content max-width + (for single-column heroes) block position.
    $maxw = (string) get_theme_mod('prt_hero_content_maxw', '0');
    if ($maxw !== '0' && $maxw !== 'full') {
        $css .= '.prt-hero .prt-hero-content{max-width:' . absint($maxw) . 'px;}';
        if ($cols < 2) {
            $bm = $ah === 'left' ? '0 auto 0 0' : ($ah === 'right' ? '0 0 0 auto' : '0 auto');
            $css .= '.prt-hero .prt-hero-content{margin:' . $bm . ';}';
        }
    }

    // Content spacing (gap between copy items).
    $gap = absint(get_theme_mod('prt_hero_content_gap', 0));
    if ($gap > 0) {
        $css .= '.prt-hero .prt-hero-content{gap:' . $gap . 'px;}';
        $css .= '.prt-hero .prt-hero-content > *{margin-top:0;margin-bottom:0;}';
    }

    // Always keep comfortable side padding so content never touches the device edge.
    $css .= '.prt-hero{padding-left:clamp(20px,5vw,28px);padding-right:clamp(20px,5vw,28px);box-sizing:border-box;}';

    // Per-breakpoint content max-width (tablet 641â€“1024, mobile â‰¤640). On a single
    // column we also re-apply the block position so it stays put when narrowed.
    $bwv = function ($v) {
        if ($v === 'full') {
            return 'none';
        }
        return ($v !== '' && $v !== '0') ? absint($v) . 'px' : '';
    };
    $bm  = $ah === 'left' ? '0 auto 0 0' : ($ah === 'right' ? '0 0 0 auto' : '0 auto');
    $pos = $cols < 2 ? 'margin:' . $bm . ';' : '';
    $mt  = $bwv((string) get_theme_mod('prt_hero_content_maxw_tablet', '0'));
    if ($mt !== '') {
        $css .= '@media(min-width:641px) and (max-width:1024px){.prt-hero .prt-hero-content{max-width:' . $mt . ';' . $pos . '}}';
    }
    $mm = $bwv((string) get_theme_mod('prt_hero_content_maxw_mobile', '0'));
    if ($mm !== '') {
        $css .= '@media(max-width:640px){.prt-hero .prt-hero-content{max-width:' . $mm . ';' . $pos . '}}';
    }

    // Multi-column: tighten the column gap as it narrows and stack to full width on mobile.
    if ($cols >= 2) {
        $css .= '@media(max-width:880px){.prt-hero .prt-hero-inner{gap:26px;}}';
        $css .= '@media(max-width:640px){.prt-hero .prt-hero-inner{gap:20px;}.prt-hero .prt-hero-content,.prt-hero .prt-hero-media{flex:1 1 100%;}}';
    }

    // Advanced flexbox overrides on the hero layout container (apply when chosen).
    $clean = function ($v) {
        return preg_replace('/[^a-z-]/', '', (string) $v);
    };
    $flex = '';
    $fd = (string) get_theme_mod('prt_hero_flex_dir', '0');
    $fj = (string) get_theme_mod('prt_hero_flex_justify', '0');
    $fa = (string) get_theme_mod('prt_hero_flex_align', '0');
    $fw = (string) get_theme_mod('prt_hero_flex_wrap', '0');
    $fg = absint(get_theme_mod('prt_hero_flex_gap', 0));
    if ($fd !== '0') {
        $flex .= 'flex-direction:' . $clean($fd) . ';';
    }
    if ($fj !== '0') {
        $flex .= 'justify-content:' . $clean($fj) . ';';
    }
    if ($fa !== '0') {
        $flex .= 'align-items:' . $clean($fa) . ';';
    }
    if ($fw !== '0') {
        $flex .= 'flex-wrap:' . $clean($fw) . ';';
    }
    if ($fg > 0) {
        $flex .= 'gap:' . $fg . 'px;';
    }
    if ($flex !== '') {
        $css .= '.prt-hero .prt-hero-inner{display:flex;' . $flex . '}';
    }

    if ($minh && $minh !== '0') {
        $h     = $minh === '100vh' ? '100vh' : (absint($minh) . 'px');
        $items = $av === 'top' ? 'flex-start' : ($av === 'bottom' ? 'flex-end' : 'center');
        $css .= '.prt-hero{min-height:' . $h . ';display:flex;align-items:' . $items . ';}';
        $css .= '.prt-hero > .prt-hero-inner{width:100%;}';
    }

    if ($bg) {
        $css .= '.prt-hero{background-image:url(\'' . $bg . '\');background-size:cover;background-position:center;border-radius:20px;overflow:hidden;}';
        $css .= '.prt-hero .prt-hero-overlay{position:absolute;inset:0;z-index:1;background:rgba(8,10,14,' . ($ov / 100) . ');}';
        $css .= '.prt-hero,.prt-hero .display-title,.prt-hero .lead{color:#fff;}';
        $css .= '.prt-hero .eyebrow{color:rgba(255,255,255,.86);}';
        $css .= '.prt-hero .btn-outline{background:rgba(255,255,255,.12);color:#fff;border-color:rgba(255,255,255,.5);}';
    }

    echo "\n<style id=\"prt-hero\">" . $css . "</style>\n";
}, 16);

/** Animation CSS (initial/hidden states under html.prt-anim). */
add_action('prt_head_end', function () {
    $heroAnim = get_theme_mod('prt_hero_anim', 'zoom-in');
    if (! get_theme_mod('prt_scroll_enable', true) && $heroAnim === 'none') {
        return;
    }
    $speed = get_theme_mod('prt_scroll_speed', 'normal');
    $dur   = $speed === 'fast' ? '.42s' : ($speed === 'slow' ? '.95s' : '.66s');

    $css  = 'html.prt-anim [data-anim]{opacity:0;transition:opacity ' . $dur . ' ease,transform ' . $dur . ' cubic-bezier(.2,.75,.25,1),filter ' . $dur . ' ease;will-change:transform,opacity;}';
    $css .= 'html.prt-anim [data-anim].is-in{opacity:1;transform:none;filter:none;}';
    $css .= 'html.prt-anim [data-anim="fade-up"]{transform:translateY(34px);}';
    $css .= 'html.prt-anim [data-anim="zoom-in"]{transform:scale(.88);}';
    $css .= 'html.prt-anim [data-anim="pop"]{transform:scale(.55);transition-timing-function:cubic-bezier(.34,1.56,.64,1);}';
    $css .= 'html.prt-anim [data-anim="blur-in"]{filter:blur(14px);transform:scale(1.03);}';
    $css .= 'html.prt-anim [data-anim="slide-left"]{transform:translateX(-48px);}';
    $css .= 'html.prt-anim [data-anim="slide-right"]{transform:translateX(48px);}';
    $css .= '@media (prefers-reduced-motion: reduce){html.prt-anim [data-anim]{opacity:1!important;transform:none!important;filter:none!important;transition:none!important;}}';

    echo "\n<style id=\"prt-anim\">" . $css . "</style>\n";
}, 17);

/** Scroll-reveal engine. */
add_action('wp_footer', function () {
    $heroAnim = get_theme_mod('prt_hero_anim', 'zoom-in');
    $enable   = (bool) get_theme_mod('prt_scroll_enable', true);
    $effect   = get_theme_mod('prt_scroll_effect', 'zoom-in');
    if (! $enable && $heroAnim === 'none') {
        return;
    }

    // Sections that receive scroll reveal site-wide.
    $selectors = '.home-section,.section-head,.card-grid,.project-grid,.project-card,.mini-card,.cta-card,.prt-cta-band,.prt-stat-strip,.post-single-title,.post-prose > h2,.post-prose > h3,.service-card,.archive-header,.project-hero,.readme-prose,.contact-form';

    $eff = esc_js($effect);
    $sel = esc_js($selectors);
    $on  = $enable ? '1' : '0';

    $js  = '(function(){';
    $js .= 'var R=window.matchMedia("(prefers-reduced-motion: reduce)").matches;';
    // Hero entrance: reveal on first paint.
    $js .= 'var hero=document.querySelectorAll(".prt-hero [data-anim],.prt-hero[data-anim]");';
    $js .= 'requestAnimationFrame(function(){requestAnimationFrame(function(){hero.forEach(function(el){el.classList.add("is-in");});});});';
    $js .= 'function revealAll(){document.querySelectorAll("[data-anim]").forEach(function(el){el.classList.add("is-in");});}';
    $js .= 'if(R){revealAll();return;}';
    $js .= 'if(' . $on . '){';
    $js .= 'var eff="' . $eff . '";';
    $js .= 'if(eff!=="none"){';
    $js .= 'var nodes=[].slice.call(document.querySelectorAll("' . $sel . '"));';
    $js .= 'nodes=nodes.filter(function(el){return !el.closest(".prt-hero");});';
    $js .= 'nodes.forEach(function(el){if(!el.hasAttribute("data-anim")){el.setAttribute("data-anim",eff);}});';
    $js .= 'if("IntersectionObserver" in window){';
    $js .= 'var io=new IntersectionObserver(function(en){en.forEach(function(e){if(e.isIntersecting){e.target.classList.add("is-in");io.unobserve(e.target);}});},{threshold:0.12,rootMargin:"0px 0px -8% 0px"});';
    $js .= 'nodes.forEach(function(el){io.observe(el);});';
    $js .= '}else{nodes.forEach(function(el){el.classList.add("is-in");});}';
    $js .= '}}';
    // Safety: reveal everything after 3s in case the observer never fires.
    $js .= 'setTimeout(revealAll,3000);';
    $js .= '})();';

    echo "\n<script id=\"prt-anim-js\">" . $js . "</script>\n";
}, 50);
