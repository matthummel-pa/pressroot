<?php

/**
 * Theme setup.
 */

namespace App;

use Illuminate\Support\Facades\Vite;

/**
 * Inject styles into the block editor.
 *
 * @return array
 */
add_filter('block_editor_settings_all', function ($settings) {
    $style = Vite::asset('resources/css/editor.css');

    $settings['styles'][] = [
        'css' => "@import url('{$style}')",
    ];

    return $settings;
});

/**
 * Clear Acorn's compiled Blade views on demand: visit any admin URL with
 * ?prt_view_clear=1. Useful when template edits don't show because the compiled
 * views were cached (e.g. `wp acorn view:cache` / production mode).
 */
add_action('admin_init', function () {
    if (empty($_GET['prt_view_clear']) || ! current_user_can('edit_theme_options')) {
        return;
    }
    $dirs = [
        WP_CONTENT_DIR . '/uploads/acorn-views',
        WP_CONTENT_DIR . '/cache/acorn/views',
    ];
    if (function_exists('config')) {
        $cfg = config('view.compiled');
        if (is_string($cfg) && $cfg !== '') {
            $dirs[] = $cfg;
        }
    }
    $count = 0;
    foreach (array_unique($dirs) as $dir) {
        foreach ((array) glob(rtrim($dir, '/') . '/*.php') as $file) {
            if (@unlink($file)) {
                $count++;
            }
        }
    }
    wp_safe_redirect(add_query_arg('prt_views_cleared', (int) $count, admin_url()));
    exit;
});

/**
 * Load the compiled design stylesheet INTO the editor as a real <link>.
 *
 * `enqueue_block_assets` runs inside the iframed editor canvas AND inside the
 * inserter's pattern/block preview iframes, so this is what makes patterns and
 * blocks preview with the full Paper + Space design (CSS variables, fonts,
 * .prt-* helpers, keyframes). The @import editor style above gets scoped to
 * `.editor-styles-wrapper` and mangled, which is why previews looked unstyled.
 * Editor-only: the front end already loads app.css via @vite.
 */
add_action('enqueue_block_assets', function () {
    if (! is_admin()) {
        return;
    }
    wp_enqueue_style(
        'matthummel-editor-design',
        Vite::asset('resources/css/editor.css'),
        [],
        null
    );
});

/**
 * Inject scripts into the block editor.
 *
 * @return void
 */
add_action('admin_head', function () {
    if (! get_current_screen()?->is_block_editor()) {
        return;
    }

    if (! Vite::isRunningHot()) {
        $dependencies = json_decode(Vite::content('editor.deps.json'));

        foreach ($dependencies as $dependency) {
            if (! wp_script_is($dependency)) {
                wp_enqueue_script($dependency);
            }
        }
    }
    echo Vite::withEntryPoints([
        'resources/js/editor.js',
    ])->toHtml();
});

/**
 * Use the generated theme.json file.
 *
 * @return string
 */
add_filter('theme_file_path', function ($path, $file) {
    return $file === 'theme.json'
        ? public_path('build/assets/theme.json')
        : $path;
}, 10, 2);

/**
 * Disable on-demand block asset loading.
 *
 * @link https://core.trac.wordpress.org/ticket/61965
 */
add_filter('should_load_separate_core_block_assets', '__return_false');

/**
 * Register the initial theme setup.
 *
 * @return void
 */
add_action('after_setup_theme', function () {
    /**
     * Disable full-site editing support.
     *
     * @link https://wptavern.com/gutenberg-10-5-embeds-pdfs-adds-verse-block-color-options-and-introduces-new-patterns
     */
    remove_theme_support('block-templates');

    /**
     * Register the navigation menus.
     *
     * @link https://developer.wordpress.org/reference/functions/register_nav_menus/
     */
    register_nav_menus([
        'primary_navigation' => __('Primary Navigation', 'sage'),
    ]);

    /**
     * Disable the default block patterns.
     *
     * @link https://developer.wordpress.org/block-editor/developers/themes/theme-support/#disabling-the-default-block-patterns
     */
    remove_theme_support('core-block-patterns');

    /**
     * Enable plugins to manage the document title.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#title-tag
     */
    add_theme_support('title-tag');

    /**
     * Enable post thumbnail support.
     *
     * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
     */
    add_theme_support('post-thumbnails');

    /**
     * Enable responsive embed support.
     *
     * @link https://developer.wordpress.org/block-editor/how-to-guides/themes/theme-support/#responsive-embedded-content
     */
    add_theme_support('responsive-embeds');

    /**
     * Enable HTML5 markup support.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#html5
     */
    add_theme_support('html5', [
        'caption',
        'comment-form',
        'comment-list',
        'gallery',
        'search-form',
        'script',
        'style',
    ]);

    /**
     * Enable selective refresh for widgets in customizer.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#customize-selective-refresh-widgets
     */
    add_theme_support('customize-selective-refresh-widgets');
}, 20);

/**
 * Register the theme sidebars.
 *
 * @return void
 */
add_action('widgets_init', function () {
    $config = [
        'before_widget' => '<section class="widget %1$s %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h3>',
        'after_title' => '</h3>',
    ];

    register_sidebar([
        'name' => __('Primary', 'sage'),
        'id' => 'sidebar-primary',
    ] + $config);

    register_sidebar([
        'name' => __('Footer', 'sage'),
        'id' => 'sidebar-footer',
    ] + $config);
});

/**
 * Enqueue Google Fonts (Fraunces display + Inter body).
 */
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'matthummel-fonts',
        'https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Instrument+Serif:ital@0;1&family=JetBrains+Mono:wght@400;500;600&display=swap',
        [],
        null
    );
}, 5);

add_action('admin_enqueue_scripts', function () {
    wp_enqueue_style(
        'matthummel-fonts',
        'https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Instrument+Serif:ital@0;1&family=JetBrains+Mono:wght@400;500;600&display=swap',
        [],
        null
    );
}, 5);

/**
 * Register the "projects" custom post type (case studies) + taxonomy,
 * so the theme is self-contained for local development.
 */
add_action('init', function () {
    register_post_type('projects', [
        'labels' => [
            'name' => __('Projects', 'pressroot'),
            'singular_name' => __('Project', 'pressroot'),
            'add_new_item' => __('Add New Project', 'pressroot'),
            'edit_item' => __('Edit Project', 'pressroot'),
        ],
        'public' => true,
        'has_archive' => false,
        'menu_icon' => 'dashicons-portfolio',
        'rewrite' => ['slug' => 'projects'],
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
        'show_in_rest' => true,
    ]);

    register_taxonomy('project_categories', 'projects', [
        'labels' => [
            'name' => __('Project Categories', 'pressroot'),
            'singular_name' => __('Project Category', 'pressroot'),
        ],
        'public' => true,
        'hierarchical' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'project-category'],
    ]);
});
