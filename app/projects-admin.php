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
        '_prt_gh_owner'   => [__('GitHub owner', 'pressroot'), (function_exists('get_theme_mod') ? get_theme_mod('prt_proj_owner', 'matthummel-pa') : 'matthummel-pa')],
        '_prt_gh_repo'    => [__('GitHub repo (slug)', 'pressroot'), $post->post_name],
        '_prt_eyebrow'    => [__('Eyebrow / label', 'pressroot'), 'GitHub Project'],
        '_prt_demo_url'   => [__('Live site / demo URL', 'pressroot'), 'https://'],
        '_prt_tech_stack' => [__('Tech stack (comma-separated)', 'pressroot'), 'WordPress, PHP, JavaScript'],
    ];
    echo '<div class="prt-project-meta">';
    foreach ($fields as $key => $f) {
        $val = get_post_meta($post->ID, $key, true);
        echo '<p style="margin:0 0 12px">';
        echo '<label for="' . esc_attr($key) . '" style="display:block;font-weight:600;margin-bottom:4px">' . esc_html($f[0]) . '</label>';
        echo '<input type="text" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" value="' . esc_attr($val) . '" placeholder="' . esc_attr($f[1]) . '" style="width:100%">';
        echo '</p>';
    }
    // Featured checkbox
    $featured = (bool) get_post_meta($post->ID, '_prt_featured', true);
    echo '<p style="margin:0 0 12px">';
    echo '<label style="display:flex;align-items:center;gap:8px;font-weight:600;cursor:pointer">';
    echo '<input type="checkbox" name="_prt_featured" id="_prt_featured" value="1"' . checked($featured, true, false) . '>';
    echo esc_html__('Featured project (shown prominently)', 'pressroot');
    echo '</label></p>';
    echo '<p class="description">' . esc_html__('Owner + repo power the live GitHub data. Leave repo blank to use the post slug. Tech stack appears as pills on the project page.', 'pressroot') . '</p>';
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
    foreach (['_prt_gh_owner', '_prt_gh_repo', '_prt_eyebrow', '_prt_demo_url', '_prt_tech_stack'] as $key) {
        if (isset($_POST[$key])) {
            $raw = wp_unslash($_POST[$key]);
            $val = ($key === '_prt_demo_url') ? esc_url_raw($raw) : sanitize_text_field($raw);
            update_post_meta($post_id, $key, $val);
        }
    }
    // Featured checkbox (unchecked = not in $_POST)
    update_post_meta($post_id, '_prt_featured', isset($_POST['_prt_featured']) ? '1' : '');
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
        echo esc_html(get_post_meta($post_id, '_prt_eyebrow', true) ?: '—');
    }
}, 10, 2);

/** REST endpoint: GET /wp-json/prt/v1/github-repos?user=&count=&sort= */
add_action('rest_api_init', function () {
    register_rest_route('prt/v1', '/github-repos', [
        'methods'             => 'GET',
        'permission_callback' => '__return_true',
        'callback'            => function (\WP_REST_Request $req) {
            $user  = sanitize_text_field($req->get_param('user') ?: 'matthummel-pa');
            $count = max(1, min(30, (int) ($req->get_param('count') ?: 12)));
            $sort  = sanitize_key($req->get_param('sort') ?: 'updated');
            return rest_ensure_response(\App\Github::fetchRepos($user, $count, $sort));
        },
        'args' => [
            'user'  => ['sanitize_callback' => 'sanitize_text_field', 'default' => 'matthummel-pa'],
            'count' => ['sanitize_callback' => 'absint', 'default' => 12],
            'sort'  => ['sanitize_callback' => 'sanitize_key', 'default' => 'updated'],
        ],
    ]);
});
