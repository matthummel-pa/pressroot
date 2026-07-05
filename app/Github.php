<?php

namespace App;

/**
 * Live, cached GitHub repo data for project case studies.
 * Mirrors the matthummel.com [prt_github] feature: repo metadata,
 * latest release, and a cleaned README intro — cached for 6 hours.
 */
class Github
{
    /** Shared request args (UA, Accept, optional auth token). */
    protected static function args(string $accept = 'application/vnd.github+json'): array
    {
        $h = ['User-Agent' => 'matthummel.com', 'Accept' => $accept];
        $token = function_exists('get_theme_mod') ? trim((string) get_theme_mod('prt_gh_token', '')) : '';
        if ($token !== '') {
            $h['Authorization'] = 'token ' . $token;
        }
        return ['timeout' => 12, 'headers' => $h];
    }

    /** Cache TTL in seconds (from the Projects setting). */
    protected static function ttl(): int
    {
        return max(1, (int) (function_exists('get_theme_mod') ? get_theme_mod('prt_proj_cache_hours', 6) : 6)) * HOUR_IN_SECONDS;
    }

    /** Fetch + cache a user/org profile. */
    public static function fetchUser(string $user): array
    {
        $key = 'prt_ghu_' . md5($user);
        if (($d = get_transient($key)) !== false) {
            return $d;
        }
        $d = [];
        $r = wp_remote_get("https://api.github.com/users/{$user}", self::args());
        if (! is_wp_error($r) && wp_remote_retrieve_response_code($r) === 200) {
            $j = json_decode(wp_remote_retrieve_body($r), true);
            $d = [
                'login'        => $j['login'] ?? $user,
                'name'         => $j['name'] ?? ($j['login'] ?? $user),
                'bio'          => $j['bio'] ?? '',
                'avatar'       => $j['avatar_url'] ?? '',
                'url'          => $j['html_url'] ?? '',
                'followers'    => (int) ($j['followers'] ?? 0),
                'following'    => (int) ($j['following'] ?? 0),
                'public_repos' => (int) ($j['public_repos'] ?? 0),
            ];
        }
        set_transient($key, $d, self::ttl());
        return $d;
    }

    /** Fetch + cache a user's repos (sorted, forks excluded). */
    public static function fetchRepos(string $user, int $count = 6, string $sort = 'updated'): array
    {
        $count = max(1, min(30, $count));
        $sort  = in_array($sort, ['updated', 'pushed', 'full_name', 'created'], true) ? $sort : 'updated';
        $key   = 'prt_ghr_' . md5($user . $sort . $count);
        if (($d = get_transient($key)) !== false) {
            return $d;
        }
        $out = [];
        $r = wp_remote_get("https://api.github.com/users/{$user}/repos?per_page={$count}&sort={$sort}", self::args());
        if (! is_wp_error($r) && wp_remote_retrieve_response_code($r) === 200) {
            foreach ((array) json_decode(wp_remote_retrieve_body($r), true) as $j) {
                if (! empty($j['fork'])) {
                    continue;
                }
                $out[] = [
                    'name'  => $j['name'] ?? '',
                    'full'  => $j['full_name'] ?? '',
                    'desc'  => $j['description'] ?? '',
                    'stars' => (int) ($j['stargazers_count'] ?? 0),
                    'forks' => (int) ($j['forks_count'] ?? 0),
                    'lang'  => $j['language'] ?? '',
                    'url'   => $j['html_url'] ?? '',
                ];
            }
        }
        set_transient($key, $out, self::ttl());
        return $out;
    }

    /** Fetch + cache recent releases for a repo. */
    public static function fetchReleases(string $owner, string $repo, int $count = 5): array
    {
        $count = max(1, min(20, $count));
        $key   = 'prt_ghrel_' . md5("{$owner}/{$repo}/{$count}");
        if (($d = get_transient($key)) !== false) {
            return $d;
        }
        $out = [];
        $r = wp_remote_get("https://api.github.com/repos/{$owner}/{$repo}/releases?per_page={$count}", self::args());
        if (! is_wp_error($r) && wp_remote_retrieve_response_code($r) === 200) {
            foreach ((array) json_decode(wp_remote_retrieve_body($r), true) as $j) {
                $out[] = [
                    'tag'        => $j['tag_name'] ?? '',
                    'name'       => ($j['name'] ?? '') ?: ($j['tag_name'] ?? ''),
                    'url'        => $j['html_url'] ?? '',
                    'date'       => isset($j['published_at']) ? date_i18n(get_option('date_format'), strtotime($j['published_at'])) : '',
                    'prerelease' => ! empty($j['prerelease']),
                    'body'       => (string) ($j['body'] ?? ''),
                ];
            }
        }
        set_transient($key, $out, self::ttl());
        return $out;
    }

    /**
     * Fetch + cache open issues for a repo (most recently updated first —
     * GitHub's default sort). Used by the Support tab (app/support-settings.php)
     * to give a theme owner a quick "what's open on the repo" glance without
     * leaving wp-admin.
     *
     * NOTE: GitHub's /issues endpoint also returns pull requests (a PR is a
     * kind of issue in their data model) — each is flagged with a
     * `pull_request` key that a plain issue doesn't have, so those are
     * filtered out here to keep this genuinely "issues only".
     */
    public static function fetchIssues(string $owner, string $repo, int $count = 5): array
    {
        $count = max(1, min(20, $count));
        $key   = 'prt_ghi_' . md5("{$owner}/{$repo}/{$count}");
        if (($d = get_transient($key)) !== false) {
            return $d;
        }
        $out = [];
        // Ask for a few more than needed since PRs will be filtered out of the results.
        $r = wp_remote_get("https://api.github.com/repos/{$owner}/{$repo}/issues?state=open&per_page=" . ($count * 2), self::args());
        if (! is_wp_error($r) && wp_remote_retrieve_response_code($r) === 200) {
            foreach ((array) json_decode(wp_remote_retrieve_body($r), true) as $j) {
                if (isset($j['pull_request'])) {
                    continue;
                }
                $out[] = [
                    'number'   => (int) ($j['number'] ?? 0),
                    'title'    => $j['title'] ?? '',
                    'url'      => $j['html_url'] ?? '',
                    'date'     => isset($j['created_at']) ? date_i18n(get_option('date_format'), strtotime($j['created_at'])) : '',
                    'comments' => (int) ($j['comments'] ?? 0),
                ];
                if (count($out) >= $count) {
                    break;
                }
            }
        }
        set_transient($key, $out, self::ttl());
        return $out;
    }

    /** Fetch + cache repo data. */
    public static function fetch(string $owner, string $repo): array
    {
        $key = 'prt_gh_' . md5($owner . '/' . $repo);

        if (($data = get_transient($key)) !== false) {
            return $data;
        }

        $data = [];
        $jargs = ['timeout' => 12, 'headers' => [
            'User-Agent' => 'matthummel.com',
            'Accept' => 'application/vnd.github+json',
        ]];
        $token = function_exists('get_theme_mod') ? trim((string) get_theme_mod('prt_gh_token', '')) : '';
        if ($token !== '') {
            $jargs['headers']['Authorization'] = 'token ' . $token;
        }

        $r = wp_remote_get("https://api.github.com/repos/{$owner}/{$repo}", $jargs);
        if (! is_wp_error($r) && wp_remote_retrieve_response_code($r) === 200) {
            $j = json_decode(wp_remote_retrieve_body($r), true);
            $data['desc']    = $j['description'] ?? '';
            $data['stars']   = (int) ($j['stargazers_count'] ?? 0);
            $data['forks']   = (int) ($j['forks_count'] ?? 0);
            $data['lang']    = $j['language'] ?? '';
            $data['license'] = (isset($j['license']['spdx_id']) && $j['license']['spdx_id'] !== 'NOASSERTION')
                ? $j['license']['spdx_id'] : '';
            $data['url']     = $j['html_url'] ?? '';
            $data['topics']  = array_values(array_filter((array) ($j['topics'] ?? []), 'is_string'));
            $data['branch']  = $j['default_branch'] ?? 'main';
            $data['owner']   = $owner;
            $data['repo']    = $repo;
            $data['homepage'] = $j['homepage'] ?? '';
            $data['open_issues'] = (int) ($j['open_issues_count'] ?? 0);
        }

        $rel = wp_remote_get("https://api.github.com/repos/{$owner}/{$repo}/releases/latest", $jargs);
        if (! is_wp_error($rel) && wp_remote_retrieve_response_code($rel) === 200) {
            $jr = json_decode(wp_remote_retrieve_body($rel), true);
            $data['release'] = $jr['tag_name'] ?? '';
        }

        // Languages breakdown (bytes per language → percentages on render).
        $lg = wp_remote_get("https://api.github.com/repos/{$owner}/{$repo}/languages", $jargs);
        if (! is_wp_error($lg) && wp_remote_retrieve_response_code($lg) === 200) {
            $langs = json_decode(wp_remote_retrieve_body($lg), true);
            $data['languages'] = is_array($langs) ? $langs : [];
        }

        // Changelog: prefer a CHANGELOG file, else fall back to the releases page.
        $branch = $data['branch'] ?? 'main';
        $data['changelog'] = ($data['url'] ?? "https://github.com/{$owner}/{$repo}") . '/releases';
        foreach (['CHANGELOG.md', 'CHANGELOG', 'changelog.md', 'docs/CHANGELOG.md'] as $cl) {
            $c = wp_remote_get("https://api.github.com/repos/{$owner}/{$repo}/contents/" . $cl, $jargs);
            if (! is_wp_error($c) && wp_remote_retrieve_response_code($c) === 200) {
                $cj = json_decode(wp_remote_retrieve_body($c), true);
                $data['changelog'] = $cj['html_url'] ?? ($data['url'] . '/blob/' . $branch . '/' . $cl);
                break;
            }
        }

        $rmHeaders = ['User-Agent' => 'matthummel.com', 'Accept' => 'application/vnd.github.html'];
        if ($token !== '') {
            $rmHeaders['Authorization'] = 'token ' . $token;
        }
        $rm = wp_remote_get("https://api.github.com/repos/{$owner}/{$repo}/readme", ['timeout' => 12, 'headers' => $rmHeaders]);
        if (! is_wp_error($rm) && wp_remote_retrieve_response_code($rm) === 200) {
            $rmBody = wp_remote_retrieve_body($rm);
            $data['intro']  = self::readmeIntro($rmBody);
            $data['readme'] = self::readmeClean($rmBody);
        }

        $ttl = max(1, (int) (function_exists('get_theme_mod') ? get_theme_mod('prt_proj_cache_hours', 6) : 6));
        set_transient($key, $data, $ttl * HOUR_IN_SECONDS);

        return $data;
    }

    /** Extract a clean README intro: up to the 2nd <h2>, headings demoted, badges/anchors stripped. */
    protected static function readmeIntro(string $body): string
    {
        $p1 = stripos($body, '<h2');
        $cut = strlen($body);
        if ($p1 !== false) {
            $p2 = stripos($body, '<h2', $p1 + 3);
            $cut = ($p2 !== false) ? $p2 : strlen($body);
        }
        $intro = substr($body, 0, $cut);

        if (($h1 = stripos($intro, '</h1>')) !== false) {
            $intro = substr($intro, $h1 + 5);
        }

        $intro = str_ireplace(['<h2', '</h2>'], ['<h3', '</h3>'], $intro);
        $intro = preg_replace('#<img[^>]*>#i', '', $intro);
        $intro = preg_replace('~<svg[^>]*>.*?</svg>~is', '', $intro);
        $intro = preg_replace('~<a[^>]*href="#[^"]*"[^>]*>.*?</a>~is', '', $intro);

        return (string) $intro;
    }

    /** Clean the FULL README html for on-page display. */
    protected static function readmeClean(string $body): string
    {
        if (($h1 = stripos($body, '</h1>')) !== false) {
            $body = substr($body, $h1 + 5);
        }
        $body = str_ireplace(['<h1', '</h1>', '<h2', '</h2>', '<h3', '</h3>'], ['<h4', '</h4>', '<h4', '</h4>', '<h5', '</h5>'], $body);
        $body = preg_replace('#<img[^>]*(shields\.io|badge|/actions/|/workflows/)[^>]*>#i', '', $body);
        $body = preg_replace('~<svg[^>]*>.*?</svg>~is', '', $body);
        $body = preg_replace('~<a[^>]*href="#[^"]*"[^>]*>(.*?)</a>~is', '$1', $body);
        $body = preg_replace('~<a[^>]*>\s*</a>~i', '', $body); // badges stripped above leave empty links
        return (string) $body;
    }

    /** GitHub language → dot colour. */
    public static function langColor(string $lang): string
    {
        $c = [
            'PHP' => '#4F5D95', 'JavaScript' => '#f1e05a', 'TypeScript' => '#3178c6', 'CSS' => '#563d7c',
            'SCSS' => '#c6538c', 'HTML' => '#e34c26', 'Python' => '#3572A5', 'Go' => '#00ADD8',
            'Rust' => '#dea584', 'Shell' => '#89e051', 'Java' => '#b07219', 'Ruby' => '#701516',
            'Blade' => '#f7523f', 'Vue' => '#41b883', 'C' => '#555555', 'C++' => '#f34b7d',
            'C#' => '#178600', 'Dockerfile' => '#384d54', 'MDX' => '#fcb32c', 'Astro' => '#ff5a03',
        ];
        return $c[$lang] ?? '#8b949e';
    }

    /**
     * Full repo profile: tags/topics, stats, languages breakdown, version notes
     * (releases + changelog link), and the README — all live + cached.
     */
    public static function renderRepo(string $owner, string $repo, array $opts = []): string
    {
        $o = array_merge(['topics' => true, 'stats' => true, 'languages' => true, 'releases' => true, 'readme' => true, 'releaseCount' => 3], $opts);
        $d = self::fetch($owner, $repo);
        if (empty($d) || empty($d['url'])) {
            return '';
        }

        $out  = '<div class="prt-gh-repo">';
        $out .= '<div class="prt-ghr-head"><h2 class="prt-ghr-titlewrap"><a class="prt-ghr-title" href="' . esc_url($d['url']) . '" target="_blank" rel="noopener">' . esc_html($owner) . ' / <strong>' . esc_html($repo) . '</strong> &#8599;</a></h2>';
        if (! empty($d['desc'])) {
            $out .= '<p class="prt-ghr-desc">' . esc_html($d['desc']) . '</p>';
        }
        $out .= '</div>';

        if ($o['topics'] && ! empty($d['topics'])) {
            $out .= '<div class="prt-ghr-topics">';
            foreach ($d['topics'] as $t) {
                $out .= '<span class="prt-ghr-topic">' . esc_html($t) . '</span>';
            }
            $out .= '</div>';
        }

        if ($o['stats']) {
            $bits = [
                '<span class="prt-ghr-stat">&#9733; ' . number_format((int) ($d['stars'] ?? 0)) . ' <em>stars</em></span>',
                '<span class="prt-ghr-stat">&#11489; ' . number_format((int) ($d['forks'] ?? 0)) . ' <em>forks</em></span>',
            ];
            if (! empty($d['license'])) {
                $bits[] = '<span class="prt-ghr-stat">' . esc_html($d['license']) . ' <em>license</em></span>';
            }
            if (! empty($d['release'])) {
                $bits[] = '<span class="prt-ghr-stat">' . esc_html($d['release']) . ' <em>latest</em></span>';
            }
            $out .= '<div class="prt-ghr-stats">' . implode('', $bits) . '</div>';
        }

        if ($o['languages'] && ! empty($d['languages'])) {
            $langs = $d['languages'];
            arsort($langs);
            $total = array_sum($langs) ?: 1;
            $bar = '';
            $list = '';
            foreach ($langs as $lang => $bytes) {
                $pct   = round($bytes / $total * 100, 1);
                $color = self::langColor((string) $lang);
                $bar  .= '<span class="prt-ghr-langseg" style="width:' . $pct . '%;background:' . esc_attr($color) . '" title="' . esc_attr($lang . ' ' . $pct . '%') . '"></span>';
                $list .= '<span class="prt-ghr-langitem"><span class="prt-ghr-langdot" style="background:' . esc_attr($color) . '"></span>' . esc_html((string) $lang) . ' <em>' . $pct . '%</em></span>';
            }
            $out .= '<div class="prt-ghr-section"><h3 class="prt-ghr-h">Languages used</h3><div class="prt-ghr-langbar">' . $bar . '</div><div class="prt-ghr-langlist">' . $list . '</div></div>';
        }

        if ($o['releases']) {
            $rels = self::fetchReleases($owner, $repo, (int) $o['releaseCount']);
            if ($rels) {
                $out .= '<div class="prt-ghr-section"><div class="prt-ghr-sechead"><h3 class="prt-ghr-h">Version notes</h3>';
                if (! empty($d['changelog'])) {
                    $out .= '<a class="prt-ghr-changelog" href="' . esc_url($d['changelog']) . '" target="_blank" rel="noopener">Full changelog &#8599;</a>';
                }
                $out .= '</div><ul class="prt-ghr-releases">';
                foreach ($rels as $r) {
                    $out .= '<li class="prt-ghr-rel"><div class="prt-ghr-relhead"><a href="' . esc_url($r['url']) . '" target="_blank" rel="noopener"><strong>' . esc_html($r['name']) . '</strong></a>';
                    if (! empty($r['prerelease'])) {
                        $out .= ' <span class="prt-ghr-pre">pre-release</span>';
                    }
                    if (! empty($r['date'])) {
                        $out .= ' <span class="prt-ghr-reldate">' . esc_html($r['date']) . '</span>';
                    }
                    $out .= '</div>';
                    if (! empty($r['body'])) {
                        $out .= '<p class="prt-ghr-relnotes">' . esc_html(wp_trim_words(wp_strip_all_tags($r['body']), 45)) . '</p>';
                    }
                    $out .= '</li>';
                }
                $out .= '</ul></div>';
            }
        }

        if ($o['readme'] && ! empty($d['readme'])) {
            $allowed = [
                'p' => [], 'a' => ['href' => [], 'rel' => [], 'title' => []], 'strong' => [], 'b' => [], 'em' => [], 'i' => [],
                'code' => [], 'pre' => [], 'ul' => [], 'ol' => [], 'li' => [], 'br' => [], 'hr' => [],
                'h4' => [], 'h5' => [], 'h6' => [], 'blockquote' => [], 'table' => [], 'thead' => [], 'tbody' => [],
                'tr' => [], 'th' => [], 'td' => [], 'img' => ['src' => [], 'alt' => []],
            ];
            $out .= '<div class="prt-ghr-section"><h3 class="prt-ghr-h">Readme</h3><div class="prt-ghr-readme readme-prose">' . wp_kses((string) $d['readme'], $allowed) . '</div></div>';
        }

        return $out . '</div>';
    }

    /** Render selected parts (desc, stats, intro) as HTML. */
    public static function render(string $owner, string $repo, array $show = ['stats', 'intro']): string
    {
        $d = self::fetch($owner, $repo);
        if (empty($d)) {
            return '';
        }

        $out = '<div class="prt-gh">';

        if (in_array('desc', $show, true) && ! empty($d['desc'])) {
            $out .= '<p class="lead">' . esc_html($d['desc']) . '</p>';
        }

        if (in_array('stats', $show, true)) {
            $items = [];
            if (isset($d['stars']))    $items[] = '<li><strong>' . number_format($d['stars']) . '</strong><span>Stars</span></li>';
            if (isset($d['forks']))    $items[] = '<li><strong>' . number_format($d['forks']) . '</strong><span>Forks</span></li>';
            if (! empty($d['lang']))   $items[] = '<li><strong>' . esc_html($d['lang']) . '</strong><span>Language</span></li>';
            if (! empty($d['license']))$items[] = '<li><strong>' . esc_html($d['license']) . '</strong><span>License</span></li>';
            if (! empty($d['release']))$items[] = '<li><strong>' . esc_html($d['release']) . '</strong><span>Release</span></li>';
            if ($items) $out .= '<ul class="stat-grid">' . implode('', $items) . '</ul>';
        }

        if (in_array('intro', $show, true) && ! empty($d['intro'])) {
            $allowed = [
                'p' => [], 'a' => ['href' => [], 'rel' => [], 'title' => []], 'strong' => [], 'em' => [],
                'code' => [], 'pre' => [], 'ul' => [], 'ol' => [], 'li' => [], 'br' => [],
                'h3' => [], 'h4' => [], 'blockquote' => [],
            ];
            $out .= '<div class="readme-prose">' . wp_kses($d['intro'], $allowed) . '</div>';
        }

        return $out . '</div>';
    }
}
