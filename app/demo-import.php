<?php

/**
 * Starter Sites — one-click demo import. Each starter site composes pages from
 * the registered block patterns, applies a Style Kit, sets the front page, and
 * builds + assigns the primary menu. Existing pages are reused, never duplicated.
 *
 * Appearance -> Starter Sites.
 */

namespace App;

/**
 * Registry of available "starter sites" (personas). Each entry defines the
 * pages to create (from patterns and/or raw block markup), which Style Kit
 * to apply, the primary menu order, and which page becomes the front page.
 * Single source of truth consumed by both the admin picker UI
 * (prt_starter_render()) and the importer (admin_post_prt_import_demo).
 *
 * @return array<string, array{label:string,desc:string,kit:string,pages:array,menu:array,front:string}>
 */
function prt_starter_sites()
{
    return [
        'portfolio' => [
            'label' => __('Developer Portfolio', 'pressroot'),
            'desc'  => __('A personal portfolio with live GitHub repos, services, stats, and a contact CTA.', 'pressroot'),
            'kit'   => 'paper_space',
            'pages' => [
                'home'     => ['title' => __('Home', 'pressroot'), 'patterns' => ['matthummel/hero-dev', 'matthummel/services-three', 'matthummel/stats-four', 'matthummel/testimonial-single', 'matthummel/contact-cta']],
                'about'    => ['title' => __('About', 'pressroot'), 'patterns' => ['matthummel/about-two-col', 'matthummel/feature-grid']],
                'projects' => ['title' => __('Projects', 'pressroot'), 'raw' => '<!-- wp:heading {"textAlign":"center"} --><h2 class="wp-block-heading has-text-align-center">Projects</h2><!-- /wp:heading --><!-- wp:prt/repo-grid {"count":6,"columns":2} /-->'],
                'contact'  => ['title' => __('Contact', 'pressroot'), 'patterns' => ['matthummel/contact-cta']],
            ],
            'menu'  => ['home', 'about', 'projects', 'contact'],
            'front' => 'home',
        ],
        'agency' => [
            'label' => __('Studio / Agency', 'pressroot'),
            'desc'  => __('A services-led site with hero, services, pricing, testimonials, and CTAs.', 'pressroot'),
            'kit'   => 'sage_classic',
            'pages' => [
                'home'     => ['title' => __('Home', 'pressroot'), 'patterns' => ['matthummel/hero-centered-minimal', 'matthummel/services-three', 'matthummel/pricing', 'matthummel/testimonials', 'matthummel/cta-split']],
                'about'    => ['title' => __('About', 'pressroot'), 'patterns' => ['matthummel/about-two-col', 'matthummel/stats-four']],
                'services' => ['title' => __('Services', 'pressroot'), 'patterns' => ['matthummel/services-three', 'matthummel/pricing']],
                'contact'  => ['title' => __('Contact', 'pressroot'), 'patterns' => ['matthummel/contact-cta']],
            ],
            'menu'  => ['home', 'about', 'services', 'contact'],
            'front' => 'home',
        ],
    ];
}

/**
 * Assemble a page's block markup by concatenating the content of each named
 * registered pattern (in order), then appending any raw block markup. Falls
 * back to an empty paragraph so wp_insert_post() never receives blank
 * content (which would make the page look broken/uneditable in the editor).
 */
function prt_compose_page($def)
{
    $out = '';
    if (! empty($def['patterns']) && class_exists('WP_Block_Patterns_Registry')) {
        $reg = \WP_Block_Patterns_Registry::get_instance();
        foreach ($def['patterns'] as $name) {
            $p = $reg->get_registered($name);
            if ($p && ! empty($p['content'])) {
                $out .= $p['content'] . "\n";
            }
        }
    }
    if (! empty($def['raw'])) {
        $out .= $def['raw'];
    }
    return $out !== '' ? $out : '<!-- wp:paragraph --><p></p><!-- /wp:paragraph -->';
}

// Adds the "Starter Sites" page under Appearance, gated to users who can
// edit theme options (same capability the rest of the Customizer uses).
add_action('admin_menu', function () {
    add_theme_page(__('Starter Sites', 'pressroot'), __('Starter Sites', 'pressroot'), 'edit_theme_options', 'prt-starter-sites', __NAMESPACE__ . '\\prt_starter_render');
});

/**
 * Renders the Appearance -> Starter Sites picker: one card per entry in
 * prt_starter_sites(), each with a confirm-before-submit import button that
 * posts to admin_post_prt_import_demo.
 */
function prt_starter_render()
{
    if (! current_user_can('edit_theme_options')) {
        return;
    }
    $post = admin_url('admin-post.php');
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Starter Sites', 'pressroot'); ?></h1>
        <?php if (isset($_GET['prt_demo']) && $_GET['prt_demo'] === 'done') : ?>
            <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Starter site imported — pages, menu, front page, and style kit are set. View your site!', 'pressroot'); ?></p></div>
        <?php endif; ?>
        <p class="description" style="max-width:640px"><?php esc_html_e('One click builds a full set of pages from the theme\'s patterns, applies a matching Style Kit, sets the homepage, and creates the primary menu. Existing pages with the same name are reused.', 'pressroot'); ?></p>

        <div style="display:flex;flex-wrap:wrap;gap:18px;margin-top:18px">
            <?php foreach (prt_starter_sites() as $id => $site) : ?>
                <div style="width:320px;border:1px solid #dcdcde;border-radius:12px;padding:18px;background:#fff">
                    <h2 style="margin-top:0;font-size:16px"><?php echo esc_html($site['label']); ?></h2>
                    <p style="color:#646970;font-size:13px;min-height:48px"><?php echo esc_html($site['desc']); ?></p>
                    <p style="font-size:12px;color:#646970"><?php printf(esc_html__('Pages: %s', 'pressroot'), esc_html(implode(', ', array_map(fn ($p) => $p['title'], $site['pages'])))); ?></p>
                    <form method="post" action="<?php echo esc_url($post); ?>" onsubmit="return confirm('<?php echo esc_js(__('Import this starter site? It will create pages, set the homepage and menu, and apply a style kit.', 'pressroot')); ?>');">
                        <input type="hidden" name="action" value="prt_import_demo">
                        <input type="hidden" name="site" value="<?php echo esc_attr($id); ?>">
                        <?php wp_nonce_field('prt_import_demo'); ?>
                        <button class="button button-primary" style="width:100%"><?php esc_html_e('Import this starter site', 'pressroot'); ?></button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

/**
 * Handles the "Import this starter site" form submission. Idempotent by
 * design: pages are matched by slug and reused rather than duplicated (only
 * empty existing pages get their content refreshed), and the primary menu is
 * only (re)built if it doesn't already have items — so re-running the import,
 * or importing a second starter site, won't stomp on manual edits.
 *
 * Order matters: style kit, then pages (so IDs exist), then front page and
 * menu (which depend on those IDs).
 */
add_action('admin_post_prt_import_demo', function () {
    if (! current_user_can('edit_theme_options') || ! check_admin_referer('prt_import_demo')) {
        wp_die('Not allowed');
    }
    $sites = prt_starter_sites();
    $id    = isset($_POST['site']) ? sanitize_key($_POST['site']) : '';
    if (! isset($sites[$id])) {
        wp_safe_redirect(admin_url('themes.php?page=prt-starter-sites'));
        exit;
    }
    $site = $sites[$id];

    // 1) style kit
    if (function_exists('App\\prt_style_kits')) {
        $kits = prt_style_kits();
        if (isset($kits[$site['kit']])) {
            foreach ($kits[$site['kit']]['mods'] as $k => $v) {
                set_theme_mod($k, $v);
            }
        }
    }

    // 2) pages
    $ids = [];
    foreach ($site['pages'] as $slug => $def) {
        $existing = get_page_by_path($slug);
        if ($existing) {
            $ids[$slug] = $existing->ID;
            // refresh content only if the page is empty
            if (trim((string) $existing->post_content) === '') {
                wp_update_post(['ID' => $existing->ID, 'post_content' => prt_compose_page($def)]);
            }
            continue;
        }
        $ids[$slug] = wp_insert_post([
            'post_title'   => $def['title'],
            'post_name'    => $slug,
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_content' => prt_compose_page($def),
        ]);
    }

    // 3) front page
    if (! empty($site['front']) && ! empty($ids[$site['front']])) {
        update_option('show_on_front', 'page');
        update_option('page_on_front', $ids[$site['front']]);
    }

    // 4) primary menu
    // NOTE(audit): looks up the menu by its translated display name ('Primary'),
    // not by slug/ID. If the site locale changes after the menu is created, or a
    // user renames the "Primary" menu, this won't find it and will silently
    // create a second menu instead of reusing/updating the original.
    $menu = wp_get_nav_menu_object(__('Primary', 'pressroot'));
    $menu_id = $menu ? $menu->term_id : wp_create_nav_menu(__('Primary', 'pressroot'));
    if (! is_wp_error($menu_id)) {
        $have = wp_get_nav_menu_items($menu_id) ?: [];
        if (empty($have)) {
            foreach ($site['menu'] as $slug) {
                if (! empty($ids[$slug])) {
                    wp_update_nav_menu_item($menu_id, 0, [
                        'menu-item-title'     => $site['pages'][$slug]['title'],
                        'menu-item-object'    => 'page',
                        'menu-item-object-id' => $ids[$slug],
                        'menu-item-type'      => 'post_type',
                        'menu-item-status'    => 'publish',
                    ]);
                }
            }
        }
        $loc = get_theme_mod('nav_menu_locations', []);
        $loc['primary_navigation'] = $menu_id;
        set_theme_mod('nav_menu_locations', $loc);
    }

    wp_safe_redirect(admin_url('themes.php?page=prt-starter-sites&prt_demo=done'));
    exit;
});
