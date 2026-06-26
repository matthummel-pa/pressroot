<?php

/**
 * Projects custom admin area: "Project Details" meta box (GitHub owner/repo,
 * eyebrow, demo URL) feeding the project template, plus admin list columns.
 */

namespace App;

add_action('add_meta_boxes', function () {
    add_meta_box('prt_project_details', __('Project Details', 'pressroot'), 'App\\prt_project_metabox', 'projects', 'side', 'high');
});

function prt_project_metabox($post)
{
    wp_nonce_field('prt_project_save', 'prt_project_nonce');
    $fields = [
        '_prt_gh_owner' => [__('GitHub owner', 'pressroot'), (function_exists('get_theme_mod') ? get_theme_mod('prt_proj_owner', 'matthummel-pa') : 'matthummel-pa')],
        '_prt_gh_repo'  => [__('GitHub repo', 'pressroot'), $post->post_name],
        '_prt_eyebrow'  => [__('Eyebrow / label', 'pressroot'), 'GitHub Project'],
        '_prt_demo_url' => [__('Live demo URL', 'pressroot'), 'https://'],
    ];
    echo '<div class="prt-project-meta">';
    foreach ($fields as $key => $f) {
        $val = get_post_meta($post->ID, $key, true);
        echo '<p style="margin:0 0 12px">';
        echo '<label for="' . esc_attr($key) . '" style="display:block;font-weight:600;margin-bottom:4px">' . esc_html($f[0]) . '</label>';
        echo '<input type="text" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" value="' . esc_attr($val) . '" placeholder="' . esc_attr($f[1]) . '" style="width:100%">';
        echo '</p>';
    }
    echo '<p class="description">' . esc_html__('Owner + repo power the live GitHub data section. Leave repo blank to use the post slug.', 'pressroot') . '</p>';
    echo '</div>';
}

add_action('save_post_projects', function ($post_id) {
    if (! isset($_POST['prt_project_nonce']) || ! wp_verify_nonce($_POST['prt_project_nonce'], 'prt_project_save')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (! current_user_can('edit_post', $post_id)) {
        return;
    }
    foreach (['_prt_gh_owner', '_prt_gh_repo', '_prt_eyebrow', '_prt_demo_url'] as $key) {
        if (isset($_POST[$key])) {
            $raw = wp_unslash($_POST[$key]);
            $val = ($key === '_prt_demo_url') ? esc_url_raw($raw) : sanitize_text_field($raw);
            update_post_meta($post_id, $key, $val);
        }
    }
});

/** Admin list columns. */
add_filter('manage_projects_posts_columns', function ($cols) {
    $new = [];
    foreach ($cols as $k => $v) {
        $new[$k] = $v;
        if ($k === 'title') {
            $new['prt_repo']    = __('Repo', 'pressroot');
            $new['prt_eyebrow'] = __('Label', 'pressroot');
        }
    }
    return $new;
});

add_action('manage_projects_posts_custom_column', function ($col, $post_id) {
    if ($col === 'prt_repo') {
        $owner = get_post_meta($post_id, '_prt_gh_owner', true) ?: get_theme_mod('prt_proj_owner', 'matthummel-pa');
        $repo  = get_post_meta($post_id, '_prt_gh_repo', true);
        if ($repo) {
            echo '<a href="' . esc_url("https://github.com/{$owner}/{$repo}") . '" target="_blank" rel="noopener">' . esc_html("{$owner}/{$repo}") . '</a>';
        } else {
            echo '&mdash;';
        }
    }
    if ($col === 'prt_eyebrow') {
        echo esc_html(get_post_meta($post_id, '_prt_eyebrow', true) ?: 'â€”');
    }
}, 10, 2);
