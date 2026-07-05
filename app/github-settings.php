<?php

/**
 * GitHub / Projects settings — the one group of settings with no Customizer
 * equivalent (default owner, API token, cache duration, OAuth Client ID, and
 * the "Connect with GitHub" device-flow widget). Everything else that used to
 * live on the old "Theme Settings" page (General/Design/Layout/Header/Footer/
 * Social Links) is fully covered by the Customizer, so that page was removed
 * — see docs/ARCHITECTURE.md for the full note.
 */

namespace App;

/**
 * Baseline values for the GitHub settings, stored as theme_mods like every
 * other setting in this theme (even though they're edited on a plain admin
 * page here, not the Customizer) so they share the same get_theme_mod()
 * storage/API as Customizer-driven options.
 *
 * NOTE(audit): 'matthummel-pa' is a personal GitHub username hardcoded as the
 * default owner. Fine for this personal site, but a distributed theme should
 * default this to '' (empty) so a fresh install doesn't silently pull a
 * stranger's repos until the new owner explicitly sets their own.
 */
function prt_github_defaults()
{
    return [
        'prt_proj_owner'       => 'matthummel-pa',
        'prt_gh_token'         => '',
        'prt_proj_cache_hours' => 6,
        'prt_gh_client_id'     => '',
    ];
}

/** Get a saved GitHub setting, falling back to prt_github_defaults(). Mirrors
 *  the prt_mod() pattern used for Customizer-driven settings elsewhere. */
function prt_github_get($key)
{
    $d = prt_github_defaults();
    return get_theme_mod($key, $d[$key] ?? '');
}

/**
 * Renders just the GitHub settings fields + connect widget — no page
 * wrapper, no <h1>. This USED to be its own "GitHub" admin page; it's now
 * the "GitHub" tab on the consolidated Appearance -> Pressroot settings page
 * (see app/pressroot-settings.php). Posts to admin-post.php (handled below)
 * rather than options.php, since these are theme_mods, not a registered
 * Settings API option group.
 */
function prt_github_tab_html()
{
    if (! current_user_can('manage_options')) {
        return;
    }
    ?>
        <p class="description">
            <?php esc_html_e('Powers the live repo data (stars, forks, latest release, README intro) shown on project pages. Everything else about the theme lives in the Customizer.', 'pressroot'); ?>
            <a href="<?php echo esc_url(admin_url('customize.php')); ?>"><?php esc_html_e('Open Customizer', 'pressroot'); ?></a>
        </p>

        <?php if (isset($_GET['updated'])) : ?>
            <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Settings saved.', 'pressroot'); ?></p></div>
        <?php endif; ?>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="prt_save_github_settings">
            <?php wp_nonce_field('prt_save_github_settings'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="prt_proj_owner"><?php esc_html_e('Default GitHub owner', 'pressroot'); ?></label></th>
                    <td>
                        <input type="text" class="regular-text" id="prt_proj_owner" name="prt_proj_owner" value="<?php echo esc_attr(prt_github_get('prt_proj_owner')); ?>">
                        <p class="description"><?php esc_html_e('Used for the live repo data when a project has no owner set.', 'pressroot'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="prt_gh_token"><?php esc_html_e('GitHub API token (optional)', 'pressroot'); ?></label></th>
                    <td>
                        <input type="text" class="regular-text" id="prt_gh_token" name="prt_gh_token" value="<?php echo esc_attr(prt_github_get('prt_gh_token')); ?>" autocomplete="off">
                        <p class="description"><?php esc_html_e('A read-only token raises the GitHub API rate limit. Set automatically by "Connect with GitHub" below, or paste one in directly.', 'pressroot'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="prt_proj_cache_hours"><?php esc_html_e('GitHub data cache (hours)', 'pressroot'); ?></label></th>
                    <td><input type="number" min="1" step="1" id="prt_proj_cache_hours" name="prt_proj_cache_hours" value="<?php echo esc_attr(prt_github_get('prt_proj_cache_hours')); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="prt_gh_client_id"><?php esc_html_e('GitHub OAuth Client ID', 'pressroot'); ?></label></th>
                    <td>
                        <input type="text" class="regular-text" id="prt_gh_client_id" name="prt_gh_client_id" value="<?php echo esc_attr(prt_github_get('prt_gh_client_id')); ?>">
                        <p class="description"><?php esc_html_e('Public Client ID from your GitHub OAuth App (Device Flow enabled). Needed for "Connect with GitHub".', 'pressroot'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('GitHub connection', 'pressroot'); ?></th>
                    <td><?php if (function_exists('App\\prt_gh_connect_widget')) { prt_gh_connect_widget(); } ?></td>
                </tr>
            </table>
            <p class="submit"><button class="button button-primary"><?php esc_html_e('Save changes', 'pressroot'); ?></button></p>
        </form>

        <p class="description"><?php esc_html_e('Per-project owner/repo, eyebrow, and demo URL are set on each project via the Project Details box.', 'pressroot'); ?></p>
    <?php
}

/**
 * Persist the GitHub settings form (prt_github_tab_html() above) as theme_mods.
 * Hooked to the `admin_post_{action}` convention (action="prt_save_github_settings"
 * hidden field in the form) rather than the Settings API, since these values
 * are theme_mods and don't need register_setting()'s option-group machinery.
 */
add_action('admin_post_prt_save_github_settings', function () {
    if (! current_user_can('manage_options')) {
        wp_die(__('You do not have permission to do this.', 'pressroot'));
    }
    check_admin_referer('prt_save_github_settings');

    set_theme_mod('prt_proj_owner', isset($_POST['prt_proj_owner']) ? sanitize_text_field(wp_unslash($_POST['prt_proj_owner'])) : '');
    set_theme_mod('prt_gh_token', isset($_POST['prt_gh_token']) ? sanitize_text_field(wp_unslash($_POST['prt_gh_token'])) : '');
    set_theme_mod('prt_proj_cache_hours', isset($_POST['prt_proj_cache_hours']) ? absint($_POST['prt_proj_cache_hours']) : 6);
    set_theme_mod('prt_gh_client_id', isset($_POST['prt_gh_client_id']) ? sanitize_text_field(wp_unslash($_POST['prt_gh_client_id'])) : '');

    wp_safe_redirect(prt_settings_tab_url('github', ['updated' => '1']));
    exit;
});
