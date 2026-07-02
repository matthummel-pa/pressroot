{{-- partials/home/repos.blade.php
     Static featured repos. Your theme already supports "live GitHub project
     pages" — swap this array for that data source (or the GitHub REST API
     /users/matthummel-pa/repos, cached via a transient) when ready. --}}
@php
  $repos = [
    ['name' => 'pressroot', 'dot' => '#7C5CFF', 'desc' => 'Premium Sage 11 (Roots) theme — Blade, a Customizer framework & a Gutenberg block + pattern library.', 'meta' => ['● PHP / Blade','Sage · Acorn','Vite'], 'url' => 'https://github.com/matthummel-pa/pressroot'],
    ['name' => 'tocflow',          'dot' => '#FF7A1A', 'desc' => 'A WordPress block plugin that auto-generates an accessible, server-rendered table of contents.', 'meta' => ['● PHP / JS','block.json','WCAG'], 'url' => 'https://github.com/matthummel-pa/tocflow'],
    ['name' => 'keepary',          'dot' => '#38BDF8', 'desc' => 'A full-stack web app designed and shipped end to end — a real, working product.', 'meta' => ['● JavaScript','Full-stack','★ 2'], 'url' => 'https://github.com/matthummel-pa/keepary'],
  ];
@endphp
<section class="prt-wrap" style="padding-top:80px; padding-bottom:30px;">
  <div style="display:flex; align-items:flex-end; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:32px;">
    <div>
      <h2 style="font-family:var(--font-display); font-weight:800; font-size:clamp(34px,4vw,46px); letter-spacing:-.025em; margin:0 0 8px; color:#1B1830;">Building in the <span style="color:#7C5CFF;">open</span></h2>
      <p style="margin:0; font-size:16px; color:#7C75A8;">16 repositories · 32 stars · contributor to the Microsoft 365 PnP community.</p>
    </div>
    <a href="https://github.com/matthummel-pa" target="_blank" rel="noopener" style="text-decoration:none; font-weight:700; color:#7C5CFF; font-size:16px;">@matthummel-pa ↗</a>
  </div>
  <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:18px;">
    @foreach($repos as $r)
      <a href="{{ $r['url'] }}" target="_blank" rel="noopener" class="prt-lift" style="text-decoration:none; color:inherit; background:#fff; border:1.5px solid #ECE4F8; border-radius:20px; padding:26px;">
        <div style="display:flex; align-items:center; gap:9px; font-family:var(--font-display); font-weight:700; font-size:17px; margin-bottom:10px; color:#1B1830;"><span style="color:{{ $r['dot'] }};">▸</span> {{ $r['name'] }}</div>
        <p style="font-size:14.5px; color:#5A5676; line-height:1.55; margin:0 0 18px;">{{ $r['desc'] }}</p>
        <div style="display:flex; gap:16px; font-family:var(--font-mono); font-size:12px; color:#7C75A8;">
          @foreach($r['meta'] as $m)<span>{{ $m }}</span>@endforeach
        </div>
      </a>
    @endforeach
  </div>
</section>
