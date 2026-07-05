<?php

/**
 * Pressroot — the one consolidated settings page for the theme.
 *
 * Appearance -> Pressroot. Used to be four separate admin pages (Theme
 * Tools, Starter Sites, Pressroot AI, GitHub), each with its own <h1> and
 * its own spot in the Appearance submenu. Consolidated into one branded
 * page with a section per area, so the theme has a single, obvious home
 * instead of scattering settings across the WordPress admin menu:
 *
 *   - Site Types — app/ai-assistant.php     (prt_pressroot_ai_tab_html())
 *   - GitHub     — app/github-settings.php  (prt_github_tab_html())
 *   - Support    — app/support-settings.php (prt_support_tab_html())
 *
 * Navigation is a left sidebar with the active section's content on the
 * right (prt_settings_render() below) rather than the top nav-tab-wrapper
 * this page used before — same sections, different chrome, so every
 * `prt_settings_tab_url($tab, $extra)` call across these files still works
 * unchanged; only the page-level layout markup changed.
 *
 * REMOVED as part of this consolidation: the old "Starter Sites" demo
 * importer (app/demo-import.php) is gone. It offered 2 personas (portfolio,
 * agency) built from a fixed set of pattern slugs. CORRECTION: an earlier
 * note here claimed most of those pattern slugs no longer existed — that
 * was checked again and was wrong; all 11 are still registered (mostly in
 * app/sections-library.php, a few in app/patterns-extra.php), so the demo
 * importer would still have worked. The actual reason it was removed
 * stands on its own: it was a strictly weaker duplicate of Pressroot AI's
 * site-type picker (5 personas, 2 hand-built variants each, live design
 * previews, regenerate, dedicated per-type patterns) that solves the exact
 * same "give me a starter site" job better — 2 fixed personas vs. 5, no
 * regenerate, no previews. A placeholder "Starter" tab briefly explained
 * the retirement and linked to Pressroot AI; that tab has since been
 * removed too — Pressroot AI is reachable directly from its own tab, so
 * the explainer had nothing left to do. The dashboard widget's old "Create
 * starter pages + menu" button (blank pages, no design) was removed the
 * same way, in app/whitelabel.php — that reasoning (blank vs. designed
 * pages) was and still is accurate.
 *
 * ALSO REMOVED: the standalone "Style Kits" tab (app/settings-io.php). Every
 * Site Type already applies its own matching Style Kit automatically when
 * chosen, so a separate manual "pick a kit yourself" grid was a second way to
 * do the same thing. The former "Pressroot AI" tab was renamed **Site
 * Types** to reflect that it's now the primary "set up your site" tab — it
 * still surfaces AI (starter hero copy + the Advanced "Connect more AI
 * models" section), per its own docblock in app/ai-assistant.php. Style
 * Kits' Export/Import/Reset controls weren't dropped, just relocated: they
 * now render inside a collapsed "Advanced" section on the Site Types tab
 * (`prt_settings_backup_fields_html()` in app/settings-io.php), same pattern
 * as AI Connectors.
 *
 * Each tab's render function was originally a full `prt_..._render()` with
 * its own `<div class="wrap"><h1>`; those were extracted down to
 * `prt_..._tab_html()` functions with no page chrome, callable from here.
 * Every admin-post/AJAX handler those files already had is unchanged — only
 * where their forms redirect back to changed, via prt_settings_tab_url()
 * below (the one place that knows this page's slug + tab query var).
 */

namespace App;

/** This page's slug, in one place, so nothing else needs to hardcode it. */
const PRT_SETTINGS_SLUG = 'prt-settings';

/**
 * Build a URL back to one tab of this page, optionally with extra query args
 * (e.g. a result/notice flag). Every admin-post handler across the four
 * consolidated files uses this instead of building `themes.php?page=...` by
 * hand, so there is exactly one place that knows this page's slug.
 */
function prt_settings_tab_url(string $tab, array $extra = []): string
{
    return add_query_arg(array_merge([
        'page' => PRT_SETTINGS_SLUG,
        'tab'  => $tab,
    ], $extra), admin_url('themes.php'));
}

/** The tabs, in display order. Each maps to a render callback (no args, echoes HTML). */
function prt_settings_tabs(): array
{
    return [
        'ai' => [
            'label'    => __('Site Types', 'pressroot'),
            'render'   => __NAMESPACE__ . '\\prt_pressroot_ai_tab_html',
            'visible'  => function_exists('App\\prt_addon_enabled') ? prt_addon_enabled('pressroot_ai') : true,
        ],
        'github' => [
            'label'    => __('GitHub', 'pressroot'),
            'render'   => __NAMESPACE__ . '\\prt_github_tab_html',
            'visible'  => current_user_can('manage_options'),
        ],
        'support' => [
            'label'    => __('Support', 'pressroot'),
            'render'   => __NAMESPACE__ . '\\prt_support_tab_html',
            // Always visible — not gated by the Pressroot AI addon toggle,
            // since getting help/docs shouldn't depend on an unrelated
            // feature being on. Still capability-gated inside its own
            // render function (edit_theme_options), same as every tab.
            'visible'  => true,
        ],
    ];
}

/** Single admin page registration, replacing the four it consolidates. */
add_action('admin_menu', function () {
    add_theme_page(
        __('Pressroot', 'pressroot'),
        __('Pressroot', 'pressroot'),
        'edit_theme_options',
        PRT_SETTINGS_SLUG,
        __NAMESPACE__ . '\\prt_settings_render'
    );
});

/**
 * Branded page header shown above every tab: a small Pressroot mark (no
 * image asset needed — a styled initial in the theme's own default brand
 * color, #7C5CFF from the "Paper + Space" Style Kit, see prt_style_kits() in
 * app/settings-io.php) plus name/tagline, and an editable Docs/Support links
 * row (prt_docs_url / prt_support_url theme_mods) so the owner can point
 * both at wherever their real documentation and support channel live —
 * intentionally not hardcoded to a guess for a fork — but the theme's OWN
 * defaults point at its real GitHub repo (the same one docs/index.md and
 * docs/ARCHITECTURE.md already reference for "Report an issue"), since that
 * much is genuinely known and correct out of the box.
 */
function prt_settings_header(): void
{
    $docsUrl    = get_theme_mod('prt_docs_url', 'https://github.com/matthummel-pa/pressroot#readme');
    $supportUrl = get_theme_mod('prt_support_url', 'https://github.com/matthummel-pa/pressroot/issues');
    ?>
    <div style="display:flex;align-items:center;gap:14px;margin:18px 0 6px">
        <span style="display:flex;align-items:center;justify-content:center;width:40px;height:40px;border-radius:10px;background:#7C5CFF;color:#fff;font-family:Georgia,serif;font-weight:700;font-size:20px;flex-shrink:0">P</span>
        <div>
            <h1 style="margin:0;font-size:22px"><?php esc_html_e('Pressroot', 'pressroot'); ?></h1>
            <p style="margin:2px 0 0;color:#646970;font-size:13px"><?php esc_html_e('Site types, AI, and integrations — all in one place.', 'pressroot'); ?></p>
        </div>
    </div>

    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin:10px 0 20px;font-size:13px">
        <?php if ($docsUrl !== '') : ?>
            <a href="<?php echo esc_url($docsUrl); ?>" target="_blank" rel="noopener noreferrer">📘 <?php esc_html_e('Documentation', 'pressroot'); ?></a>
        <?php endif; ?>
        <?php if ($supportUrl !== '') : ?>
            <a href="<?php echo esc_url($supportUrl); ?>" target="_blank" rel="noopener noreferrer">💬 <?php esc_html_e('Support', 'pressroot'); ?></a>
        <?php endif; ?>
        <details style="display:inline-block">
            <summary style="cursor:pointer;color:#646970"><?php esc_html_e('Edit links', 'pressroot'); ?></summary>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;align-items:center">
                <input type="hidden" name="action" value="prt_save_meta_links">
                <?php wp_nonce_field('prt_save_meta_links'); ?>
                <label class="screen-reader-text" for="prt_docs_url"><?php esc_html_e('Documentation URL', 'pressroot'); ?></label>
                <input type="url" id="prt_docs_url" name="prt_docs_url" class="regular-text" placeholder="<?php esc_attr_e('Documentation URL', 'pressroot'); ?>" value="<?php echo esc_attr($docsUrl); ?>">
                <label class="screen-reader-text" for="prt_support_url"><?php esc_html_e('Support URL', 'pressroot'); ?></label>
                <input type="url" id="prt_support_url" name="prt_support_url" class="regular-text" placeholder="<?php esc_attr_e('Support URL', 'pressroot'); ?>" value="<?php echo esc_attr($supportUrl); ?>">
                <button class="button"><?php esc_html_e('Save', 'pressroot'); ?></button>
            </form>
        </details>
    </div>
    <?php
}

/** Persist the Docs/Support links. Purely cosmetic/navigational — no secrets. */
add_action('admin_post_prt_save_meta_links', function () {
    if (! current_user_can('edit_theme_options') || ! check_admin_referer('prt_save_meta_links')) {
        wp_die(__('Not allowed.', 'pressroot'));
    }
    set_theme_mod('prt_docs_url', isset($_POST['prt_docs_url']) ? esc_url_raw(wp_unslash($_POST['prt_docs_url'])) : '');
    set_theme_mod('prt_support_url', isset($_POST['prt_support_url']) ? esc_url_raw(wp_unslash($_POST['prt_support_url'])) : '');
    wp_safe_redirect(wp_get_referer() ?: prt_settings_tab_url('ai'));
    exit;
});

/**
 * Render the page: branded header, then a left-sidebar menu with the active
 * section's content on the right (replacing the top nav-tab-wrapper this
 * page used to use — same sections, same prt_settings_tab_url() links,
 * just a different chrome, more like a typical settings app than a row of
 * WordPress admin tabs).
 *
 * Defaults to the "ai" (Site Types) tab — the primary one since Style Kits
 * was removed. If that's not visible (Pressroot AI addon switched off) or an
 * invalid ?tab= was passed, falls through to the first tab that IS visible,
 * rather than assuming any specific tab always is.
 */
function prt_settings_render(): void
{
    if (! current_user_can('edit_theme_options')) {
        return;
    }
    $tabs   = prt_settings_tabs();
    $active = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'ai';
    if (! isset($tabs[$active]) || empty($tabs[$active]['visible'])) {
        $active = '';
        foreach ($tabs as $id => $t) {
            if (! empty($t['visible'])) {
                $active = $id;
                break;
            }
        }
    }
    ?>
    <div class="wrap">
        <?php prt_settings_header(); ?>

        <div style="display:flex;gap:28px;align-items:flex-start;margin-top:8px">
            <nav aria-label="<?php esc_attr_e('Pressroot settings sections', 'pressroot'); ?>" style="width:190px;flex-shrink:0">
                <ul style="margin:0;padding:0;list-style:none;display:flex;flex-direction:column;gap:2px">
                    <?php foreach ($tabs as $id => $tab) :
                        if (empty($tab['visible'])) {
                            continue;
                        }
                        $isActive = $id === $active;
                    ?>
                        <li>
                            <a href="<?php echo esc_url(prt_settings_tab_url($id)); ?>"
                               style="display:block;padding:9px 12px;border-radius:6px;text-decoration:none;font-size:14px;<?php echo $isActive ? 'background:#7C5CFF;color:#fff;font-weight:600' : 'color:#1d2327'; ?>">
                                <?php echo esc_html($tab['label']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>

            <div style="flex:1;min-width:0;border-left:1px solid #dcdcde;padding-left:28px">
                <?php
                if (isset($tabs[$active]) && is_callable($tabs[$active]['render'])) {
                    call_user_func($tabs[$active]['render']);
                }
                ?>
            </div>
        </div>
    </div>
    <?php
}
