<p align="center">
  <img src="brand/pressroot-logo.svg" alt="Pressroot" width="380">
</p>

<h1 align="center">Pressroot <sup><em>Beta</em></sup></h1>

<p align="center"><strong>Your brand in. Your site out.</strong><br>
The AI site-builder WordPress theme: answer a short brand questionnaire and Pressroot generates the whole site —<br>
design, pages, copy, images, navigation, header, and footer — all on core Gutenberg blocks. Free AI by default.</p>

<p align="center">
  <a href="https://matthummel-pa.github.io/pressroot/">Website</a> ·
  <a href="https://matthummel-pa.github.io/pressroot/documentation.html">Documentation</a> ·
  <a href="https://matthummel-pa.github.io/pressroot/testers.html">Become a tester</a> ·
  <a href="https://matthummel-pa.github.io/pressroot/collaborate.html">Collaborate</a> ·
  <a href="https://matthummel-pa.github.io/pressroot/feedback.html">Feedback</a> ·
  <a href="CHANGELOG.md">Changelog</a>
</p>

<p align="center">Stack: Sage 11 · Blade · Tailwind CSS v4 · Vite · Acorn (Laravel-in-WordPress) · PHP 8.3 · v1.6.0 · MIT</p>

---

> **Open beta.** Pressroot is being tested on real businesses right now — testers get a free domain and keep their site. [Join the beta →](https://matthummel-pa.github.io/pressroot/testers.html)

## How it works

1. **Tell it your brand** — Theme Settings + the Brand questionnaire are the *core prompt for your entire site*: name, one-liner, industry (dropdown), audience, voice, goal, imagery, density, design trend, plus free-form **AI instructions** (WYSIWYG, 1,000-word cap) and uploadable **`.md` instruction files**. Everything compiles into one saved **CORE SITE BRIEF** prepended to every AI call.
2. **Save — it builds** — the status bar runs the pipeline: compile the brief → deal a design (kit + trend filtered by your answers) → generate pages on core blocks → build the **site chrome** (nav menu, goal-driven header CTA, brand-driven footer) → prime smart-block copy.
3. **Refresh until you love it** — every 🎲 deals a genuinely different theme (never repeating the current one); your brand color and content survive every deal.

The whole theme wears the [Repofolio](https://github.com/matthummel-pa/repofolio) design language — iris/pink/coral gradients, spectrum-topped cards, gradient pill buttons — and Repofolio itself ships inside as a theme addon.

## Features

### The generator (Pressroot AI)
- **Theme Settings = the prompt** — one tab consolidates identity, the full brand questionnaire, AI instructions, and instruction-file uploads. Design settings (kit, colors, fonts, corners, hero copy) are **written in the backend by the build**; manual controls unlock as a collapsed fine-tuning panel after the first save, so owners never fight the generator. "✨ Generate my brand with AI" drafts the questionnaire from just a name + one-liner.
- **Site Types ×8** — Restaurant/Café, Real Estate, Affiliate Marketing, Agency/Studio, Freelance/Portfolio, SaaS/Startup, Blog/Content, Marketing/Landing. Each ships starter pages, per-industry **marketing questions** the AI treats as hard facts, and matched design pools. Live previews render in the owner's own design (gated until first setup save, always cache-busted).
- **🎲 Refresh = a whole new theme** — random layout variants per page, a random Style Kit from the type's pool, and a random design trend — brand-filtered, never repeating, branding re-asserted last, caches flushed.
- **Core blocks only (default)** — generated pages use core Gutenberg blocks + coded theme blocks (`prt/smart-hero`, `prt/smart-cta` with auto-generated copy). **No Custom HTML blocks, ever.** The AI writes text and images only — block markup is never AI-touched, so pages can't break.
- **Full site chrome** — builds generate the shell too: a synced "Pressroot Menu" assigned to the primary location (hand-made menus never touched), a goal-driven header CTA (Get a quote / Shop now / Book now / Subscribe) pointing at the most relevant page, and a footer with your description as tagline on a light/dark ground.
- **Edit-screen AI tools** — AI-write, AI-image, and new-design actions plus per-role suggested blocks right on page/post edit screens.
- **AI models** — per-provider dropdowns, validated on save and read: keyless **Pollinations** (text + images, the free default), **Anthropic** (Claude Opus/Sonnet/Haiku), **OpenAI** (GPT + gpt-image-1), **Google Gemini**, **Groq**, **OpenRouter**, **Stability** (SD 3.5); Luma/Runway video keys stored for the upcoming AI video hero. Keys live server-side only, masked in the UI, with automatic free fallback — **the default stack costs $0**.
- **"Powered by AI — or not"** — one switch disables every AI call; the design generator keeps working without it.

### Design system & theming
- **Tokens-first** — colors, type scale, spacing as Tailwind v4 `@theme` variables; re-skin from one place.
- **13 Style Kits** — six originals + the Repofolio family (Iris Dark, Pink Pop, Coral Cream, Mint Fresh, Cyan Sky, Amber Toast) + a reserved **Core Marketing** kit.
- **6 design trends** — CSS-only layers: Bento spectrum, Glassmorphism, Neo-brutalist, Editorial serif, Swiss minimal, Retro pop.
- **Dark mode** — light / dark / auto, no-flash, dedicated dark navbar surface.
- **`theme.json`** — spacing, border, shadow, fluid type, gradient, duotone controls in the editor.

### Hero, header, footer & content
- **Hero builder** — fully generic base-theme hero (every string a Customizer mod), 1–3 columns, media, background modes, entrance animation; built-in image finder (Openverse/Unsplash/Pexels/AI-generate) imports straight to the hero.
- **Header & nav** — sticky/shrink/transparent behaviors, flexbox nav control, off-canvas popout, mega menu, scheduled announcement bar.
- **Footer builder** — 1–4 columns, palettes, tagline, author credit.
- **Blocks & patterns** — Social Icons, Icon (Blade), Post Grid, GitHub repo blocks, 22 starter patterns + ~50 generated remix patterns, Pattern Library admin page.
- **Repofolio addon** — live GitHub repo grid, project case-study post type, OAuth connect; yields to the standalone plugin automatically.
- **Reading UX, forms, performance, SEO** — auto TOC, reading progress, plugin-free contact form, newsletter, cookie notice, split block CSS, critical CSS, local fonts (1,500+ Google families), OG/Twitter/JSON-LD (auto-off under Rank Math/Yoast), white-label + onboarding checklist.

### Settings, developer tools & support
- **Appearance → Pressroot** — one page, five tabs in the Repofolio docs-site design: **AI Models → Theme Settings → Site Types → GitHub → Support**, with build status bars on every generate.
- **Export / Import / Reset**, **WP-CLI suite** (`wp pressroot ...`), **Hook Registry**, **Dev Mode** debug panel.

## Requirements

| Tool | Version |
|---|---|
| PHP | 8.3+ |
| Composer | 2.x |
| Node | 20.19+ or 22.12+ |
| WordPress | 6.6+ |

## Quick start

```bash
composer install
npm install
npm run build      # or: npm run dev  (Vite HMR)
npm run wp         # zero-Docker local WordPress (Playground) at 127.0.0.1:8881
```

Activate **Pressroot**, open **Appearance → Pressroot**: pick AI models (the free defaults work with no keys), fill Theme Settings + Brand, save, apply a Site Type, then ✨ AI-write and 🖼 generate. Full walkthrough in the [documentation](https://matthummel-pa.github.io/pressroot/documentation.html); a field-by-field worked example lives in the [restaurant build recipe](docs/BUILD-RECIPE-RESTAURANT.md).

## Docs & history

- [Documentation site](https://matthummel-pa.github.io/pressroot/) — marketing site, docs, tester program, feedback form (served from [`docs/`](docs/))
- [THEME-SETTINGS.md](docs/THEME-SETTINGS.md) — every control, cataloged
- [ARCHITECTURE.md](docs/ARCHITECTURE.md) · [DEVELOPMENT.md](docs/DEVELOPMENT.md) · [BRAND-DESIGN-SYSTEM.md](docs/BRAND-DESIGN-SYSTEM.md)
- [BUILD-NOTES.md](docs/BUILD-NOTES.md) — the chronological build log (root cause → fix → takeaway)
- [CHANGELOG.md](CHANGELOG.md) — versioned release notes

## Contributing & testing

Beta testers get a free domain and keep their site — see [Become a tester](https://matthummel-pa.github.io/pressroot/testers.html). Code, site types, style kits, recipes, and translations are all welcome — see [Collaborate](https://matthummel-pa.github.io/pressroot/collaborate.html) and [CONTRIBUTING.md](CONTRIBUTING.md).

## License

MIT © 2024–2026 [Matt Hummel (matthummel)](https://github.com/matthummel-pa)
