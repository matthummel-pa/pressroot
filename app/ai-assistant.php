<?php

/**
 * AI Setup Assistant — Appearance -> AI Setup Assistant.
 *
 * The "reduce heavy lifting for a new theme owner" feature: two independent
 * tools on one admin page.
 *
 * 1. SITE TYPE PROFILES — a small step up from the plain Style Kit picker in
 *    settings-io.php. Instead of just choosing colors/fonts, the owner picks
 *    the kind of site they're building (Agency, Freelance/Portfolio, SaaS,
 *    Blog, Marketing landing) and this applies the matching Style Kit AND
 *    creates the handful of starter pages that type of site actually needs —
 *    each pre-filled with one of the "— Full page" patterns already built in
 *    page-patterns.php, so the owner lands on a real, editable draft instead
 *    of a blank page. Safe to click more than once: existing pages are never
 *    touched or duplicated (matched by slug), only missing ones are created.
 *    This directly answers "clone this into other projects based on type of
 *    WordPress website someone would need" — the site-type list below is
 *    exactly that set of starting points, and is filterable so a fork of this
 *    theme can add its own without touching this file (apply_filters below).
 *
 * 2. STARTER COPY GENERATOR — a one-line business description in, a hero
 *    headline + subheadline out, using the same free/no-API-key Pollinations
 *    text endpoint the Hero Image Finder (app/hero-image.php) already uses
 *    for images. No server proxy, no keys to configure, nothing to bill —
 *    consistent with this theme's "free AI, zero setup" philosophy. This is
 *    real generated copy, not a canned template: the owner types their own
 *    one-liner and gets a fresh headline/subhead pair back every time.
 *
 * Neither tool touches published content without an explicit click, and
 * nothing here calls a paid API or requires an account.
 */

namespace App;

/**
 * Site type -> {style kit slug, starter pages}. Each starter page maps a
 * page slug + title to one of the full-page block patterns already
 * registered in page-patterns.php / home-patterns.php (see their $keep /
 * registered-pattern lists for the canonical set of "-full" slugs).
 *
 * Filterable (`matthummel/site_types`) so a fork of this theme aimed at a
 * different niche can add, remove, or re-map profiles without editing this
 * file directly — the same extensibility convention prt_style_kits() uses.
 */
function prt_site_types()
{
    return apply_filters('matthummel/site_types', [
        'agency' => [
            'label' => __('Agency / Studio', 'pressroot'),
            'desc'  => __('Multi-service shop selling to clients: Services, Pricing, Contact.', 'pressroot'),
            'kit'   => 'paper_space',
            'pages' => [
                ['slug' => 'services', 'title' => __('Services', 'pressroot'), 'pattern' => 'matthummel/services-full'],
                ['slug' => 'pricing',  'title' => __('Pricing', 'pressroot'),  'pattern' => 'matthummel/pricing-full'],
                ['slug' => 'contact',  'title' => __('Contact', 'pressroot'),  'pattern' => 'matthummel/contact-full'],
            ],
        ],
        'freelance' => [
            'label' => __('Freelance / Portfolio', 'pressroot'),
            'desc'  => __('One person selling their own work: About, Résumé, Projects.', 'pressroot'),
            'kit'   => 'editorial',
            'pages' => [
                ['slug' => 'about',    'title' => __('About', 'pressroot'),    'pattern' => 'matthummel/about-full'],
                ['slug' => 'resume',   'title' => __('Résumé', 'pressroot'),   'pattern' => 'matthummel/resume-full'],
                ['slug' => 'projects', 'title' => __('Projects', 'pressroot'), 'pattern' => 'matthummel/projects-full'],
            ],
        ],
        'saas' => [
            'label' => __('SaaS / Startup', 'pressroot'),
            'desc'  => __('Product-led site: Features, Pricing, Contact — dark, modern default.', 'pressroot'),
            'kit'   => 'midnight',
            'pages' => [
                ['slug' => 'features', 'title' => __('Features', 'pressroot'), 'pattern' => 'matthummel/services-full'],
                ['slug' => 'pricing',  'title' => __('Pricing', 'pressroot'),  'pattern' => 'matthummel/pricing-full'],
                ['slug' => 'contact',  'title' => __('Contact', 'pressroot'),  'pattern' => 'matthummel/contact-full'],
            ],
        ],
        'blog' => [
            'label' => __('Blog / Content site', 'pressroot'),
            'desc'  => __('Writing-first site: Blog index + About, warm neutral palette.', 'pressroot'),
            'kit'   => 'warm_sand',
            'pages' => [
                ['slug' => 'blog',  'title' => __('Blog', 'pressroot'),  'pattern' => 'matthummel/blog-full'],
                ['slug' => 'about', 'title' => __('About', 'pressroot'), 'pattern' => 'matthummel/about-full'],
            ],
        ],
        'marketing' => [
            'label' => __('Marketing / Landing page', 'pressroot'),
            'desc'  => __('Single-focus landing site: Home + Contact, sharp minimal palette.', 'pressroot'),
            'kit'   => 'mono_slate',
            'pages' => [
                ['slug' => 'home',    'title' => __('Home', 'pressroot'),    'pattern' => 'matthummel/home-full'],
                ['slug' => 'contact', 'title' => __('Contact', 'pressroot'), 'pattern' => 'matthummel/contact-full'],
            ],
        ],
    ]);
}

/** Admin page registration, alongside (but separate from) Theme Tools. */
add_action('admin_menu', function () {
    add_theme_page(
        __('AI Setup Assistant', 'pressroot'),
        __('AI Setup Assistant', 'pressroot'),
        'edit_theme_options',
        'prt-ai-assistant',
        __NAMESPACE__ . '\\prt_ai_assistant_render'
    );
});

/** Load the copy-generator's JS only on this one admin screen. */
add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'appearance_page_prt-ai-assistant') {
        return;
    }
    $path = 'resources/js/ai-assistant.js';
    wp_enqueue_script(
        'prt-ai-assistant',
        get_theme_file_uri($path),
        [],
        file_exists(get_theme_file_path($path)) ? filemtime(get_theme_file_path($path)) : '1',
        true
    );
});

function prt_ai_assistant_render()
{
    if (! current_user_can('edit_theme_options')) {
        return;
    }
    $post   = admin_url('admin-post.php');
    $result = isset($_GET['prt_site_type_result']) ? sanitize_key($_GET['prt_site_type_result']) : '';
    $created = isset($_GET['prt_created']) ? sanitize_text_field(wp_unslash($_GET['prt_created'])) : '';
    $skipped = isset($_GET['prt_skipped']) ? sanitize_text_field(wp_unslash($_GET['prt_skipped'])) : '';
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('AI Setup Assistant', 'pressroot'); ?></h1>
        <p class="description"><?php esc_html_e('Pick the kind of site you\'re building to apply a matching design and create starter pages, then generate hero copy to fill them in.', 'pressroot'); ?></p>

        <?php if ($result !== '') : ?>
            <div class="notice notice-success is-dismissible">
                <p>
                    <?php
                    if ($created !== '') {
                        printf(
                            /* translators: %s: comma-separated list of created page titles */
                            esc_html__('Design applied. Created: %s.', 'pressroot'),
                            esc_html($created)
                        );
                    } else {
                        esc_html_e('Design applied. All starter pages already existed, so nothing new was created.', 'pressroot');
                    }
                    if ($skipped !== '') {
                        echo ' ' . esc_html(sprintf(
                            /* translators: %s: comma-separated list of skipped page titles */
                            __('Already existed (left untouched): %s.', 'pressroot'),
                            $skipped
                        ));
                    }
                    ?>
                </p>
            </div>
        <?php endif; ?>

        <h2 style="margin-top:24px"><?php esc_html_e('1. Choose a site type', 'pressroot'); ?></h2>
        <div style="display:flex;flex-wrap:wrap;gap:14px;margin:16px 0 32px">
            <?php foreach (prt_site_types() as $id => $type) : ?>
                <form method="post" action="<?php echo esc_url($post); ?>" style="width:260px;border:1px solid #dcdcde;border-radius:10px;padding:16px;background:#fff">
                    <strong style="font-size:14px"><?php echo esc_html($type['label']); ?></strong>
                    <p style="margin:6px 0 10px;color:#646970;font-size:12px"><?php echo esc_html($type['desc']); ?></p>
                    <p style="margin:0 0 12px;font-size:12px;color:#646970">
                        <?php
                        printf(
                            /* translators: %s: comma-separated list of starter page titles */
                            esc_html__('Creates: %s', 'pressroot'),
                            esc_html(implode(', ', wp_list_pluck($type['pages'], 'title')))
                        );
                        ?>
                    </p>
                    <input type="hidden" name="action" value="prt_apply_site_type">
                    <input type="hidden" name="site_type" value="<?php echo esc_attr($id); ?>">
                    <?php wp_nonce_field('prt_apply_site_type'); ?>
                    <button class="button button-primary" style="width:100%"><?php esc_html_e('Use this', 'pressroot'); ?></button>
                </form>
            <?php endforeach; ?>
        </div>

        <hr>

        <h2><?php esc_html_e('2. Generate starter hero copy', 'pressroot'); ?></h2>
        <p class="description"><?php esc_html_e('Describe your business or site in a sentence and get a draft headline + subheadline you can paste into your Hero pattern. Uses a free AI text service — no account or API key needed.', 'pressroot'); ?></p>
        <div id="prt-ai-copy" style="max-width:640px;margin-top:14px">
            <label for="prt-ai-copy-input" class="screen-reader-text"><?php esc_html_e('Describe your business', 'pressroot'); ?></label>
            <textarea id="prt-ai-copy-input" rows="2" style="width:100%" placeholder="<?php echo esc_attr__('e.g. a two-person branding studio for indie game developers', 'pressroot'); ?>"></textarea>
            <p>
                <button type="button" id="prt-ai-copy-go" class="button button-primary"><?php esc_html_e('Generate headline & subhead', 'pressroot'); ?></button>
            </p>
            <div id="prt-ai-copy-note" style="color:#646970;font-size:13px"></div>
            <div id="prt-ai-copy-result" style="display:none;margin-top:10px;border:1px solid #dcdcde;border-radius:8px;padding:16px;background:#fff">
                <p style="margin:0 0 6px"><strong id="prt-ai-copy-headline" style="font-size:18px;display:block"></strong></p>
                <p id="prt-ai-copy-subhead" style="margin:0 0 12px;color:#3c434a"></p>
                <button type="button" class="button" data-copy="headline"><?php esc_html_e('Copy headline', 'pressroot'); ?></button>
                <button type="button" class="button" data-copy="subhead"><?php esc_html_e('Copy subhead', 'pressroot'); ?></button>
                <button type="button" class="button" id="prt-ai-copy-regen"><?php esc_html_e('Regenerate', 'pressroot'); ?></button>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Apply a site type: set the matching Style Kit (reusing prt_apply_style_kit()
 * from settings-io.php, so this stays the one place that knows how to apply a
 * kit), then create any of its starter pages that don't already exist yet
 * (matched by post_name/slug — existing pages, published or draft, are never
 * modified or duplicated). New pages are created as drafts pre-filled with
 * the matching full-page block pattern's content, pulled straight from
 * WP_Block_Patterns_Registry so this always matches what a user would see if
 * they inserted that pattern by hand from the editor.
 */
add_action('admin_post_prt_apply_site_type', function () {
    prt_require_admin_post('prt_apply_site_type');

    $id    = isset($_POST['site_type']) ? sanitize_key($_POST['site_type']) : '';
    $types = prt_site_types();

    if (! isset($types[$id])) {
        wp_safe_redirect(admin_url('themes.php?page=prt-ai-assistant'));
        exit;
    }

    $type = $types[$id];
    prt_apply_style_kit($type['kit']);

    $created = [];
    $skipped = [];
    $registry = class_exists('WP_Block_Patterns_Registry') ? \WP_Block_Patterns_Registry::get_instance() : null;

    foreach ($type['pages'] as $page) {
        $existing = get_posts([
            'post_type'      => 'page',
            'name'           => $page['slug'],
            'post_status'    => ['publish', 'draft', 'pending', 'private', 'future'],
            'numberposts'    => 1,
            'fields'         => 'ids',
        ]);

        if (! empty($existing)) {
            $skipped[] = $page['title'];
            continue;
        }

        $pattern = $registry ? $registry->get_registered($page['pattern']) : null;
        $content = $pattern['content'] ?? '';

        wp_insert_post([
            'post_type'    => 'page',
            'post_status'  => 'draft', // Left as a draft so the owner reviews/publishes deliberately.
            'post_title'   => $page['title'],
            'post_name'    => $page['slug'],
            'post_content' => $content,
        ]);
        $created[] = $page['title'];
    }

    wp_safe_redirect(add_query_arg([
        'page'                  => 'prt-ai-assistant',
        'prt_site_type_result'  => $id,
        'prt_created'           => rawurlencode(implode(', ', $created)),
        'prt_skipped'           => rawurlencode(implode(', ', $skipped)),
    ], admin_url('themes.php')));
    exit;
});
