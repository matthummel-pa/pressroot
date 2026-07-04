# Pressroot — Build Log

Chronological, one short section per step. Skim top-to-bottom for the whole
story, or jump to one section. Each entry: root cause → fix → takeaway.

---

## Phase 0 — matthummel-theme (where pressroot came from)
- Bespoke Sage 11 theme built for matthummel.com, later productized into
  pressroot as a reusable framework.
- Replaced a Kadence + Code Snippets site with a code-first Sage theme; new
  "Paper + Space Grotesk" design system.
- Local dev: PHP 8.3 + Composer + WP-CLI + SQLite drop-in + `wp server`, no
  Docker. Content migrated in via WXR import.
- Build order: scaffold/brand → design tokens → templates → Customizer →
  GitHub release → layout engine → header system → Projects admin → footer.
- Same duplication bug as pressroot later hit: Customizer *and* an admin page
  both wrote the same theme mods from day one.

## Phase 1 — Social icon system
- Unified dynamic social icon system, replacing hardcoded CSS; added a
  brand-color mode and a "Match site style" block option. Lint-verified.

## Phase 2 — Color tokens in templates
- Wired H1–H6 color variables into homepage/page templates that had hardcoded
  heading colors, so Typography settings actually reach every heading.

## Phase 3 — Developer tooling
- Built a WP-CLI suite (`app/cli.php`: export/import/reset, Style Kits, clear
  views, hook registry), a Dev Mode admin-bar toggle, more `do_action`/
  `apply_filters` hooks, PHPCS + ESLint config, and the first `ARCHITECTURE.md`.

---

## Design tokens reference (`theme.json`)
- **Layout:** content 760px, wide 1180px.
- **Palette (16):** Paper/Surface/Cream, Ink/Body/Muted/Faint, Brand purple
  `#7C5CFF` + hover/tint, Orange/Lime/Sky/Pink accents. Default WP palette/
  gradients/duotone off.
- **Gradients (6), duotone (2), fonts:** Outfit, Instrument Serif, JetBrains
  Mono — self-hosted woff2.
- **Font sizes (5):** 15px → fluid 48–88px. **Spacing (6):** 0.5rem → 6rem.
  **Shadows (3):** Soft, Lift, Ring.
- **Takeaway:** single source of truth for the block editor's palette/type/
  spacing pickers — check here before assuming a missing color is a bug.

---

## "the menu area is overlapping the container on mobile"
- **Cause:** header flex children had `flex-shrink: 0` in a non-wrapping row.
- **Fix:** let `.brand` shrink, keep only the logo mark fixed, truncate the
  brand name, hide nav/social/CTA earlier on mobile.
- **Takeaway:** a non-wrapping flex row always needs one shrinkable child.

## "none of these settings are updating"
- **Cause:** header/nav/footer CSS still targeted old markup (`.banner`,
  `.nav-primary`, `.header-cta`, `.social`) that no longer existed — Customizer
  was saving fine, the CSS just matched nothing.
- **Also:** Top Bar had a full UI but no render function at all; Announcement
  bar was hooked outside `#app`'s flex container, breaking reorder.
- **Fix:** renamed selectors to current markup across 5 files; built
  `prt_topbar_render()`; moved Announcement to `prt_before_header`.
- **Takeaway:** if a settings panel "does nothing," check CSS-to-markup match
  before assuming broken PHP/JS.

## "remove the theme settings page, just use customizer"
- **Cause:** two UIs (Customizer + a tabbed admin page) wrote identical theme
  mods.
- **Fix:** removed the admin page; kept only GitHub/Projects settings (no
  Customizer equivalent) as their own small page.
- **Takeaway:** one settings UI per setting, always.

## "make sure all the theme tools function properly / work together"
Full audit, ~20 Customizer sections:
- Nav + advanced typography still targeted `.nav-primary` — fixed.
- Menu-icon breakpoints drove dead-selector body classes — replaced with a
  3-state auto/on/off + real CSS emitter (default changes nothing).
- "Body code" leaked into `<head>` — a `get_header`/`wp_body_open` shared-guard
  hook-ordering bug (fixed by hooking only `wp_body_open`).
- Orphaned contact-intro setting wired up; duplicate social-platform
  registration consolidated; dead Hero eyebrow field removed.
- Added `active_callback` conditional visibility across ~6 parent/child pairs.
- **Takeaway:** a full audit catches what targeted fixes never will.

## "Non-existent changeset UUID"
- WordPress-core error: Customizer trying to reload a draft session that no
  longer exists. Usually stale browser session storage, not a code bug.

## "verify fixes actually work live"
- Reproduced the changeset error live inside the preview iframe — ruled out
  the theme's own code (zero "changeset" matches anywhere in it).
- **Real cause:** this local WordPress runs on **Playground CLI** (WASM PHP +
  SQLite), not MySQL — so system `wp-cli` could never find a real
  `wp-config.php` and kept failing.
- **Fix:** stop the server, `rm -rf .playground/database/*`, restart with
  `npm run wp`, load `/wp-admin` once to reseed.
- Verified live afterward: Top Bar toggle rendered correctly (previously
  invisible).
- **Takeaway:** check a project's own dev docs for non-standard local setups
  before reaching for generic WP-CLI/MySQL fixes.

## "customizer is slow to load updates"
- Two causes: WASM PHP + heavy Acorn boot per request, and almost every
  setting uses full-reload `refresh` transport instead of instant
  `postMessage`. Most are pure CSS-variable settings — good `postMessage`
  candidates if worth fixing. Not yet done, pending a decision.

## Build notes log — created, then refined
- Drafted this log, restructured it into per-step chronological sections,
  folded in Phase 0/1–3 history and the `theme.json` reference, then trimmed
  every entry down to its essentials for faster skimming.

## "one more scan, consolidate, add comments, upscale into a marketable theme"
Final audit pass before treating pressroot as a product, not just a personal
site theme:
- **2 real bugs fixed:** unguarded `filemtime()` in `social-block.php` (only
  script registration missing the `file_exists()` guard used everywhere
  else); `should_load_separate_core_block_assets` was set two places
  (`setup.php` hardcoded, `critical-css.php` Customizer-driven) — the
  hardcoded one always silently won, so the Customizer toggle never worked.
  Removed the hardcoded one.
- **Block-pattern architecture fix (the big one):** `page-patterns.php` had a
  priority-99 cleanup pass that unregistered *every* pattern except its own
  12 curated "Full page" patterns — silently deleting ~27 working
  section-level patterns (heroes, CTAs, pricing, testimonials, stat strips…)
  from `block-patterns.php`, `patterns-extra.php`, `sections-library.php`,
  and `blocks.php` on every single page load. Removed the blanket purge (more
  prebuilt sections = more product value for a page-building theme); kept
  only the two truly-superseded duplicate patterns removed by name
  (`about-page`/`resume-page`, since `about-full`/`resume-full` already do
  that job better). Also fixed a duplicate pattern-category registration and
  a duplicate `matthummel/hero` slug collision (renamed to `hero-simple`)
  that were both silently throwing WordPress "doing_it_wrong" notices.
- **Consolidation:** removed dead `app/Vite.php` (unreferenced, superseded by
  Acorn's real `Vite` facade); removed a duplicate hardcoded Google Fonts
  enqueue in `setup.php` that fought with the real Customizer-driven one in
  `customizer.php`; extracted 3 shared helpers that were duplicated across
  files — `prt_apply_style_kit()`, `prt_ensure_theme_options_panel()`
  (deduped 20 call sites), `prt_require_admin_post()`.
- **Comments:** added file- and function-level doc comments across all
  ~45 `app/*.php` files — what each file does, why non-obvious code exists,
  what problem each helper solves. 13 additional minor issues flagged inline
  as `// NOTE(audit):` for later review (stale labels, a hardcoded default
  GitHub owner, a couple of dead/unreachable branches) rather than fixed
  blind, since none of them affect current behavior.
- **New feature — AI Setup Assistant** (`app/ai-assistant.php`,
  Appearance → AI Setup Assistant): pick a site type (Agency, Freelance/
  Portfolio, SaaS, Blog, Marketing landing) to apply a matching Style Kit
  *and* auto-create the starter pages that site type needs, pre-filled with
  the theme's existing full-page patterns (idempotent — never touches a page
  that already exists). Plus a starter hero-copy generator: type a one-line
  business description, get a headline + subheadline back from Pollinations'
  free text API — same no-key, no-server-proxy pattern the Hero Image Finder
  already used for images. This is the "clone into other project types"
  answer: the site-type list is exactly that set of starting points, and any
  fork can add its own via the `matthummel/site_types` filter.
- **Takeaway:** a full audit right before "productizing" catches architecture
  problems (like the pattern purge) that targeted fixes never surface,
  because they only show up when you ask "does everything I built actually
  get used?"

---

## Recurring bug patterns
- **Dead selector rot** — renamed markup breaks every CSS file still
  targeting the old class names, silently.
- **Hook-ordering hazards** — `get_header` (head) vs. `wp_body_open` (body);
  never share one guard between them.
- **Duplicate sources of truth** — two UIs writing one option confuses users.
- **Save ≠ working** — a setting can persist fine and still do nothing if the
  template/CSS never reads it. Trace to the render, not just `get_theme_mod()`.
- **Non-standard local envs** — check project docs before generic tooling.

## Takeaways for next time
- Check CSS selector drift first when "settings don't do anything."
- Build `active_callback`-style conditional visibility in from day one.
- Decide `postMessage` vs. `refresh` per setting, deliberately.
- Document non-standard local dev setups prominently.
- Do a full audit pass periodically, not just targeted fixes.

---

*Keep adding to this in the same short, per-step format.*
