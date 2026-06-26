<p align="center">
  <img src="brand/pressroot-logo.svg" alt="Pressroot" width="380">
</p>

<h1 align="center">Pressroot</h1>

<p align="center"><strong>A premium Sage 11 (Roots) WordPress theme framework.</strong><br>
Server-rendered, accessible, deliberately light on plugins — with a deep, point-and-click options system.</p>

<p align="center">Stack: Sage 11 · Blade · Tailwind CSS v4 · Vite · Acorn (Laravel-in-WordPress) · PHP 8.3</p>

---

> **Experimental / marketable build.** Pressroot is a rebrandable productization of a bespoke Sage theme — a starting point for a commercial premium theme. The internals (`prt_` prefix, `prt-` CSS classes, `pressroot` text domain) are namespaced for clean distribution.

## Features

### Design system & theming
- **Tokens-first design** — colors, type scale, spacing as Tailwind v4 `@theme` variables; re-skin the whole site from one place.
- **Style Kits** — one-click presets (Editorial, Classic, Warm Sand, Midnight, Mono Slate).
- **Colors / Typography** — brand + surfaces, 10 font families, base size, line-heights, letter-spacing, plus advanced per-element weights and responsive base sizes.
- **Dark mode** — light / dark / auto with no-flash loading and a dedicated dark navbar surface.
- **`theme.json`** — unlocks spacing, border, shadow, fluid typography, gradient, and duotone controls in the editor.

### Hero & animations
- **Hero builder** — editable eyebrow / H1 / sub-paragraph, 1–3 columns (content + side/2nd image), content position (H/V), max-width + spacing with tablet/mobile overrides, and full flexbox control.
- **Hero media** — side image / illustration, or a background cover image with overlay + min-height.
- **Built-in image finder** — search **Openverse** (no key), **Unsplash** and **Pexels** (optional keys), or **generate an AI image** (free, no key) right in the Customizer; the pick imports into the Media Library and sets the hero image.
- **On-scroll animations** — site-wide reveal (fade/zoom/pop/blur/slide) with speed + a hero entrance animation; respects `prefers-reduced-motion`.

### Header, nav, footer & responsive
- **Header Layout** — full-width menu, sizing, element placement + alignment, bar order, sticky / shrink / transparent behaviors, header CTA.
- **Navigation** — full flexbox control + per-item styling; off-canvas popout with breakpoints, panel style, columns, item styling; menu-item icons + mega menu.
- **Social Icons** — appearance + account URLs in one place; **Top bar** + **scheduled announcement bar**.
- **Footer builder** — 1–4 widget-area columns, palette/custom colors, tagline.
- **Responsive controls** — per-device hide toggles (social, buttons, "Menu" label, logo shrink) and per-breakpoint widths. Mobile ≤640px · tablet 641–1024px.

### Blocks, content & performance
- **Blocks** — Social Icons, Icon (Blade), Post Grid, GitHub repo card/grid/stats/releases + 17 starter patterns.
- **Live GitHub project pages** — repo metadata, stars/forks, latest release, README intro (cached); device-flow "Connect with GitHub".
- **Plugin-free contact form**, newsletter, cookie notice, code injection.
- **Performance** — disable bloat, self-host fonts, split block CSS, critical CSS, Prism highlighting.
- **SEO** — Open Graph, Twitter, JSON-LD; auto-off under Rank Math/Yoast.
- **Agency** — white-label login, dashboard widget, one-click starter pages.

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
```

Activate **Pressroot**, then open **Appearance → Theme Tools** to apply a Style Kit, and fine-tune under **Customize → Theme Options**.

## License

MIT © Matt Hummel
