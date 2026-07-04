<?php

/**
 * Theme filters.
 *
 * Small, unrelated WordPress filter/shortcode tweaks that don't warrant their
 * own file. Keep additions here narrow and self-contained — anything that
 * grows its own settings UI or multiple hooks (e.g. GitHub data, layout)
 * should get its own app/*.php file instead.
 */

namespace App;

/**
 * Replace the default "[…]" excerpt suffix with a real "… Continued" link
 * back to the post, since the stock ellipsis gives readers no way to click
 * through from an excerpt-only listing.
 *
 * @return string
 */
add_filter('excerpt_more', function () {
    return sprintf(' &hellip; <a href="%s">%s</a>', get_permalink(), __('Continued', 'sage'));
});

/**
 * [prt_github owner="" repo="" show="desc,stats,intro"] — live cached GitHub repo data.
 *
 * Lets any post/page embed a repo card without hand-writing markup. Delegates
 * to \App\Github::render() (app/Github.php) which owns the actual GitHub API
 * call + caching; this shortcode is just the content-editor-facing surface.
 * Silently renders nothing if owner/repo are missing, rather than erroring,
 * since a malformed shortcode shouldn't break the rest of the page.
 */
add_shortcode('prt_github', function ($atts) {
    $a = shortcode_atts(['owner' => '', 'repo' => '', 'show' => 'stats,intro'], $atts);

    if (! $a['owner'] || ! $a['repo']) {
        return '';
    }

    return \App\Github::render($a['owner'], $a['repo'], array_map('trim', explode(',', $a['show'])));
});
