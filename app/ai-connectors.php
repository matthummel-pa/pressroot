<?php

/**
 * AI Connectors — provider registry + key storage for Pressroot AI.
 *
 * Lets the theme owner optionally connect additional free AI text-generation
 * providers beyond the built-in Pollinations endpoint (used by the Hero Image
 * Finder and, until now, the only option for Pressroot AI's starter hero-copy
 * generator). Pollinations stays the always-on, no-signup default — nothing
 * here is required for Pressroot AI to keep working exactly as it did before.
 *
 * This USED to be its own "AI Connectors" admin page; it's now folded into
 * Appearance -> Pressroot AI as a collapsed "Advanced" section (see
 * prt_ai_connectors_fields_html() below and its embedding in
 * app/ai-assistant.php) so the whole addon lives on one screen. This file no
 * longer registers an admin_menu page of its own.
 *
 * Providers included are the best currently-available FREE (no credit card)
 * AI text APIs as of mid-2026:
 *   - Google Gemini  — generous, indefinite free tier via Google AI Studio.
 *   - Groq           — very fast free-tier inference (Llama/Qwen/DeepSeek).
 *   - OpenRouter     — one key, an aggregator with several always-free models.
 * Each needs a free API key from the provider (linked below) — this theme
 * never talks to a paid API or requires a credit card anywhere.
 *
 * SECURITY: API keys are stored as theme_mods (same pattern the existing
 * GitHub token uses in app/github-settings.php — see the NOTE(audit) there
 * about theme_mods not being the most secure secret store available). Keys
 * are NEVER sent to the browser: Pressroot AI's "Generate" button calls a
 * server-side AJAX endpoint (prt_ai_generate_copy, below) that reads the
 * stored key and calls the provider from PHP — the key never appears in any
 * page source, request the browser makes, or client-side JS.
 */

namespace App;

/**
 * The connector registry. 'pollinations' is always available and needs no
 * key/settings row (kept out of the settings table below); the other three
 * are optional and only show up as a model choice once a key is saved.
 *
 * @return array<string, array{label:string, needs_key:bool, model_default:string, docs_url:string, note:string}>
 */
function prt_ai_connectors_defs(): array
{
    return apply_filters('matthummel/ai_connectors', [
        'pollinations' => [
            'label'         => __('Pollinations (default)', 'pressroot'),
            'needs_key'     => false,
            'model_default' => '',
            'docs_url'      => 'https://pollinations.ai/',
            'note'          => __('Free, no signup, no API key — already used by the Hero Image Finder. Always available.', 'pressroot'),
        ],
        'gemini' => [
            'label'         => __('Google Gemini', 'pressroot'),
            'needs_key'     => true,
            'model_default' => 'gemini-2.0-flash',
            'docs_url'      => 'https://ai.google.dev/gemini-api/docs',
            'note'          => __('Free tier via Google AI Studio — no credit card required. Get a key at aistudio.google.com/apikey.', 'pressroot'),
        ],
        'groq' => [
            'label'         => __('Groq', 'pressroot'),
            'needs_key'     => true,
            'model_default' => 'llama-3.3-70b-versatile',
            'docs_url'      => 'https://console.groq.com/keys',
            'note'          => __('Free tier, very fast inference (Llama/Qwen/DeepSeek). Get a key at console.groq.com/keys.', 'pressroot'),
        ],
        'openrouter' => [
            'label'         => __('OpenRouter', 'pressroot'),
            'needs_key'     => true,
            'model_default' => 'openrouter/free',
            'docs_url'      => 'https://openrouter.ai/keys',
            'note'          => __('One key, several always-free models (auto-routed by default). Get a key at openrouter.ai/keys.', 'pressroot'),
        ],
    ]);
}

function prt_ai_get_key(string $slug): string
{
    return get_theme_mod('prt_ai_key_' . $slug, '');
}

function prt_ai_get_model(string $slug): string
{
    $defs = prt_ai_connectors_defs();
    $default = $defs[$slug]['model_default'] ?? '';
    return get_theme_mod('prt_ai_model_' . $slug, $default) ?: $default;
}

/** A connector is "configured" if it needs no key (Pollinations) or has one saved. */
function prt_ai_is_configured(string $slug): bool
{
    $defs = prt_ai_connectors_defs();
    if (! isset($defs[$slug])) {
        return false;
    }
    return empty($defs[$slug]['needs_key']) || prt_ai_get_key($slug) !== '';
}

/**
 * @return array<string, array> Every configured connector (Pollinations
 *                               always first), each entry merged with its
 *                               saved key/model, ready for the dropdown.
 */
function prt_ai_configured_connectors(): array
{
    $out = [];
    foreach (prt_ai_connectors_defs() as $slug => $def) {
        if (! prt_ai_is_configured($slug)) {
            continue;
        }
        $out[$slug] = array_merge($def, [
            'model' => prt_ai_get_model($slug),
        ]);
    }
    return $out;
}

/**
 * Renders just the connectors settings table + its own <form> — no page
 * wrapper, no <h1> — so it can be dropped into an <details> "Advanced"
 * section on the Pressroot AI screen (see app/ai-assistant.php). Kept as its
 * own function/file (rather than inlined there) because it's genuinely about
 * the connector registry, not page-creation/regeneration.
 */
function prt_ai_connectors_fields_html(): void
{
    ?>
    <p class="description">
        <?php esc_html_e('Pollinations is always on and needs nothing set up here. Connect any of these free providers to add them as extra model choices above. Every key stays server-side and is never sent to the browser.', 'pressroot'); ?>
    </p>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="prt_save_ai_connectors">
        <?php wp_nonce_field('prt_save_ai_connectors'); ?>
        <table class="form-table" role="presentation">
            <?php foreach (prt_ai_connectors_defs() as $slug => $def) :
                if (empty($def['needs_key'])) {
                    continue; // Pollinations: nothing to configure.
                }
                $configured = prt_ai_is_configured($slug);
            ?>
                <tr>
                    <th scope="row">
                        <?php echo esc_html($def['label']); ?>
                        <?php if ($configured) : ?>
                            <span style="display:inline-block;margin-left:6px;padding:2px 8px;border-radius:999px;background:#edfaef;color:#1e7a34;font-size:11px;font-weight:600">
                                <?php esc_html_e('Connected', 'pressroot'); ?>
                            </span>
                        <?php endif; ?>
                    </th>
                    <td>
                        <p class="description" style="margin:0 0 8px">
                            <?php echo esc_html($def['note']); ?>
                            <a href="<?php echo esc_url($def['docs_url']); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Get a free key ↗', 'pressroot'); ?></a>
                        </p>
                        <label for="prt_ai_key_<?php echo esc_attr($slug); ?>" class="screen-reader-text"><?php esc_html_e('API key', 'pressroot'); ?></label>
                        <input
                            type="password"
                            class="regular-text"
                            autocomplete="off"
                            id="prt_ai_key_<?php echo esc_attr($slug); ?>"
                            name="prt_ai_key_<?php echo esc_attr($slug); ?>"
                            value="<?php echo esc_attr(prt_ai_get_key($slug)); ?>"
                            placeholder="<?php esc_attr_e('Paste API key…', 'pressroot'); ?>"
                        >
                        <br><br>
                        <label for="prt_ai_model_<?php echo esc_attr($slug); ?>"><?php esc_html_e('Model', 'pressroot'); ?></label><br>
                        <input
                            type="text"
                            class="regular-text"
                            id="prt_ai_model_<?php echo esc_attr($slug); ?>"
                            name="prt_ai_model_<?php echo esc_attr($slug); ?>"
                            value="<?php echo esc_attr(prt_ai_get_model($slug)); ?>"
                            placeholder="<?php echo esc_attr($def['model_default']); ?>"
                        >
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <p class="submit"><button class="button button-primary"><?php esc_html_e('Save connectors', 'pressroot'); ?></button></p>
    </form>
    <?php
}

/** Persist the AI Connectors form as theme_mods (same convention as GitHub
 *  settings). Redirects back to the merged Pressroot AI screen, which shows
 *  the notice and re-opens the Advanced section (see app/ai-assistant.php). */
add_action('admin_post_prt_save_ai_connectors', function () {
    if (! current_user_can('manage_options') || ! prt_addon_enabled('pressroot_ai')) {
        wp_die(__('You do not have permission to do this.', 'pressroot'));
    }
    check_admin_referer('prt_save_ai_connectors');

    foreach (prt_ai_connectors_defs() as $slug => $def) {
        if (empty($def['needs_key'])) {
            continue;
        }
        $keyField   = 'prt_ai_key_' . $slug;
        $modelField = 'prt_ai_model_' . $slug;
        set_theme_mod($keyField, isset($_POST[$keyField]) ? sanitize_text_field(wp_unslash($_POST[$keyField])) : '');
        set_theme_mod($modelField, isset($_POST[$modelField]) ? sanitize_text_field(wp_unslash($_POST[$modelField])) : '');
    }

    wp_safe_redirect(prt_settings_tab_url('ai', ['connectors_updated' => '1']) . '#prt-ai-advanced');
    exit;
});

/**
 * Build the same fixed-shape prompt Pressroot AI's starter-copy
 * generator has always used (see the matching comment that used to live only
 * in resources/js/ai-assistant.js) — now shared here so every provider,
 * Pollinations included, gets identical instructions regardless of which
 * model generates the response.
 */
function prt_ai_build_hero_prompt(string $description, string $siteType = ''): string
{
    // Anchor the copy to the applied Site Type category so generated copy
    // matches the design that was just generated for it, and demand a fresh
    // angle each run so "Regenerate" produces genuinely new copy — pairing
    // with the Site Types tool that deals a random new design on every
    // refresh (see app/ai-assistant.php).
    $typeLine = '';
    if ($siteType !== '' && function_exists('App\\prt_site_types')) {
        $types = prt_site_types();
        if (isset($types[$siteType]['label'])) {
            $typeLine = 'This is a "' . $types[$siteType]['label'] . '" website — write in the voice that category expects. ';
        }
    }
    // Fold in the Brand tab's questionnaire answers (app/site-type-remix.php)
    // so generated copy is anchored to the actual business, not just the
    // one-line description typed into the generator.
    $brandLine = '';
    if (function_exists('App\\prt_brand_profile')) {
        $brand = prt_brand_profile();
        if ($brand['name'] !== '') {
            $brandLine .= 'The business is called "' . $brand['name'] . '". ';
        }
        $vibes = ['bold' => 'bold and confident', 'minimal' => 'minimal and sharp', 'warm' => 'warm and inviting', 'playful' => 'playful and bright'];
        if (isset($vibes[$brand['vibe']])) {
            $brandLine .= 'Brand personality: ' . $vibes[$brand['vibe']] . '. ';
        }
    }
    return 'Write website hero copy for this business: "' . $description . '". '
        . $typeLine
        . $brandLine
        . 'Voice: confident, concise, a little playful — lead with the outcome, no jargon, never overpromise. '
        . 'Take a completely fresh angle every time you are asked; do not repeat phrasings from earlier runs (variation seed: ' . wp_rand(1000, 9999) . '). '
        . "Respond in exactly this format with no extra commentary:\n"
        . "HEADLINE: <a punchy headline, under 10 words>\n"
        . 'SUBHEAD: <one supporting sentence, under 25 words>';
}

/**
 * Call one connector server-side and return its raw text response (or an
 * error). Every provider keeps its secret key in this one PHP function —
 * it never reaches the browser. Returns ['ok' => bool, 'text' => string] on
 * success or ['ok' => false, 'error' => string] on failure.
 */
function prt_ai_generate_text(string $slug, string $prompt): array
{
    $defs = prt_ai_connectors_defs();
    if (! isset($defs[$slug]) || ! prt_ai_is_configured($slug)) {
        return ['ok' => false, 'error' => __('That model isn\'t connected.', 'pressroot')];
    }

    if ($slug === 'pollinations') {
        $response = wp_remote_get('https://text.pollinations.ai/' . rawurlencode($prompt), ['timeout' => 20]);
        if (is_wp_error($response)) {
            return ['ok' => false, 'error' => $response->get_error_message()];
        }
        return ['ok' => true, 'text' => wp_remote_retrieve_body($response)];
    }

    $key   = prt_ai_get_key($slug);
    $model = prt_ai_get_model($slug);

    if ($slug === 'gemini') {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model) . ':generateContent?key=' . rawurlencode($key);
        $response = wp_remote_post($url, [
            'timeout' => 20,
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => wp_json_encode([
                'contents' => [['parts' => [['text' => $prompt]]]],
            ]),
        ]);
        if (is_wp_error($response)) {
            return ['ok' => false, 'error' => $response->get_error_message()];
        }
        $body = json_decode(wp_remote_retrieve_body($response), true);
        $text = $body['candidates'][0]['content']['parts'][0]['text'] ?? '';
        if ($text === '' && ! empty($body['error']['message'])) {
            return ['ok' => false, 'error' => $body['error']['message']];
        }
        return ['ok' => true, 'text' => $text];
    }

    if ($slug === 'groq' || $slug === 'openrouter') {
        // Both speak the same OpenAI-compatible chat-completions shape.
        $endpoint = $slug === 'groq'
            ? 'https://api.groq.com/openai/v1/chat/completions'
            : 'https://openrouter.ai/api/v1/chat/completions';

        $headers = [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $key,
        ];
        if ($slug === 'openrouter') {
            // Recommended (not required) by OpenRouter so requests are attributable.
            $headers['HTTP-Referer'] = home_url('/');
            $headers['X-Title']      = get_bloginfo('name');
        }

        $response = wp_remote_post($endpoint, [
            'timeout' => 20,
            'headers' => $headers,
            'body'    => wp_json_encode([
                'model'    => $model,
                'messages' => [['role' => 'user', 'content' => $prompt]],
            ]),
        ]);
        if (is_wp_error($response)) {
            return ['ok' => false, 'error' => $response->get_error_message()];
        }
        $body = json_decode(wp_remote_retrieve_body($response), true);
        $text = $body['choices'][0]['message']['content'] ?? '';
        if ($text === '' && ! empty($body['error']['message'])) {
            return ['ok' => false, 'error' => $body['error']['message']];
        }
        return ['ok' => true, 'text' => $text];
    }

    return ['ok' => false, 'error' => __('Unknown model.', 'pressroot')];
}

/**
 * AJAX endpoint Pressroot AI's copy generator calls instead of hitting
 * Pollinations directly from the browser, so keyed providers' secret keys
 * never have to leave the server. Same admin-only gate as the rest of the
 * Pressroot AI screen, plus the addon on/off switch (app/theme-addons.php).
 */
add_action('wp_ajax_prt_ai_generate_copy', function () {
    if (! current_user_can('edit_theme_options') || ! prt_addon_enabled('pressroot_ai') || ! check_ajax_referer('prt_ai_generate_copy', 'nonce', false)) {
        wp_send_json_error(['message' => __('Not allowed.', 'pressroot')], 403);
    }

    $description = isset($_POST['description']) ? sanitize_text_field(wp_unslash($_POST['description'])) : '';
    $model       = isset($_POST['model']) ? sanitize_key($_POST['model']) : 'pollinations';

    if ($description === '') {
        wp_send_json_error(['message' => __('Describe your business first.', 'pressroot')]);
    }

    // Which site type is live right now? Detected from the starter pages the
    // Site Types tool tagged, so the prompt stays category-aware without the
    // browser needing to send anything extra.
    $activeType = '';
    if (function_exists('App\\prt_get_site_type_pages')) {
        foreach (prt_get_site_type_pages() as $sp) {
            if (! empty($sp->prt_site_type)) {
                $activeType = (string) $sp->prt_site_type;
                break;
            }
        }
    }

    $prompt = prt_ai_build_hero_prompt($description, $activeType);
    $result = prt_ai_generate_text($model, $prompt);

    if (! $result['ok']) {
        wp_send_json_error(['message' => $result['error'] ?: __('Generation failed.', 'pressroot')]);
    }

    wp_send_json_success(['text' => $result['text']]);
});
