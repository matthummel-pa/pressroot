---
title: Settings reference
---

# matthummel theme â€” settings reference

Every setting the theme exposes, where to find it, and what it does. The theme is
built on **Sage 11 / Acorn**; all options write WordPress **theme mods** (or options,
where noted) and render with no build step.

Three places hold settings:

1. **Customize â†’ Theme Options** â€” the live, preview-as-you-edit panel.
2. **Appearance â†’ Theme Settings** â€” a tabbed admin page mirroring the most-used options.
3. **Appearance â†’ Theme Tools / Local Fonts** â€” utilities (presets, import/export, font hosting).

---

## Customizer â†’ Theme Options

### Colors
- **Brand / buttons** â€” primary green used for buttons, links, accents (`--color-green`).
- **Page background** â€” site background (`--color-khaki`).
- **Headings** â€” heading text color (`--color-ink`).
- **Body text** â€” body copy color (`--color-body`).

### Typography
- **Heading font** / **Body font** â€” pick from Geist, Bricolage Grotesque, Schibsted Grotesk, Space Grotesk, Sora, Inter Tight, Fraunces, Inter, Work Sans, or System.
- **Base font size** â€” root body size (15â€“19px).
- **Body line height** â€” 1.5 to 2.0.
- **Heading line height** â€” 1.0 to 1.3.
- **Heading letter spacing** â€” tighter â†’ wide.

### Typography (advanced)
- **Navigation font** / **Button font** â€” assign different families to the nav and buttons (loads only when set).
- **Heading / Body / Nav / Button weight** â€” per-element font weights.
- **Nav letter case** â€” normal / UPPERCASE / lowercase.
- **Body letter spacing** â€” tight / normal / loose.
- **Base font on tablet / mobile** â€” responsive overrides at â‰¤1024px and â‰¤600px.

### Extras
- **Underline content links** â€” underline links inside post/page content.
- **Button corner radius** â€” square â†’ pill.
- **Card corner radius** â€” 6â€“20px.
- **Text selection color** â€” `::selection` background.
- **Scroll-to-top button** â€” floating back-to-top control.

### Layout
- **Default content width** â€” global content max-width (standard presets).
- **Per type (Pages / Posts / Projects / Archives)** â€” width preset, custom width, and show-sidebar toggle for each content type.

### Header Layout
- **Full-width menu** â€” stretch the nav across the header.
- **Header width / height / gap** â€” sizing of the header row.
- **Element position** â€” order of logo, menu, social links, and button.
- **Header button** â€” show/hide, text, and URL of the header CTA.
- **Sticky header** â€” pin the header on scroll.
- **Shrink on scroll** â€” reduce header padding once scrolled (needs sticky).
- **Transparent overlay header** â€” off / front page only / all pages.

### Top Bar
- **Enable top bar**, **contact text**, **show social links**, **button text/URL**, **background** and **text color** (palette).

### Navigation
- Full flexbox control of the primary menu: **direction, justify, align, align-content, wrap, gap**.
- Menu item box/type: **padding, min-height, font-size, weight, transform, letter-spacing, radius, color, hover color**.

### Menu & Popout
- **Use menu icon on desktop / tablet / mobile** â€” where the hamburger replaces the inline nav.
- **Panel background** â€” solid or gradient (start, end, angle), **text / icon color**.
- **Popout width**, **menu columns** and **block columns** (desktop), plus **popout item styling** (align, padding, font, weight, transform, gap).

### Social Icons
- **Show social icons in navigation bar** + **position** (left / center / right).
- **Display** (text or icons), **size, shape, color, chip background, hover color**.
- **Social URLs** â€” LinkedIn, GitHub, Dev.to, X, Bluesky, YouTube, Instagram, Facebook, Mastodon, Email, RSS (each shows its Blade icon).

### Announcement Bar
- **Show bar**, **message**, **link text/URL**, **background/text color**, **dismissible** (remembered per visitor), **hide on mobile**, and optional **start/end dates** for scheduling.

### Hero
- **Copy** â€” editable **eyebrow**, **H1 title**, and **sub-paragraph** (clear a field to hide that element).
- **Layout** â€” **columns** (1â€“3: content + side image + 2nd image), **content position** (horizontal & vertical), **content max-width** + **spacing**, with **tablet/mobile** max-width overrides.
- **Flexbox (advanced)** â€” direction, justify, align, wrap, gap on the hero container.
- **Media** â€” **side image / illustration** (+ 2nd for 3-column), **image side**, or a **background cover image** with **overlay %** and **min-height**.
- **Image finder** (per image control) â€” search **Openverse** (no key), **Unsplash**/**Pexels** (optional keys â†’ fields in this section), or generate a free **AI** image; the pick is imported to the Media Library and set.
- **Entrance animation** â€” fade-up/in, zoom, pop, blur, slide.

### Animations
- **Enable on-scroll animations** (site-wide), **effect** (fade-up/in, zoom, pop, blur, slide), and **speed** (fast/normal/slow). Honours `prefers-reduced-motion`.

### Responsive (mobile & tablet)
- **Hide on mobile/tablet** â€” navigation social (mobile/desktop), top-bar social, top-bar button, navbar button (mobile + tablet), the "Menu" label, and shrink the logo on mobile.
- **Keep top bar on one line** (tablet), and **per-breakpoint inner widths** for the top bar, navbar, and message bar (tablet + mobile). Mobile = â‰¤640px, tablet = 641â€“1024px.

### Dark Mode
- **Enable dark mode toggle** and **default mode** (light / dark / auto by system).

### Footer & Header
- **Show social icons in footer**. *(Sticky header lives in Header Layout.)*
- **Footer background / text** â€” palette choice or custom hex.
- **Footer columns** â€” 1â€“4 (each maps to a block widget area under Appearance â†’ Widgets).
- **Footer tagline**.

### CTA & Intros
- Global **project CTA** plus intro text for the Projects and Contact templates.

### SEO & Schema
- **Output meta + schema** (auto-disables if Rank Math/Yoast is active).
- **Entity** â€” Person or Organization, **name**, **logo**, **default share image**, **Twitter/X handle**.
- Emits Open Graph, Twitter cards, and JSON-LD (Person/Org, WebSite, Article, BreadcrumbList).

### Performance
- Toggles: **disable emojis, oEmbed/wp-embed, jQuery Migrate, XML-RPC/pingbacks, dashicons (logged-out), wp_head cleanup, defer scripts**.
- **Preconnect domains** â€” comma-separated origins.

### Custom Code
- **Head / Body / Footer code** injection (analytics, pixels, verification) and a **Custom CSS** box.

### Newsletter
- **Form action URL** (Mailchimp), **heading**, **sub-note**, **button text** â€” rendered by `[prt_newsletter]`.

### Cookie Notice
- **Show notice**, **message**, **accept button**, **policy link** (URL + text). Dismissal remembered locally.

### White Label
- **Login logo**, **login background**, **admin footer text**, and **"Get started" dashboard widget** toggle.

---

## Appearance â†’ Theme Settings (admin tabs)

A tabbed panel mirroring the most common options for quick edits:

- **General** â€” header button text/URL, show button, footer tagline.
- **Design** â€” colors, heading/body fonts, default content width.
- **Layout** â€” per-type width preset, custom width, sidebar.
- **Header** â€” full-width menu, sizing, element order, top bar, menu-icon breakpoints.
- **Footer** â€” show social, columns, background/text colors, tagline, sticky header.
- **Projects** â€” default GitHub owner, API token, data cache (hours), **OAuth Client ID**, and **Connect with GitHub** (device-flow login).

---

## Appearance â†’ utilities

### Theme Tools
- **Style Kits** â€” one-click palette + font + radius presets (Editorial, Sage Classic, Warm Sand, Midnight, Mono Slate).
- **Export / Import** â€” download all theme settings as JSON and re-import elsewhere.
- **Reset** â€” return to defaults.

### Local Fonts
- **Download fonts now** â€” fetch the active families' woff2 into `uploads/prt-fonts/`.
- **Serve fonts locally** â€” use the local stylesheet and remove every Google Fonts request + preconnect.

---

## Blocks (in the inserter, "Matt Hummel" category)

- **Social Icons** â€” inline Blade SVG social icons; pulls from site social links by default, fully styleable (size, shape, brand/mono, colors, hover, alignment).
- **Icon (Blade)** â€” drop in any Blade icon by name (`si-â€¦`, `heroicon-o-â€¦`, `lucide-â€¦`, `prt-â€¦`), with size, color, alignment.
- **Post Grid** â€” query posts/projects/pages into a responsive card grid (columns, count, order, image/excerpt/date/category toggles).
- **Patterns** â€” Hero, Pricing, Testimonials, Logo cloud, Feature grid, Callout, CTA band, Stat strip, FAQ.

## Shortcodes
- `[prt_newsletter]` â€” newsletter signup form (Customizer â†’ Newsletter).
- `[prt_breadcrumbs]` â€” breadcrumb trail with BreadcrumbList schema.

## Per-project (Projects â†’ edit â†’ "Project Details")
- **GitHub owner / repo**, **eyebrow**, **demo URL** â€” drive the live GitHub data section on each project.

---

## Notes for developers
- All front-end overrides are emitted via the `prt_head_end` action, which fires after the
  built stylesheet, so settings win without `!important` gymnastics.
- Icons use **Blade Icons** (Simple Icons `si-`, Heroicons `heroicon-o-`/`-s-`, Lucide `lucide-`,
  and a local `prt-` set in `resources/svg/`).
- Module files live in `app/*.php` and are registered in `functions.php`.