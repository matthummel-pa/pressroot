# Changelog

All notable changes to Pressroot are documented here.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2026-07-01

Imported upstream matthummel-theme v1.2.0–v2.0.0 framework updates, adapted to
Pressroot naming (`prt_` / `prt-` / `prt/`) and the existing Sage 11 + Roots + Vite
structure. Bud/Tailwind-config files, personal site content, and dev-only scripts
were intentionally not imported.

### Added
- **Bespoke blocks** — `app/blocks-bespoke.php` registers six editor blocks (`prt/stat-strip`, `prt/skills-grid`, `prt/timeline`, `prt/resource-group`, `prt/cta-band`, `prt/project-card`) with per-block editor scripts in `resources/js/prt-*-editor.js`.
- **Section block** — `app/block-section.php` (`prt/section`) generic section wrapper with editor script.
- **Block patterns** — `app/block-patterns.php`: 12 full-page/section patterns under the Pressroot category, built from the bespoke blocks and `design-language.css` classes.
- **Pattern Library admin page** — `app/pattern-library.php` (Appearance → Pattern Library) with copy-pattern-name helper.
- **Social Links Customizer section + Quick Setup panel** — `app/social-links.php`, `app/quick-setup.php`.
- **Page templates** — `template-about`, `template-blog`, `template-legal`, `template-projects`, `template-resources`, `template-resume` Blade templates.
- **Projects experience** — redesigned `template-projects.blade.php` (dark hero with live GitHub stat strip, category filter pills, live repo grid via `Github::fetchRepos()`, featured manual projects) and `single-projects.blade.php` (dark hero, tech-stack pills, GitHub stats band, related projects). GitHub owner resolves from the `prt_proj_owner` theme mod.
- **Projects admin** — `_prt_tech_stack` and `_prt_featured` meta fields; REST endpoint `GET /wp-json/prt/v1/github-repos` proxying `Github::fetchRepos()` with transient caching.
- **Reading progress JS** — `resources/js/reading-progress.js` (progress bar, auto TOC, code copy buttons) driving new markup in `partials/content-single.blade.php`; `app/reading.php` slimmed to the read-time badge only to avoid duplicate bars/TOCs.
- **CSS** — `page-templates.css`, `blocks.css`, `design-language.css`, imported from `app.css`.
- **Expanded Google Fonts library** — grouped font catalog in `app/customizer.php` behind a `matthummel/fonts` filter.
- **Redesigned blog views** — `archive.blade.php` editorial post list, `content-single.blade.php` single-post layout, refreshed `sections/header.blade.php`.
- `config/view.php` — compiled Blade views moved to `wp-content/uploads/acorn-views` (fixes Windows file-permission conflicts).
- `.wp-now.json` for the wp-now local dev workflow.

### Changed
- `projects` CPT: `has_archive` → `false` so the Projects page template is served at `/projects` instead of the CPT archive.
- `functions.php` loads the six new app modules.

### Fixed (upstream v1.9.x–v2.0.0 audit)
- `app.css`: defined missing `--color-paper` token; dark-mode overrides for `.form-error`, `.btn-outline`, sticky-header glass effect.
- `page-templates.css`: dark-mode backgrounds for project heroes, blog hero image, and filter pills.
- Blade `@php(...)` inline-syntax parse errors in projects templates (block form used throughout).

## [1.0.0] - 2026-06-26

### Added
- Initial Pressroot release — a rebranded, namespaced (`prt_` / `prt-` / `pressroot`) productization of the Sage 11 theme framework.
- Design system + Style Kits, dark mode, `theme.json` controls.
- Hero builder (editable copy, 1–3 columns, flexbox, side/background images), built-in image finder (Openverse / Unsplash / Pexels / AI), and site-wide on-scroll animations.
- Header layout + flexbox navigation + off-canvas popout, Social Icons, top bar, scheduled announcement bar, footer builder, and per-device responsive controls.
- Dynamic blocks + 17 starter patterns, live GitHub project pages, plugin-free contact form, newsletter, cookie notice, code injection.
- Performance suite (bloat control, self-hosted fonts, split/critical CSS), SEO + JSON-LD, and white-label / onboarding.
