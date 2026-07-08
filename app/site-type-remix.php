<?php

namespace App;

/**
 * Site-type REMIX engine — turns the Site Types tool from "toggle between
 * two hand-built pages" into a design generator that deals a whole new
 * THEME on every refresh:
 *
 * 1. REMIX PATTERNS (variants C + D for every page of every site type) —
 *    composed programmatically from a pool of hero / feature / CTA section
 *    builders in the Repofolio design language. Which sections a given
 *    page's C/D variant uses is seeded from the type+role, so all ~50
 *    generated patterns genuinely differ. Combined with the hand-built A/B
 *    variants, every page now has FOUR designs to deal between.
 *
 * 2. DESIGN KITS PER REFRESH — each site type maps to a POOL of style kits
 *    (see the new Repofolio-family kits in app/settings-io.php). Applying
 *    or bulk-refreshing a site type applies a random kit from the pool —
 *    different from the one currently active — so the SITE-WIDE design
 *    (palette, fonts, radii) regenerates too, not just page content. Kits
 *    are pure theme-mod/CSS-variable swaps: instant, no build step.
 *
 * 3. BRAND PROFILE — a short questionnaire on the Site Types tab (business
 *    name, one-line description, brand color, light/dark, vibe). Answers
 *    steer generation: the vibe + light/dark answers filter which kits can
 *    be dealt, the brand color overrides the kit's action color after
 *    apply, and the AI copy prompt picks the profile up automatically
 *    (see prt_ai_build_hero_prompt in app/ai-connectors.php).
 *
 * Loaded after the site-type-*.php pattern files (see functions.php).
 */

/* ─────────────────────────── Brand profile ─────────────────────────── */

/** The saved brand questionnaire answers, with safe defaults. */
function prt_brand_profile(): array
{
    return [
        'name'  => (string) get_theme_mod('prt_brand_name', ''),
        'desc'  => (string) get_theme_mod('prt_brand_desc', ''),
        'color' => (string) get_theme_mod('prt_brand_color', ''),
        'mode'  => (string) get_theme_mod('prt_brand_mode', 'either'),   // light | dark | either
        'vibe'  => (string) get_theme_mod('prt_brand_vibe', 'any'),      // bold | minimal | warm | playful | any
    ];
}

/** Save handler for the questionnaire (form rendered by prt_brand_tab_html() below — the "Brand" tab on Appearance -> Pressroot). */
add_action('admin_post_prt_save_brand_profile', function () {
    if (! current_user_can('edit_theme_options') || ! check_admin_referer('prt_save_brand_profile')) {
        wp_die(__('Not allowed.', 'pressroot'));
    }
    set_theme_mod('prt_brand_name', sanitize_text_field(wp_unslash($_POST['prt_brand_name'] ?? '')));
    set_theme_mod('prt_brand_desc', sanitize_text_field(wp_unslash($_POST['prt_brand_desc'] ?? '')));
    $color = sanitize_hex_color(wp_unslash($_POST['prt_brand_color'] ?? ''));
    set_theme_mod('prt_brand_color', $color ?: '');
    $mode = sanitize_key($_POST['prt_brand_mode'] ?? 'either');
    set_theme_mod('prt_brand_mode', in_array($mode, ['light', 'dark', 'either'], true) ? $mode : 'either');
    $vibe = sanitize_key($_POST['prt_brand_vibe'] ?? 'any');
    set_theme_mod('prt_brand_vibe', in_array($vibe, ['bold', 'minimal', 'warm', 'playful', 'any'], true) ? $vibe : 'any');
    wp_safe_redirect(prt_settings_tab_url('brand', ['prt_brand_saved' => '1']));
    exit;
});

/* ──────────────────── Kit pools + random kit dealing ─────────────────── */

/** Which style kits each site type may be dealt. First entry = the classic default. */
function prt_site_type_kit_pools(): array
{
    return apply_filters('matthummel/site_type_kit_pools', [
        'agency'     => ['paper_space', 'iris_dark', 'pink_pop', 'cyan_sky'],
        'freelance'  => ['editorial', 'coral_cream', 'mint_fresh', 'paper_space'],
        'saas'       => ['midnight', 'iris_dark', 'cyan_sky', 'mono_slate'],
        'blog'       => ['warm_sand', 'amber_toast', 'coral_cream', 'editorial'],
        'marketing'  => ['mono_slate', 'pink_pop', 'cyan_sky', 'paper_space'],
        'affiliate'  => ['paper_space', 'pink_pop', 'amber_toast', 'mint_fresh'],
        'restaurant' => ['warm_sand', 'coral_cream', 'amber_toast', 'iris_dark'],
        'realty'     => ['editorial', 'cyan_sky', 'mono_slate', 'paper_space'],
    ]);
}

/**
 * Deal a random style kit for a site type — never the one dealt last time,
 * filtered by the brand profile's light/dark + vibe answers when they
 * narrow the field, with the brand color applied on top afterwards.
 * Returns the applied kit slug.
 */
function prt_apply_random_site_kit(string $typeId, array $type): string
{
    $kits    = prt_style_kits();
    $pool    = prt_site_type_kit_pools()[$typeId] ?? [$type['kit'] ?? 'paper_space'];
    $pool    = array_values(array_filter($pool, fn ($slug) => isset($kits[$slug])));
    $brand   = prt_brand_profile();

    // Brand answers narrow the pool (only when the filter leaves options).
    if ($brand['mode'] !== 'either') {
        $filtered = array_values(array_filter($pool, fn ($slug) => ($kits[$slug]['mode'] ?? 'light') === $brand['mode']));
        if ($filtered) {
            $pool = $filtered;
        }
    }
    if ($brand['vibe'] !== 'any') {
        $filtered = array_values(array_filter($pool, fn ($slug) => in_array($brand['vibe'], $kits[$slug]['vibes'] ?? [], true)));
        if ($filtered) {
            $pool = $filtered;
        }
    }

    $current = (string) get_option('prt_active_site_kit', '');
    $choices = array_values(array_diff($pool, [$current])) ?: $pool;
    $slug    = $choices ? $choices[array_rand($choices)] : ($type['kit'] ?? 'paper_space');

    prt_apply_style_kit($slug);
    update_option('prt_active_site_kit', $slug);

    // Brand color wins over the kit's action color — the one non-negotiable
    // piece of the owner's branding, applied after every re-deal.
    if ($brand['color'] !== '') {
        set_theme_mod('prt_color_action', $brand['color']);
    }

    return $slug;
}

/* ─────────────── Remix pattern generation (variants C + D) ─────────────── */

/**
 * Extend every site type page definition with the generated C/D variant
 * slugs, so prt_site_type_page_variants() (app/ai-assistant.php) finds four
 * designs per page instead of two. Runs through the existing site_types
 * filter — no site-type file needs editing to participate.
 */
add_filter('matthummel/site_types', function (array $types): array {
    foreach ($types as $typeId => &$type) {
        foreach ($type['pages'] as &$page) {
            $page['pattern_c'] = 'prt-site/' . $typeId . '-' . $page['role'] . '-c';
            $page['pattern_d'] = 'prt-site/' . $typeId . '-' . $page['role'] . '-d';
        }
    }
    return $types;
}, 20);

add_action('init', function () {

    $GRAD = 'linear-gradient(135deg,#6C4CF1 0%,#FF4D9D 55%,#FF7A3D 100%)';
    $SPEC = 'linear-gradient(90deg,#6C4CF1 0%,#FF4D9D 28%,#FF7A3D 52%,#FFC53D 72%,#37E29A 88%,#22CFEE 100%)';

    $wrap = function (string $inner, string $pt = '56px', string $pb = '24px'): string {
        $style = '{"spacing":{"padding":{"top":"' . $pt . '","bottom":"' . $pb . '"}}}';
        return '<!-- wp:group {"className":"prt-wrap","style":' . $style . ',"layout":{"type":"constrained","contentSize":"1240px"}} -->'
            . '<div class="wp-block-group prt-wrap" style="padding-top:' . $pt . ';padding-bottom:' . $pb . '">'
            . "<!-- wp:html -->\n" . $inner . "\n<!-- /wp:html -->"
            . '</div><!-- /wp:group -->' . "\n\n";
    };

    /* Hero builders — each takes (title, label) and returns markup. */
    $heroes = [
        // 0: light centered, gradient keyword
        function (string $t, string $l) use ($GRAD): string {
            return '<section style="text-align:center; padding:26px 0 8px;">'
                . '<div style="font-family:var(--font-mono); letter-spacing:2px; text-transform:uppercase; font-size:13px; color:#6C4CF1; margin-bottom:14px;">' . esc_html($l) . '</div>'
                . '<h1 style="font-family:var(--font-display); font-weight:800; font-size:clamp(40px,5.5vw,66px); line-height:1.02; letter-spacing:-.03em; margin:0 0 16px; color:var(--color-ink,#17151F);">' . esc_html($t) . ', <span style="background:' . $GRAD . '; -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; color:transparent;">reimagined</span>.</h1>'
                . '<p style="font-size:18px; color:var(--color-body,#4A4660); max-width:36em; margin:0 auto;">Fresh from the design generator — swap this line for your story, or let the AI copy tool write it for you.</p>'
                . '</section>';
        },
        // 1: dark radial, left aligned
        function (string $t, string $l) use ($GRAD): string {
            return '<section style="position:relative; overflow:hidden; border-radius:28px; color:#fff; padding:58px 42px; background:radial-gradient(900px 400px at 80% -10%, rgba(108,76,241,.35), transparent 60%), radial-gradient(700px 400px at 10% 10%, rgba(255,77,157,.22), transparent 55%), linear-gradient(180deg,#201B3A,#15122a);">'
                . '<div style="font-family:var(--font-mono); letter-spacing:2px; text-transform:uppercase; font-size:13px; color:#b9a7ff;">' . esc_html($l) . '</div>'
                . '<h1 style="font-family:var(--font-display); font-weight:800; font-size:clamp(38px,5vw,60px); line-height:1.03; letter-spacing:-.03em; margin:12px 0 12px; background:linear-gradient(90deg,#C9B8FF,#FF9DC4,#FFC08A); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; color:transparent;">' . esc_html($t) . ' that earns its keep.</h1>'
                . '<p style="font-size:18px; color:#e2ddf5; max-width:34em; margin:0 0 24px;">Dealt fresh by the design generator. Every refresh reshuffles the layout, palette, and copy angle.</p>'
                . '<a href="#" class="prt-lift" style="text-decoration:none; display:inline-block; background:' . $GRAD . '; color:#fff; padding:15px 28px; border-radius:999px; font-weight:700; font-family:var(--font-display);">Get started</a>'
                . '</section>';
        },
        // 2: gradient band
        function (string $t, string $l) use ($GRAD): string {
            return '<section class="prt-spec-card" style="background:' . $GRAD . '; color:#fff; border-radius:28px; padding:56px 44px; text-align:center;">'
                . '<div style="font-family:var(--font-mono); letter-spacing:2px; text-transform:uppercase; font-size:12px; opacity:.85; margin-bottom:12px;">' . esc_html($l) . '</div>'
                . '<h1 style="font-family:var(--font-display); font-weight:800; font-size:clamp(36px,5vw,58px); letter-spacing:-.02em; margin:0 0 14px;">' . esc_html($t) . ' without the busywork.</h1>'
                . '<a href="#" class="prt-lift" style="text-decoration:none; display:inline-block; background:#fff; color:#6C4CF1; padding:15px 32px; border-radius:999px; font-weight:800; font-family:var(--font-display); box-shadow:0 10px 26px rgba(23,21,31,.22);">See how</a>'
                . '</section>';
        },
        // 3: split light + spectrum panel
        function (string $t, string $l) use ($SPEC): string {
            return '<section style="display:grid; grid-template-columns:1.1fr .9fr; gap:28px; align-items:center;">'
                . '<div>'
                . '<div style="font-family:var(--font-mono); letter-spacing:2px; text-transform:uppercase; font-size:13px; color:#6C4CF1; margin-bottom:12px;">' . esc_html($l) . '</div>'
                . '<h1 style="font-family:var(--font-display); font-weight:800; font-size:clamp(36px,4.5vw,56px); line-height:1.04; letter-spacing:-.03em; margin:0 0 14px; color:var(--color-ink,#17151F);">The ' . esc_html(strtolower($t)) . ' page people actually read.</h1>'
                . '<p style="font-size:17px; color:var(--color-body,#4A4660); margin:0;">Another spin of the generator — same brand system, brand-new bones.</p>'
                . '</div>'
                . '<div style="aspect-ratio:4/3; border-radius:22px; background:' . $SPEC . '; opacity:.9;"></div>'
                . '</section>';
        },
    ];

    /* Feature-section builders — (title) => markup. */
    $features = [
        // 0: three spectrum cards
        function (string $t): string {
            $out = '<div style="display:grid; grid-template-columns:repeat(3,1fr); gap:18px;">';
            foreach ([['◆', '#6C4CF1', 'Made to fit'], ['▤', '#FF4D9D', 'Ready today'], ['⚡', '#FF7A3D', 'Fast by default']] as $c) {
                $out .= '<div class="prt-spec-card" style="background:#fff; border:1.5px solid #ECE6FB; border-radius:18px; padding:26px;">'
                    . '<div style="width:34px; height:34px; border-radius:10px; background:' . $c[1] . '; color:#fff; display:grid; place-items:center; font-weight:800;">' . $c[0] . '</div>'
                    . '<h3 style="font-family:var(--font-display); font-weight:800; font-size:19px; margin:12px 0 6px; color:#17151F;">' . esc_html($c[2]) . '</h3>'
                    . '<p style="font-size:14px; color:#5A5676; line-height:1.55; margin:0;">Placeholder copy for the ' . esc_html(strtolower($t)) . ' page — replace with a specific, provable claim.</p></div>';
            }
            return $out . '</div>';
        },
        // 1: list rows
        function (string $t): string {
            $out = '<div style="display:grid; gap:14px; max-width:820px; margin:0 auto;">';
            foreach ([['01', 'Start with the essentials'], ['02', 'Layer in the details'], ['03', 'Ship it and iterate']] as $r) {
                $out .= '<div class="prt-spec-card" style="background:#fff; border:1.5px solid #ECE6FB; border-radius:18px; padding:22px 26px; display:flex; gap:18px; align-items:baseline;">'
                    . '<span style="font-family:var(--font-mono); font-weight:600; font-size:13px; color:#FF4D9D;">' . esc_html($r[0]) . '</span>'
                    . '<div><h3 style="font-family:var(--font-display); font-weight:800; font-size:18px; margin:0 0 4px; color:#17151F;">' . esc_html($r[1]) . '</h3>'
                    . '<p style="font-size:14px; color:#5A5676; margin:0;">One sentence about how ' . esc_html(strtolower($t)) . ' works here.</p></div></div>';
            }
            return $out . '</div>';
        },
        // 2: stat band
        function (string $t): string {
            $out = '<div style="display:grid; grid-template-columns:repeat(4,1fr); gap:16px;">';
            foreach ([['#6C4CF1', '10×'], ['#FF4D9D', '24/7'], ['#37E29A', '99.9%'], ['#FFC53D', '5★']] as $s) {
                $out .= '<div class="prt-spec-card" style="background:#fff; border:1.5px solid #ECE6FB; border-radius:18px; padding:22px; text-align:center;">'
                    . '<div style="font-family:var(--font-display); font-weight:900; font-size:30px; color:' . $s[0] . ';">' . esc_html($s[1]) . '</div>'
                    . '<div style="font-size:12.5px; color:#5A5676; margin-top:4px;">a stat about ' . esc_html(strtolower($t)) . '</div></div>';
            }
            return $out . '</div>';
        },
        // 3: two-column feature + dark aside
        function (string $t): string {
            return '<div style="display:grid; grid-template-columns:1.2fr .8fr; gap:18px;">'
                . '<div class="prt-spec-card" style="background:#fff; border:1.5px solid #ECE6FB; border-radius:18px; padding:30px;">'
                . '<h3 style="font-family:var(--font-display); font-weight:800; font-size:22px; margin:0 0 10px; color:#17151F;">Why it works</h3>'
                . '<p style="font-size:15px; color:#4A4660; line-height:1.6; margin:0;">Two paragraphs of honest explanation beat ten feature bullets. This block is where the ' . esc_html(strtolower($t)) . ' page earns trust — swap in your specifics.</p></div>'
                . '<div class="prt-spec-card" style="background:#17151F; color:#fff; border-radius:18px; padding:30px;">'
                . '<div style="font-family:var(--font-mono); font-size:12px; color:#37E29A; letter-spacing:.1em; margin-bottom:8px;">GOOD TO KNOW</div>'
                . '<p style="font-size:14.5px; color:#CFCBE6; line-height:1.6; margin:0;">A short aside, guarantee, or proof point that deserves its own dark card.</p></div>'
                . '</div>';
        },
    ];

    /* CTA builders. */
    $ctas = [
        function () use ($GRAD): string {
            return '<div class="prt-spec-card" style="background:' . $GRAD . '; color:#fff; border-radius:26px; padding:46px 40px; text-align:center;">'
                . '<h2 style="font-family:var(--font-display); font-weight:800; font-size:clamp(26px,3.5vw,40px); letter-spacing:-.02em; margin:0 0 14px;">Ready when you are.</h2>'
                . '<a href="#" class="prt-lift" style="text-decoration:none; display:inline-block; background:#fff; color:#6C4CF1; padding:15px 32px; border-radius:999px; font-weight:800; font-family:var(--font-display);">Get in touch</a></div>';
        },
        function () use ($GRAD): string {
            return '<div class="prt-spec-card" style="background:#17151F; color:#fff; border-radius:26px; padding:44px 40px; display:flex; align-items:center; justify-content:space-between; gap:22px; flex-wrap:wrap;">'
                . '<h2 style="font-family:var(--font-display); font-weight:800; font-size:clamp(22px,3vw,32px); letter-spacing:-.02em; margin:0;">One conversation. Zero pressure.</h2>'
                . '<a href="#" class="prt-lift" style="text-decoration:none; display:inline-block; background:' . $GRAD . '; color:#fff; padding:14px 28px; border-radius:999px; font-weight:700; font-family:var(--font-display);">Say hello</a></div>';
        },
        function () use ($GRAD): string {
            return '<div class="prt-spec-card" style="background:#fff; border:1.5px solid #ECE6FB; border-radius:26px; padding:40px; text-align:center;">'
                . '<h2 style="font-family:var(--font-display); font-weight:800; font-size:clamp(24px,3vw,34px); letter-spacing:-.02em; margin:0 0 8px; color:#17151F;">Questions? Good.</h2>'
                . '<p style="font-size:15px; color:#5A5676; margin:0 0 18px;">The best projects start with a few of them.</p>'
                . '<a href="#" class="prt-lift" style="text-decoration:none; display:inline-block; background:' . $GRAD . '; color:#fff; padding:14px 30px; border-radius:999px; font-weight:700; font-family:var(--font-display);">Start the conversation</a></div>';
        },
    ];

    // Compose + register C and D for every page of every site type. Section
    // choices are seeded per type+role+variant, so each of the generated
    // patterns is a different hero/feature/CTA combination.
    foreach (prt_site_types() as $typeId => $type) {
        foreach ($type['pages'] as $page) {
            foreach (['c', 'd'] as $variant) {
                $seed = crc32($typeId . '|' . $page['role'] . '|' . $variant);
                $h    = $heroes[$seed % count($heroes)];
                $f    = $features[intdiv($seed, 7) % count($features)];
                $c    = $ctas[intdiv($seed, 31) % count($ctas)];

                register_block_pattern('prt-site/' . $typeId . '-' . $page['role'] . '-' . $variant, [
                    'title'      => sprintf(
                        /* translators: 1: site type label, 2: page title, 3: variant letter */
                        __('%1$s %2$s — remix %3$s (generated)', 'pressroot'),
                        $type['label'],
                        $page['title'],
                        strtoupper($variant)
                    ),
                    'categories' => ['prt-site-types'],
                    'blockTypes' => ['core/post-content'],
                    'content'    => $wrap($h($page['title'], $type['label']), '48px', '0px')
                        . $wrap($f($page['title']), '8px', '0px')
                        . $wrap($c(), '8px', '64px'),
                ]);
            }
        }
    }
}, 14);

/* ───────────────────────── Brand tab (settings) ───────────────────────── */

/**
 * "Brand" tab on Appearance -> Pressroot (registered in
 * app/pressroot-settings.php) — the questionnaire whose answers steer every
 * design the Site Types generator deals: kits are filtered by light/dark +
 * vibe, the brand color overrides each dealt kit's action color, and the AI
 * copy prompt reads the whole profile.
 */
function prt_brand_tab_html(): void
{
    if (! current_user_can('edit_theme_options')) {
        return;
    }
    $b = prt_brand_profile();
    if (isset($_GET['prt_brand_saved'])) {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Brand profile saved — every design refresh now follows it.', 'pressroot') . '</p></div>';
    }
    ?>
    <h2 style="margin-top:0"><?php esc_html_e('Brand', 'pressroot'); ?></h2>
    <p class="description" style="max-width:640px"><?php esc_html_e('Answer a few questions about the business and the design generator builds around them: color, light-or-dark, and personality shape every theme it deals on the Site Types tab, and the AI copywriter matches the voice.', 'pressroot'); ?></p>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="max-width:640px;margin-top:18px">
        <input type="hidden" name="action" value="prt_save_brand_profile">
        <?php wp_nonce_field('prt_save_brand_profile'); ?>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><label for="prt_brand_name"><?php esc_html_e('Business name', 'pressroot'); ?></label></th>
                <td><input type="text" id="prt_brand_name" name="prt_brand_name" class="regular-text" value="<?php echo esc_attr($b['name']); ?>" placeholder="<?php esc_attr_e('e.g. Hummel & Co.', 'pressroot'); ?>"></td>
            </tr>
            <tr>
                <th scope="row"><label for="prt_brand_desc"><?php esc_html_e('What do you do?', 'pressroot'); ?></label></th>
                <td>
                    <input type="text" id="prt_brand_desc" name="prt_brand_desc" class="large-text" value="<?php echo esc_attr($b['desc']); ?>" placeholder="<?php esc_attr_e('One sentence — who you help and how', 'pressroot'); ?>">
                    <p class="description"><?php esc_html_e('Pre-fills the AI copy generator on the Site Types tab.', 'pressroot'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="prt_brand_color"><?php esc_html_e('Brand color', 'pressroot'); ?></label></th>
                <td>
                    <input type="color" id="prt_brand_color" name="prt_brand_color" value="<?php echo esc_attr($b['color'] ?: '#6C4CF1'); ?>" style="width:60px;height:36px;padding:2px">
                    <p class="description"><?php esc_html_e('Overrides the accent color of every design the generator deals. Leave at the default iris to let each kit keep its own.', 'pressroot'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Light or dark?', 'pressroot'); ?></th>
                <td>
                    <?php foreach (['light' => __('Light', 'pressroot'), 'dark' => __('Dark', 'pressroot'), 'either' => __('Surprise me', 'pressroot')] as $val => $label) : ?>
                        <label style="margin-right:16px"><input type="radio" name="prt_brand_mode" value="<?php echo esc_attr($val); ?>" <?php checked($b['mode'], $val); ?>> <?php echo esc_html($label); ?></label>
                    <?php endforeach; ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Personality', 'pressroot'); ?></th>
                <td>
                    <select name="prt_brand_vibe">
                        <?php foreach (['any' => __('Anything goes', 'pressroot'), 'bold' => __('Bold & confident', 'pressroot'), 'minimal' => __('Minimal & sharp', 'pressroot'), 'warm' => __('Warm & inviting', 'pressroot'), 'playful' => __('Playful & bright', 'pressroot')] as $val => $label) : ?>
                            <option value="<?php echo esc_attr($val); ?>" <?php selected($b['vibe'], $val); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php esc_html_e('Filters which design kits the generator may deal.', 'pressroot'); ?></p>
                </td>
            </tr>
        </table>
        <?php submit_button(__('Save brand profile', 'pressroot')); ?>
    </form>
    <?php
}
