@if (is_singular('projects'))
  @include('partials.cta')
@endif

@php
  $prtFoot = \App\prt_footer();
  $prtFootSoc = \App\prt_social_links();
  $cols = max(1, min(4, (int) get_theme_mod('prt_footer_cols', 3)));
  $hasFooterWidgets = false;
  for ($i = 1; $i <= $cols; $i++) {
    if (is_active_sidebar("footer-{$i}")) { $hasFooterWidgets = true; break; }
  }
@endphp
<footer class="content-info">
  @if ($hasFooterWidgets)
    <div class="footer-widgets footer-widgets--cols-{{ $cols }}">
      @for ($i = 1; $i <= $cols; $i++)
        <div class="footer-col">
          @if (is_active_sidebar("footer-{$i}"))
            @php(dynamic_sidebar("footer-{$i}"))
          @endif
        </div>
      @endfor
    </div>
  @endif

  @if (is_active_sidebar('sidebar-footer'))
    @php(dynamic_sidebar('sidebar-footer'))
  @endif

  @if ($prtFoot['show_social'] && $prtFootSoc)
    <div class="footer-socials">
      @foreach ($prtFootSoc as $s)
        <a href="{{ esc_url($s['url']) }}" aria-label="{{ $s['label'] }}" rel="me noopener" target="_blank">{!! \App\prt_social_icon($s['key']) !!}</a>
      @endforeach
    </div>
  @endif

  @php($prtFooterText = apply_filters('matthummel/footer_text', ''))
  @if ($prtFooterText)
    <p class="footer-tagline">{!! wp_kses_post($prtFooterText) !!}</p>
  @endif

  <p>&copy; {{ date('Y') }} {{ $siteName }}. {{ __('Built with Sage.', 'pressroot') }}</p>
</footer>
