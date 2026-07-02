{{-- partials/home/risk-reversal.blade.php --}}
@php
  $items = [
    ['c' => '#C2F23D', 'title' => 'Start small',       'desc' => "Begin with a paid audit or a single page. Scale up only once you're confident."],
    ['c' => '#38BDF8', 'title' => 'Fixed-price quotes', 'desc' => 'Approve a clear scope and price before any work begins. No surprise invoices.'],
    ['c' => '#FF7A1A', 'title' => 'You own everything',  'desc' => 'Code, content & accounts are yours from day one. No lock-in, ever.'],
    ['c' => '#C2F23D', 'title' => 'Built to last',       'desc' => 'Clean, documented code the next developer will actually thank you for.'],
  ];
@endphp
<section class="prt-wrap" style="padding-top:48px; padding-bottom:30px;">
  <div style="background:#1B1830; color:#fff; border-radius:34px; padding:54px 48px; position:relative; overflow:hidden;">
    <div style="position:absolute; top:-40px; right:-30px; width:200px; height:200px; background:#7C5CFF; opacity:.5; border-radius:50%; filter:blur(20px);"></div>
    <div style="position:relative;">
      <div style="font-family:var(--font-mono); font-size:13px; color:#C2F23D; letter-spacing:.1em; margin-bottom:14px;">NEW TO WORKING WITH ME?</div>
      <h2 style="font-family:var(--font-display); font-weight:800; font-size:40px; letter-spacing:-.025em; margin:0 0 36px; max-width:18em;">I make it easy &amp; low-risk to start.</h2>
      <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:30px;">
        @foreach($items as $i)
          <div>
            <div style="font-family:var(--font-display); font-weight:800; font-size:20px; color:{{ $i['c'] }}; margin-bottom:10px;">{{ $i['title'] }}</div>
            <p style="font-size:15px; color:#CFCBE6; line-height:1.55; margin:0;">{{ $i['desc'] }}</p>
          </div>
        @endforeach
      </div>
    </div>
  </div>
</section>
