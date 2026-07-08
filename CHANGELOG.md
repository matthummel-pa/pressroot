# Changelog

All notable changes to Pressroot are documented here.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.6.0] - 2026-07-08

**One brief, whole sites.** Theme Settings becomes the single prompting surface for the entire site, and builds now generate the full shell — navigation, header, and footer — not just content.

### Added
- **Core AI instructions**: every Theme Settings + Brand answer compiles into one saved "CORE SITE BRIEF" (`prt_core_ai_instructions`) prepended to every AI call; viewer on the settings page shows exactly what the model receives.
- **Site chrome builder** (`prt_build_site_chrome()`): generated "Pressroot Menu" (Home + starter pages) assigned to the primary location, goal-driven header CTA (Get a quote / Shop now / Book now / Subscribe) targeting the most relevant page, brand-driven footer (description tagline, light/dark ground). Runs on apply, 🎲 refresh-all, and Theme Settings auto-build; hand-made menus are never touched.
- **Theme Settings tab** as the owner's front door: Identity + full brand questionnaire (industry dropdown), consolidated with site title/tagline/brand color; auto-build on save with a real-step status bar; sticky saving bar.
- **AI instructions upgrades**: WYSIWYG editor with a live counter and 1,000-word cap, plus uploadable `.md` instruction files (stored server-side, appended to the brief as reference docs, trimmed to prompt-safe length).
- **AI Models tab**: per-provider model dropdowns (incl. `claude-opus-4-8`), validated on save and read; image + video connector registries; keys masked and never sent to the browser.
- **Core-blocks-only generation** (default on): remixed C/D page variants built purely from core Gutenberg blocks + new `prt/smart-hero` and `prt/smart-cta` server-rendered theme blocks with auto-generated copy; no Custom HTML blocks.
- **Edit-screen AI tools**: AI-write / AI-image / new-design actions plus per-role suggested blocks on page and post edit screens.
- **✨ Generate my brand with AI**: drafts voice, audience, goal, and personality from just the name + one-liner.
- **Docs website**: full marketing site in Pressroot branding at matthummel-pa.github.io/pressroot (landing, documentation, become-a-tester, collaborate) plus a restaurant build recipe (`docs/BUILD-RECIPE-RESTAURANT.md`).
- **Attribution**: theme credit ("Pressroot theme by matthummel") in the footer credit line, settings hero byline, and `style.css` copyright header.

### Changed
- Design settings (kit, colors, fonts, corners, hero copy) are now **generated in the backend** from brand answers; manual controls are hidden until the first build, then live in a collapsed fine-tuning panel.
- Site-type previews are gated until the first Theme Settings save, always render the owner's design (bare/brand/kit modes), and bust caches on every build.
- Tab order: AI Models → Theme Settings → Site Types → GitHub → Support; legacy Brand tab merged into Theme Settings.

## [1.5.0] - 2026-07-08

**"Your brand in. Your site out."** Pressroot becomes an AI-assisted design generator wearing the Repofolio brand, with Repofolio itself bundled as a theme addon.

### Added
- **Repofolio theme addon**: the Repofolio plugin bundled under `app/Repofolio/` — GitHub tab on Appearance → Pressroot, OAuth + PAT fallback, repo grid block, `repofolio_project` case studies. The standalone plugin takes over automatically if activated; option keys are shared. Restored `prt_github_get()` and the `App\Github` facade.
- **Brand tab**: a 12-question, plain-language brand questionnaire (identity, color, light/dark, personality, audience, industry, voice, goal, imagery, density, trend) that steers all generation; opt-in sync to site title/tagline.
- **Design generator**: applying/refreshing a Site Type now deals a random pattern variant per page, a random Style Kit from a per-type pool, and a random design trend — brand-filtered, never repeating the current deal; existing pages are refreshed (not skipped) so old designs never linger; branding is re-asserted after every deal.
- **Remix engine** (`app/site-type-remix.php`): programmatically generated C/D variants for every page of every site type (~50 patterns) from seeded hero/feature/CTA pools.
- **Three new Site Types**: Affiliate Marketing, Restaurant/Café, Real Estate (hand-built A/B patterns each).
- **Six new Repofolio-family Style Kits** + a reserved **Core Marketing** kit exclusive to the Marketing type.
- **Six design trends** as CSS-only layers: Bento spectrum, Glassmorphism, Neo-brutalist, Editorial serif, Swiss minimal, Retro pop.
- **✨ AI Builder** (`app/ai-builder.php`): per-page / per-type "Write with AI" fills Gutenberg pages with brand-profile copy (text-only replacement — markup is never AI-generated); AI brand-image generation into the Media Library; "Edit blocks" shortcuts.
- **"Powered by AI — or not"**: one Brand-tab switch (`prt_ai_features_enabled()`) disables every AI-calling feature while the generator keeps working.
- **Site Type preview upgrades**: cards preview all four designs per page, each rendered in the type's own kit (`prt_preview_kit`), with no-cache headers + cache-busted URLs.
- `npm run refresh` (ESLint + Pint + Vite build), `npm run lint`, `npm run lint:php`.

### Changed
- **Full Repofolio rebrand**: token swap to Iris/Ink/Paper + spectrum accents, brand + spectrum gradients, 8px spectrum bar site-wide, spectrum-topped cards, gradient pill buttons (core block buttons included), dark radial homepage hero, dark footer defaults, docs-site chrome for Appearance → Pressroot.
- **Generic base-theme hero**: every hero string is now a Customizer mod with neutral defaults (tagline: "Your brand in. Your site out."); Brand profile supplies smarter defaults. Personal copy removed.
- New `screenshot.png` in the Repofolio design; `style.css` description leads with the tagline.
- AI copy prompt is now site-type + brand aware and takes a fresh angle per run.

### Fixed
- Pattern previews render the real design system (the preview route now fires `prt_head_end`) and are never served stale (nocache headers, versioned URLs, `filemtime`-versioned admin CSS, critical-CSS flush on every deal/brand save).

## [1.4.0] - 2026-07-05

**Pressroot AI, Site Types, and a consolidated settings page.** The theme's admin experience moved from four separate Appearance pages to one, and its AI-assisted setup grew from a one-off "generate some copy" tool into the theme's primary way to start a new site.

### Added
- **Pressroot AI — Site Types**: pick a business category (Agency/Studio, Freelance/Portfolio, SaaS/Startup, Blog/Content site, Marketing/Landing page) to apply its matching Style Kit and create starter pages together, each pre-filled with one of two hand-built pattern variants. Live, scaled-down previews of each type's first page render before you commit.
- **Regenerate**: swap any starter page — or every page in a site type at once — to its other hand-built variant with one click.
- **Starter hero copy generator**: a one-line business description in, a draft headline + subheadline out.
- **AI Connectors**: optional free API keys for Google Gemini, Groq, and OpenRouter, alongside the always-on, keyless Pollinations default, selectable per-generation from a model dropdown. All generation is proxied server-side — API keys never reach the browser.
- **AI in the block editor**: a "Generate with AI" toolbar button on paragraph, heading, and list blocks for everyday content editing, not just initial setup.
- **Theme Addons**: a new Customizer section to switch the entire Pressroot AI feature (menu entry, endpoints, admin-post actions) off if a site doesn't want the AI surface at all.
- **Appearance → Pressroot**: Theme Tools, Starter Sites, Pressroot AI, and GitHub — four separate admin pages — are now one page with a left-sidebar menu and the active section's content on the right.
- **Support tab**: live status for the theme's own GitHub repository (stats, languages, latest releases, open issues) via the existing GitHub data engine, plus a curated, filterable list of links to the theme's documentation. Always visible, independent of the Pressroot AI addon toggle.
- **Editable Docs/Support links** in the settings page header, defaulted to the theme's real repo.
- **Export / Import / Reset** moved into a collapsed "Advanced" section on the Site Types tab (same tool, new home).

### Changed
- The former "Pressroot AI" tab is renamed **Site Types**, reflecting its role as the primary "set up your site" tab.
- The standalone **Style Kits** tab is retired — every Site Type already applies its own matching kit automatically, so a separate manual picker was a second way to do the same thing. The underlying Style Kit data/apply logic is unchanged and still powers Site Types.
- The **Starter Sites** demo importer is retired in favor of Site Types (more personas, regenerate, live previews, dedicated per-type patterns); its dashboard "Create starter pages" button (blank pages, no design) is removed the same way.
- README and the settings reference now document Pressroot AI, Site Types, the consolidated settings page, WP-CLI, Dev Mode, Reading UX, Pattern Library, and Google Fonts Collection — all previously undocumented — and the stale "17 starter patterns" count is corrected to the actual 22 general-purpose + 26 Site Type patterns.
- Repository references corrected from `matthummel-pa/matthummel-theme` to `matthummel-pa/pressroot` throughout the code and docs.
- Leftover "Matt Hummel"/"Matthummel" branding in user-facing strings (pattern category labels, the dashboard widget title, Pattern Library help text) now reads "Pressroot".
- README, the Support doc, and the in-admin Support tab now include a short origin story: Pressroot started as the author's personal portfolio theme and was generalized into a rebrandable framework, with Site Types as the piece aimed at multiple business categories.

### Fixed
- A dev-seed mu-plugin collided with Pressroot AI's page slugs, corrupting preview content on some installs.
- Switching site types now force-deletes the previous type's pages (bypassing Trash) instead of leaving them to accumulate.
- A `prt/skills-grid` pattern attribute mismatch (wrong key/shape) that silently fell back to placeholder card content.
- Block-editor pattern/thumbnail previews that depend on a `ServerSideRender` REST round-trip now show an instant static skeleton instead of staying blank inside lightweight preview surfaces (pattern thumbnails, the "Choose a pattern" modal).
- Corrected an inaccurate claim in the docs that the Starter Sites importer's patterns were mostly dead — they weren't; the decision to retire it in favor of Site Types stands on its own merits (see BUILD-NOTES.md for the full correction).

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
