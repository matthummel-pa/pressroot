<?php

/**
 * GitHub feature blocks (reuse App\Github cached engine + auth token):
 *  - prt/repo-card     : one repo (desc + stats).
 *  - prt/repo-grid     : a user's repos as a responsive card grid.
 *  - prt/gh-stats      : a user/org profile (avatar, bio, followers, repos).
 *  - prt/gh-releases   : recent releases for a repo.
 *  - prt/gh-repo       : full repo profile (topics, languages, releases, README).
 *
 * All server-side rendered (render_callback, not static block markup) so the
 * data is always fresh at request time within Github.php's own cache TTL, and
 * so the editor preview (via wp-server-side-render) always matches the front
 * end exactly — there's no separate client-side rendering path to drift out
 * of sync.
 */

namespace App;

/**
 * Default GitHub owner/org used by any block instance that doesn't specify
 * its own owner/username attribute, sourced from the "Projects" Customizer
 * setting so authors don't have to retype the same owner on every block.
 */
function prt_gh_default_owner()
{
    return get_theme_mod('prt_proj_owner', 'matthummel-pa');
}

/**
 * Single source of truth for each block's registered attributes (used both
 * by register_block_type() below and implicitly documents what each render
 * callback can expect in $a). Keeping this as one lookup array — keyed by the
 * same slug used in the render-callback map below — means adding a new
 * attribute only requires editing one place instead of two.
 */
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
        'gh-repo' => [
            'owner'         => ['type' => 'string',  'default' => ''],
            'repo'          => ['type' => 'string',  'default' => ''],
            'showTopics'    => ['type' => 'boolean', 'default' => true],
            'showLanguages' => ['type' => 'boolean', 'default' => true],
            'showReleases'  => ['type' => 'boolean', 'default' => true],
            'showReadme'    => ['type' => 'boolean', 'default' => true],
            'releaseCount'  => ['type' => 'number',  'default' => 3],
        ],
    ];
}

/**
 * Registers the shared editor script (one JS bundle drives all five block
 * types' editor UI via wp-server-side-render, so the editor just previews
 * whatever the PHP render callback below produces) and the five `prt/*`
 * block types themselves, each wired to its own render_callback so the
 * front end and editor preview always render identical server-side HTML.
 * Priority 12 just needs to run after `init`'s default block-registration
 * timing expectations; nothing else in this file depends on ordering.
 */
add_action('init', function () {
    $path = 'resources/js/github-blocks-editor.js';
    wp_register_script(
        'prt-github-blocks',
        get_theme_file_uri($path),
        ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-i18n'],
        file_exists(get_theme_file_path($path)) ? filemtime(get_theme_file_path($path)) : '1',
        true
    );
    wp_localize_script('prt-github-blocks', 'mhGithubBlocks', ['owner' => prt_gh_default_owner()]);

    $defs = prt_gh_blocks_attrs();
    $map  = [
        'repo-card'   => 'prt_render_repo_card',
        'repo-grid'   => 'prt_render_repo_grid',
        'gh-stats'    => 'prt_render_gh_stats',
        'gh-releases' => 'prt_render_gh_releases',
        'gh-repo'     => 'prt_render_gh_repo',
    ];
    foreach ($map as $slug => $cb) {
        register_block_type('prt/' . $slug, [
            'api_version'     => 2,
            'editor_script'   => 'prt-github-blocks',
            'attributes'      => $defs[$slug],
            'render_callback' => __NAMESPACE__ . '\\' . $cb,
            'supports'        => ['align' => ['wide', 'full'], 'spacing' => ['margin' => true]],
            'example'         => ['viewportWidth' => 900],
        ]);
    }
}, 12);

/**
 * Shared "nothing to show" placeholder for all five blocks. Only renders
 * visible text inside a REST request (i.e. the editor's server-side-render
 * preview) — on the actual front end it returns an empty string so a
 * misconfigured or failed block just disappears instead of showing an error
 * message to site visitors.
 */
function prt_gh_rest_placeholder($msg)
{
    return (defined('REST_REQUEST') && REST_REQUEST)
        ? '<p style="opacity:.7;font-style:italic">' . esc_html($msg) . '</p>'
        : '';
}

/**
 * Renders a colored dot + label for a language name, mimicking GitHub's own
 * language-color convention (same hex values GitHub uses in its UI) so the
 * blocks feel visually consistent with GitHub itself. Unknown languages fall
 * back to GitHub's own generic gray rather than a blank/missing dot.
 */
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

/**
 * Render callback for prt/repo-card: a single repo's name, description, and
 * stats, pulled from the cached Github::fetch() engine (app/Github.php).
 * Falls back to the Customizer's default owner when the block instance
 * doesn't specify one, so authors can drop the block in and just type a repo
 * name.
 */
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

/**
 * Render callback for prt/repo-grid: a responsive card grid of a user's
 * repos. Generates a scoped inline <style> per block instance (keyed to a
 * wp_unique_id()) rather than a shared stylesheet class, because the number
 * of grid columns is a per-block attribute and needs its own CSS rule.
 */
function prt_render_repo_grid($a)
{
    $user = $a['username'] ?: prt_gh_default_owner();
    // Clamp to 1-4 columns — the design only has grid/responsive CSS for that
    // range, and an unbounded column count would break small-viewport layout.
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

/**
 * Render callback for prt/gh-stats: a user/org profile card (avatar, name,
 * bio, follower/repo/following counts).
 */
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

/**
 * Render callback for prt/gh-releases: a list of recent GitHub releases for
 * one repo, flagging pre-releases so visitors don't mistake a beta for a
 * stable release.
 */
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

/**
 * Render callback for prt/gh-repo: the "full profile" block. Delegates the
 * actual HTML assembly to Github::renderRepo() (app/Github.php) — this
 * function's only job is translating block attributes into that method's
 * options array, keeping the heavier topics/languages/releases/README
 * rendering logic in the shared engine rather than duplicated per block.
 */
function prt_render_gh_repo($a)
{
    $owner = $a['owner'] ?: prt_gh_default_owner();
    $repo  = trim((string) ($a['repo'] ?? ''));
    if ($repo === '') {
        return prt_gh_rest_placeholder(__('Enter a repository name.', 'pressroot'));
    }
    $html = Github::renderRepo($owner, $repo, [
        'topics'       => ! empty($a['showTopics']),
        'languages'    => ! empty($a['showLanguages']),
        'releases'     => ! empty($a['showReleases']),
        'readme'       => ! empty($a['showReadme']),
        'releaseCount' => (int) ($a['releaseCount'] ?? 3),
    ]);
    if ($html === '') {
        return prt_gh_rest_placeholder(sprintf(__('Could not load %s/%s.', 'pressroot'), $owner, $repo));
    }
    return '<div class="wp-block-prt-gh-repo">' . $html . '</div>';
}

/**
 * Block styles: one inline stylesheet covering all five prt/* block types
 * (repo card, repo grid, stats, releases, and the larger gh-repo profile).
 * Kept as a single hand-rolled inline <style> rather than an enqueued CSS
 * file so the blocks work with zero build step / asset registration, and
 * only prints when prt_head_end fires (i.e. on the front end, not on every
 * admin request).
 */
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
        // ── Full repo profile (prt/gh-repo) ─────────────────────────────
        . '.prt-gh-repo{background:var(--color-card,#fff);border:1.5px solid var(--color-line,#ECE4F8);border-radius:22px;padding:32px;}'
        . '.prt-ghr-titlewrap{margin:0;font-size:inherit;font-weight:inherit;line-height:inherit;display:inline;}'
        . '.prt-ghr-title{font-family:var(--font-mono);font-size:15px;color:var(--color-ink,#1B1830);text-decoration:none;font-weight:400;}.prt-ghr-title strong{color:var(--color-purple,#7C5CFF);}'
        . '.prt-ghr-desc{font-family:var(--font-display);font-size:18px;line-height:1.5;color:var(--color-body,#4A4660);margin:10px 0 0;}'
        . '.prt-ghr-topics{display:flex;flex-wrap:wrap;gap:8px;margin-top:18px;}'
        . '.prt-ghr-topic{font-family:var(--font-mono);font-size:12px;background:var(--color-green-tint,#EFE9FF);color:var(--color-purple,#7C5CFF);border-radius:999px;padding:5px 12px;}'
        . '.prt-ghr-stats{display:flex;flex-wrap:wrap;gap:12px;margin-top:18px;}'
        . '.prt-ghr-stat{font-family:var(--font-display);font-weight:700;font-size:15px;color:var(--color-ink,#1B1830);background:var(--color-paper,#FFFDF7);border:1px solid var(--color-line,#ECE4F8);border-radius:12px;padding:8px 14px;}'
        . '.prt-ghr-stat em{font-style:normal;font-weight:500;color:var(--color-faint,#7C75A8);font-size:13px;}'
        . '.prt-ghr-section{margin-top:28px;padding-top:24px;border-top:1.5px solid var(--color-line,#ECE4F8);}'
        . '.prt-ghr-sechead{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;}'
        . '.prt-ghr-h{font-family:var(--font-display);font-weight:800;font-size:15px;letter-spacing:.02em;text-transform:uppercase;color:var(--color-faint,#7C75A8);margin:0 0 14px;}'
        . '.prt-ghr-changelog{font-family:var(--font-mono);font-size:13px;color:var(--color-purple,#7C5CFF);text-decoration:none;font-weight:600;}'
        . '.prt-ghr-langbar{display:flex;height:10px;border-radius:999px;overflow:hidden;background:var(--color-line,#ECE4F8);}.prt-ghr-langseg{height:100%;}'
        . '.prt-ghr-langlist{display:flex;flex-wrap:wrap;gap:14px;margin-top:12px;}'
        . '.prt-ghr-langitem{font-family:var(--font-mono);font-size:13px;color:var(--color-muted,#5A5676);display:inline-flex;align-items:center;gap:6px;}.prt-ghr-langitem em{font-style:normal;color:var(--color-faint,#7C75A8);}'
        . '.prt-ghr-langdot{width:10px;height:10px;border-radius:50%;display:inline-block;}'
        . '.prt-ghr-releases{list-style:none;margin:0;padding:0;}'
        . '.prt-ghr-rel{padding:14px 0;border-bottom:1px solid var(--color-line,#ECE4F8);}.prt-ghr-rel:last-child{border-bottom:0;}'
        . '.prt-ghr-relhead{display:flex;align-items:center;gap:10px;flex-wrap:wrap;}.prt-ghr-relhead a{font-family:var(--font-display);color:var(--color-ink,#1B1830);text-decoration:none;font-size:16px;}'
        . '.prt-ghr-pre{font-family:var(--font-mono);font-size:11px;background:#FFE9E0;color:#7a2e00;border-radius:6px;padding:2px 7px;}'
        . '.prt-ghr-reldate{font-family:var(--font-mono);font-size:12px;color:var(--color-faint,#7C75A8);margin-left:auto;}'
        . '.prt-ghr-relnotes{font-size:14.5px;line-height:1.55;color:var(--color-muted,#5A5676);margin:8px 0 0;}'
        . '.prt-ghr-readme{margin-top:6px;}.prt-ghr-readme h4{font-family:var(--font-display);font-weight:800;font-size:19px;color:var(--color-ink,#1B1830);margin:22px 0 8px;}.prt-ghr-readme h5{font-weight:700;font-size:16px;margin:18px 0 6px;}'
        . '.prt-ghr-readme pre{background:#1B1830;color:#EDEBFF;border-radius:12px;padding:16px;overflow:auto;font-size:13px;}.prt-ghr-readme code{font-family:var(--font-mono);}'
        . '.prt-ghr-readme table{border-collapse:collapse;width:100%;font-size:14px;}.prt-ghr-readme th,.prt-ghr-readme td{border:1px solid var(--color-line,#ECE4F8);padding:8px 10px;text-align:left;}'
        . "</style>\n";
}, 13);
