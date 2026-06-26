<?php

/**
 * GitHub feature blocks (reuse App\Github cached engine + auth token):
 *  - prt/repo-card     : one repo (desc + stats).
 *  - prt/repo-grid     : a user's repos as a responsive card grid.
 *  - prt/gh-stats      : a user/org profile (avatar, bio, followers, repos).
 *  - prt/gh-releases   : recent releases for a repo.
 */

namespace App;

function prt_gh_default_owner()
{
    return get_theme_mod('prt_proj_owner', 'matthummel-pa');
}

function prt_gh_blocks_attrs()
{
    return [
        'repo-card' => [
            'owner'     => ['type' => 'string', 'default' => ''],
            'repo'      => ['type' => 'string', 'default' => ''],
            'showDesc'  => ['type' => 'boolean', 'default' => true],
            'showStats' => ['type' => 'boolean', 'default' => true],
        ],
        'repo-grid' => [
            'username' => ['type' => 'string', 'default' => ''],
            'count'    => ['type' => 'number', 'default' => 6],
            'columns'  => ['type' => 'number', 'default' => 2],
            'sort'     => ['type' => 'string', 'default' => 'updated'],
        ],
        'gh-stats' => [
            'username'   => ['type' => 'string', 'default' => ''],
            'showAvatar' => ['type' => 'boolean', 'default' => true],
            'showBio'    => ['type' => 'boolean', 'default' => true],
        ],
        'gh-releases' => [
            'owner' => ['type' => 'string', 'default' => ''],
            'repo'  => ['type' => 'string', 'default' => ''],
            'count' => ['type' => 'number', 'default' => 5],
        ],
    ];
}

add_action('init', function () {
    $path = 'resources/js/github-blocks-editor.js';
    wp_register_script(
        'prt-github-blocks',
        get_theme_file_uri($path),
        ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-i18n'],
        file_exists(get_theme_file_path($path)) ? filemtime(get_theme_file_path($path)) : '1',
        true
    );
    wp_localize_script('prt-github-blocks', 'prtGithubBlocks', ['owner' => prt_gh_default_owner()]);

    $defs = prt_gh_blocks_attrs();
    $map  = [
        'repo-card'   => 'prt_render_repo_card',
        'repo-grid'   => 'prt_render_repo_grid',
        'gh-stats'    => 'prt_render_gh_stats',
        'gh-releases' => 'prt_render_gh_releases',
    ];
    foreach ($map as $slug => $cb) {
        register_block_type('prt/' . $slug, [
            'api_version'     => 2,
            'editor_script'   => 'prt-github-blocks',
            'attributes'      => $defs[$slug],
            'render_callback' => __NAMESPACE__ . '\\' . $cb,
            'supports'        => ['align' => ['wide', 'full'], 'spacing' => ['margin' => true]],
        ]);
    }
}, 12);

function prt_gh_rest_placeholder($msg)
{
    return (defined('REST_REQUEST') && REST_REQUEST)
        ? '<p style="opacity:.7;font-style:italic">' . esc_html($msg) . '</p>'
        : '';
}

function prt_lang_dot($lang)
{
    $c = [
        'PHP' => '#4F5D95', 'JavaScript' => '#f1e05a', 'TypeScript' => '#3178c6', 'CSS' => '#563d7c',
        'HTML' => '#e34c26', 'Python' => '#3572A5', 'Go' => '#00ADD8', 'Rust' => '#dea584',
        'Shell' => '#89e051', 'Java' => '#b07219', 'Ruby' => '#701516', 'Blade' => '#f7523f',
    ];
    $hex = $c[$lang] ?? '#8b949e';
    return '<span class="prt-lang"><span class="prt-lang-dot" style="background:' . esc_attr($hex) . '"></span>' . esc_html($lang) . '</span>';
}

/** Single repo card. */
function prt_render_repo_card($a)
{
    $owner = $a['owner'] ?: prt_gh_default_owner();
    $repo  = trim((string) ($a['repo'] ?? ''));
    if ($repo === '') {
        return prt_gh_rest_placeholder(__('Enter a repository name.', 'pressroot'));
    }
    $d = Github::fetch($owner, $repo);
    if (empty($d) || empty($d['url'])) {
        return prt_gh_rest_placeholder(sprintf(__('Could not load %s/%s.', 'pressroot'), $owner, $repo));
    }
    $out  = '<div class="wp-block-prt-repo-card prt-repo-card">';
    $out .= '<a class="prt-repo-name" href="' . esc_url($d['url']) . '" target="_blank" rel="noopener">' . esc_html($owner) . '/<strong>' . esc_html($repo) . '</strong></a>';
    if (! empty($a['showDesc']) && ! empty($d['desc'])) {
        $out .= '<p class="prt-repo-desc">' . esc_html($d['desc']) . '</p>';
    }
    if (! empty($a['showStats'])) {
        $bits = [];
        $bits[] = '<span>&#9733; ' . number_format((int) $d['stars']) . '</span>';
        $bits[] = '<span>&#11489; ' . number_format((int) $d['forks']) . '</span>';
        if (! empty($d['lang'])) {
            $bits[] = prt_lang_dot($d['lang']);
        }
        if (! empty($d['release'])) {
            $bits[] = '<span>' . esc_html($d['release']) . '</span>';
        }
        $out .= '<div class="prt-repo-meta">' . implode('', $bits) . '</div>';
    }
    return $out . '</div>';
}

/** Grid of a user's repos. */
function prt_render_repo_grid($a)
{
    $user = $a['username'] ?: prt_gh_default_owner();
    $cols = max(1, min(4, (int) ($a['columns'] ?? 2)));
    $repos = Github::fetchRepos($user, (int) ($a['count'] ?? 6), (string) ($a['sort'] ?? 'updated'));
    if (empty($repos)) {
        return prt_gh_rest_placeholder(sprintf(__('No public repos found for %s.', 'pressroot'), $user));
    }
    $uid = 'prt-rg-' . wp_unique_id();
    $out  = '<style>#' . $uid . '{display:grid;grid-template-columns:repeat(' . $cols . ',1fr);gap:16px;}@media(max-width:680px){#' . $uid . '{grid-template-columns:1fr;}}</style>';
    $out .= '<div id="' . esc_attr($uid) . '" class="wp-block-prt-repo-grid prt-repo-grid">';
    foreach ($repos as $r) {
        $out .= '<a class="prt-repo-card prt-repo-card--link" href="' . esc_url($r['url']) . '" target="_blank" rel="noopener">';
        $out .= '<span class="prt-repo-name"><strong>' . esc_html($r['name']) . '</strong></span>';
        if (! empty($r['desc'])) {
            $out .= '<p class="prt-repo-desc">' . esc_html(wp_trim_words($r['desc'], 18)) . '</p>';
        }
        $meta = '<span>&#9733; ' . number_format($r['stars']) . '</span><span>&#11489; ' . number_format($r['forks']) . '</span>';
        if (! empty($r['lang'])) {
            $meta .= prt_lang_dot($r['lang']);
        }
        $out .= '<div class="prt-repo-meta">' . $meta . '</div></a>';
    }
    return $out . '</div>';
}

/** Profile stats. */
function prt_render_gh_stats($a)
{
    $user = $a['username'] ?: prt_gh_default_owner();
    $u = Github::fetchUser($user);
    if (empty($u) || empty($u['url'])) {
        return prt_gh_rest_placeholder(sprintf(__('Could not load profile for %s.', 'pressroot'), $user));
    }
    $out = '<div class="wp-block-prt-gh-stats prt-gh-stats">';
    $out .= '<div class="prt-gh-stats-head">';
    if (! empty($a['showAvatar']) && ! empty($u['avatar'])) {
        $out .= '<img class="prt-gh-avatar" src="' . esc_url($u['avatar']) . '" alt="" width="64" height="64" loading="lazy">';
    }
    $out .= '<div><a class="prt-repo-name" href="' . esc_url($u['url']) . '" target="_blank" rel="noopener"><strong>' . esc_html($u['name']) . '</strong></a>';
    if (! empty($a['showBio']) && ! empty($u['bio'])) {
        $out .= '<p class="prt-repo-desc">' . esc_html($u['bio']) . '</p>';
    }
    $out .= '</div></div>';
    $out .= '<ul class="stat-grid"><li><strong>' . number_format($u['followers']) . '</strong><span>Followers</span></li>'
        . '<li><strong>' . number_format($u['public_repos']) . '</strong><span>Repos</span></li>'
        . '<li><strong>' . number_format($u['following']) . '</strong><span>Following</span></li></ul>';
    return $out . '</div>';
}

/** Releases feed. */
function prt_render_gh_releases($a)
{
    $owner = $a['owner'] ?: prt_gh_default_owner();
    $repo  = trim((string) ($a['repo'] ?? ''));
    if ($repo === '') {
        return prt_gh_rest_placeholder(__('Enter a repository name.', 'pressroot'));
    }
    $rels = Github::fetchReleases($owner, $repo, (int) ($a['count'] ?? 5));
    if (empty($rels)) {
        return prt_gh_rest_placeholder(sprintf(__('No releases found for %s/%s.', 'pressroot'), $owner, $repo));
    }
    $out = '<ul class="wp-block-prt-gh-releases prt-releases">';
    foreach ($rels as $r) {
        $out .= '<li><a href="' . esc_url($r['url']) . '" target="_blank" rel="noopener"><strong>' . esc_html($r['name']) . '</strong></a>';
        if ($r['prerelease']) {
            $out .= ' <span class="prt-rel-pre">pre-release</span>';
        }
        if ($r['date']) {
            $out .= ' <span class="prt-rel-date">' . esc_html($r['date']) . '</span>';
        }
        $out .= '</li>';
    }
    return $out . '</ul>';
}

/** Block styles. */
add_action('prt_head_end', function () {
    echo "\n<style id=\"prt-gh-blocks\">"
        . '.prt-repo-card{display:block;border:1px solid var(--color-line,#e6e2d9);border-radius:12px;padding:16px 18px;background:var(--color-surface,#fff);text-decoration:none;}'
        . '.prt-repo-card--link{transition:transform .15s ease,box-shadow .15s ease;}'
        . '.prt-repo-card--link:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(23,25,30,.08);}'
        . '.prt-repo-name{font-size:16px;color:var(--color-ink,#17191e);text-decoration:none;}'
        . '.prt-repo-name strong{color:var(--color-green,#2f6b4e);}'
        . '.prt-repo-desc{font-size:14px;color:var(--color-body,#2b2f36);margin:8px 0 10px;}'
        . '.prt-repo-meta{display:flex;flex-wrap:wrap;gap:14px;font-size:13px;color:var(--color-muted,#5c636c);align-items:center;}'
        . '.prt-lang{display:inline-flex;align-items:center;gap:5px;}.prt-lang-dot{width:10px;height:10px;border-radius:50%;display:inline-block;}'
        . '.prt-gh-stats-head{display:flex;gap:14px;align-items:center;margin-bottom:14px;}.prt-gh-avatar{border-radius:50%;}'
        . '.prt-releases{list-style:none;margin:0;padding:0;}.prt-releases li{padding:10px 0;border-bottom:1px solid var(--color-line,#e6e2d9);display:flex;gap:10px;align-items:center;flex-wrap:wrap;}'
        . '.prt-rel-pre{font-size:11px;background:#fff3cd;color:#7a5b00;border-radius:4px;padding:1px 6px;}.prt-rel-date{font-size:12px;color:var(--color-muted,#5c636c);margin-left:auto;}'
        . "</style>\n";
}, 13);
