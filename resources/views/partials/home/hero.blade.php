{{-- partials/home/hero.blade.php --}}
@php
  $eyebrow   = get_theme_mod('prt_avail_text', 'Open to side projects · 2 slots');
  $avail     = (bool) get_theme_mod('prt_avail_open', true);
  $headline  = get_theme_mod('prt_hero_title', "Hi there! I'm Matt — I build");
  $sub       = get_theme_mod('prt_hero_subtext', 'Full-stack developer with 15+ years building fast, accessible WordPress & Sage sites and Microsoft Power Platform tools — from Gettysburg, PA.');
  $portrait  = get_theme_mod('prt_hero_portrait'); // attachment URL
@endphp

{{-- Repofolio docs-site hero treatment (matthummel-pa.github.io/repofolio):
     dark #201B3A -> #15122a ground with iris/pink radial glows, mono
     uppercase eyebrow, gradient display headline, lavender-white lead,
     gradient + ghost pill CTAs. Pressroot's floaty portrait column kept —
     the accent chips pop even harder on the dark ground. --}}
<section style="position:relative; overflow:hidden; padding:70px 32px 80px; color:#fff; background:radial-gradient(1200px 500px at 80% -10%, rgba(108,76,241,.35), transparent 60%), radial-gradient(900px 500px at 10% 10%, rgba(255,77,157,.22), transparent 55%), linear-gradient(180deg,#201B3A,#15122a);">
  <div class="prt-wrap" style="position:relative; padding:0;">
    <div style="display:grid; grid-template-columns:1.3fr 0.9fr; gap:40px; align-items:center;">
      <div style="animation:prt-fadeUp .7s ease both;">
        @if($avail)
          <div style="display:inline-flex; align-items:center; gap:9px; font-family:var(--font-mono); letter-spacing:2px; text-transform:uppercase; font-size:13px; font-weight:600; color:#b9a7ff; margin-bottom:26px;">
            <span style="width:8px; height:8px; border-radius:50%; background:#37E29A; animation:prt-bob 1.6s ease-in-out infinite;"></span> {{ $eyebrow }}
          </div>
        @endif
        <h1 style="font-family:var(--font-display); font-weight:800; font-size:clamp(48px,7vw,88px); line-height:.98; letter-spacing:-.03em; margin:0 0 24px; color:#fff;">
          {{ $headline }}<br>
          <span style="background:linear-gradient(90deg,#C9B8FF,#FF9DC4,#FFC08A); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; color:transparent;">delightful</span>
          <span style="font-family:var(--font-serif); font-style:italic; font-weight:400;">things</span> for the web.
        </h1>
        <p style="font-family:var(--font-display); font-size:20px; line-height:1.55; max-width:30em; color:#e2ddf5; margin:0 0 34px;">{{ $sub }}</p>
        <div style="display:flex; gap:14px; flex-wrap:wrap;">
          <a href="{{ get_post_type_archive_link('projects') ?: '#work' }}" class="prt-lift prt-btn-grad" style="padding:17px 30px; font-size:16px; font-family:var(--font-display);">See my work →</a>
          <a href="{{ get_permalink(get_page_by_path('contact')) ?: '#contact' }}" class="prt-lift prt-btn-ghost-dark" style="padding:17px 30px; font-size:16px; font-family:var(--font-display);">Let's chat</a>
        </div>
      </div>

      <div style="position:relative; height:440px;">
        <div style="position:absolute; inset:0; margin:auto; width:300px; height:380px; border:2px solid rgba(255,255,255,.22); border-radius:28px; overflow:hidden; display:flex; align-items:flex-end; padding:18px; animation:prt-blob 12s ease-in-out infinite; {{ $portrait ? "background:url('".esc_url($portrait)."') center/cover;" : "background:repeating-linear-gradient(135deg,rgba(108,76,241,.30) 0 16px,rgba(255,77,157,.18) 16px 32px);" }}">
          @unless($portrait)<span style="font-family:var(--font-mono); font-size:12px; color:#b9a7ff;">[ portrait.jpg ]</span>@endunless
        </div>
        <div style="position:absolute; top:8px; right:14px; background:#37E29A; color:#17151F; padding:12px 16px; border-radius:16px; font-weight:800; font-size:15px; transform:rotate(8deg); box-shadow:0 8px 22px rgba(0,0,0,.35); animation:prt-floatA 5s ease-in-out infinite; font-family:var(--font-display);">⚡ Ships fast</div>
        <div style="position:absolute; bottom:24px; left:0; background:#FF7A3D; color:#fff; padding:12px 16px; border-radius:16px; font-weight:800; font-size:15px; transform:rotate(-6deg); box-shadow:0 8px 22px rgba(0,0,0,.35); animation:prt-floatB 6s ease-in-out infinite; font-family:var(--font-display);">15+ yrs building</div>
        <div style="position:absolute; bottom:90px; right:-6px; width:54px; height:54px; border-radius:50%; background:#22CFEE; animation:prt-floatA 7s ease-in-out infinite;"></div>
      </div>
    </div>
  </div>
</section>
