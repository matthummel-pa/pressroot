{{-- partials/home/hero.blade.php --}}
@php
  $eyebrow   = get_theme_mod('prt_avail_text', 'Open to side projects · 2 slots');
  $avail     = (bool) get_theme_mod('prt_avail_open', true);
  $headline  = 'Hi there! I\'m Matt — I build';        // edit copy here
  $sub       = 'Full-stack developer with 15+ years building fast, accessible WordPress & Sage sites and Microsoft Power Platform tools — from Gettysburg, PA.';
  $portrait  = get_theme_mod('prt_hero_portrait'); // attachment URL
@endphp

<section style="position:relative; overflow:hidden; padding:70px 32px 80px; background:#FFFDF7;">
  <div style="position:absolute; top:-60px; left:-40px; width:340px; height:340px; background:radial-gradient(circle at 30% 30%,#7C5CFF,#38BDF8); filter:blur(10px); opacity:.32; animation:prt-drift 16s ease-in-out infinite;"></div>
  <div style="position:absolute; bottom:-80px; right:60px; width:300px; height:300px; background:radial-gradient(circle at 60% 40%,#FF7A1A,#FF5DA2); filter:blur(14px); opacity:.28; animation:prt-drift 20s ease-in-out infinite reverse;"></div>

  <div class="prt-wrap" style="position:relative; padding:0;">
    <div style="display:grid; grid-template-columns:1.3fr 0.9fr; gap:40px; align-items:center;">
      <div style="animation:prt-fadeUp .7s ease both;">
        @if($avail)
          <div style="display:inline-flex; align-items:center; gap:9px; background:#1B1830; color:#C2F23D; padding:9px 18px; border-radius:999px; font-size:13px; font-weight:700; margin-bottom:28px; font-family:var(--font-display);">
            <span style="width:8px; height:8px; border-radius:50%; background:#C2F23D; animation:prt-bob 1.6s ease-in-out infinite;"></span> {{ $eyebrow }}
          </div>
        @endif
        <h1 style="font-family:var(--font-display); font-weight:800; font-size:clamp(48px,7vw,88px); line-height:.96; letter-spacing:-.035em; margin:0 0 24px; color:#1B1830;">
          {{ $headline }}<br>
          <span class="prt-gradient-text">delightful</span>
          <span style="font-family:var(--font-serif); font-style:italic; font-weight:400;">things</span> for the web.
        </h1>
        <p style="font-family:var(--font-display); font-size:21px; line-height:1.5; max-width:30em; color:#4A4660; margin:0 0 34px;">{{ $sub }}</p>
        <div style="display:flex; gap:14px; flex-wrap:wrap;">
          <a href="{{ get_post_type_archive_link('projects') ?: '#work' }}" class="prt-lift" style="text-decoration:none; background:#1B1830; color:#fff; padding:17px 30px; border-radius:999px; font-weight:700; font-size:16px; font-family:var(--font-display);">See my work →</a>
          <a href="{{ get_permalink(get_page_by_path('contact')) ?: '#contact' }}" class="prt-lift" style="text-decoration:none; background:#fff; border:1.5px solid #1B1830; color:#1B1830; padding:17px 30px; border-radius:999px; font-weight:700; font-size:16px; font-family:var(--font-display);">Let's chat</a>
        </div>
      </div>

      <div style="position:relative; height:440px;">
        <div style="position:absolute; inset:0; margin:auto; width:300px; height:380px; border:2px solid #1B1830; border-radius:28px; overflow:hidden; display:flex; align-items:flex-end; padding:18px; animation:prt-blob 12s ease-in-out infinite; {{ $portrait ? "background:url('".esc_url($portrait)."') center/cover;" : "background:repeating-linear-gradient(135deg,#efe9ff 0 16px,#f6f1ff 16px 32px);" }}">
          @unless($portrait)<span style="font-family:var(--font-mono); font-size:12px; color:#7C75A8;">[ portrait.jpg ]</span>@endunless
        </div>
        <div style="position:absolute; top:8px; right:14px; background:#C2F23D; color:#1B1830; padding:12px 16px; border-radius:16px; font-weight:800; font-size:15px; transform:rotate(8deg); box-shadow:0 8px 22px rgba(27,24,48,.16); animation:prt-floatA 5s ease-in-out infinite; font-family:var(--font-display);">⚡ Ships fast</div>
        <div style="position:absolute; bottom:24px; left:0; background:#FF7A1A; color:#fff; padding:12px 16px; border-radius:16px; font-weight:800; font-size:15px; transform:rotate(-6deg); box-shadow:0 8px 22px rgba(27,24,48,.16); animation:prt-floatB 6s ease-in-out infinite; font-family:var(--font-display);">15+ yrs building</div>
        <div style="position:absolute; bottom:90px; right:-6px; width:54px; height:54px; border-radius:50%; background:#38BDF8; animation:prt-floatA 7s ease-in-out infinite;"></div>
      </div>
    </div>
  </div>
</section>
