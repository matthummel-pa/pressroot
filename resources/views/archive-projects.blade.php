@extends('layouts.app')

@section('content')
  <div class="page-header container">
    <h1 class="display-title is-hero">{{ __('Projects', 'pressroot') }}</h1>
    @php($prtProjIntro = get_theme_mod('prt_projects_intro', ''))
    @if ($prtProjIntro)
      <div class="archive-desc">{!! wp_kses_post($prtProjIntro) !!}</div>
    @endif
  </div>

  <div class="container">
    @php($prtTerms = get_terms(['taxonomy' => 'project_categories', 'hide_empty' => false]))
    @if (! is_wp_error($prtTerms) && $prtTerms)
      <div class="project-filters">
        <button type="button" class="project-filter-btn is-active" data-filter="*">{{ __('All', 'pressroot') }}</button>
        @foreach ($prtTerms as $prtT)
          <button type="button" class="project-filter-btn" data-filter="{{ $prtT->slug }}">{{ $prtT->name }}</button>
        @endforeach
      </div>
    @endif

    @if (have_posts())
      <div class="project-grid" id="prt-project-grid">
        @while(have_posts()) @php(the_post())
          @php($prtPterms = wp_get_post_terms(get_the_ID(), 'project_categories', ['fields' => 'slugs']))
          <a class="project-card" href="{{ get_permalink() }}" data-terms="{{ is_array($prtPterms) ? implode(' ', $prtPterms) : '' }}">
            @if (has_post_thumbnail())
              {!! get_the_post_thumbnail(null, 'medium_large', ['loading' => 'lazy']) !!}
            @endif
            <h2>{!! get_the_title() !!}</h2>
            <p>{{ wp_trim_words(get_the_excerpt(), 16) }}</p>
          </a>
        @endwhile
      </div>
    @else
      <p class="archive-desc">{{ __('No projects yet â€” check back soon.', 'pressroot') }}</p>
    @endif
  </div>

  <script>
  (function(){
    var btns = document.querySelectorAll('.project-filter-btn');
    var cards = document.querySelectorAll('#prt-project-grid .project-card');
    btns.forEach(function(b){
      b.addEventListener('click', function(){
        var f = b.getAttribute('data-filter');
        btns.forEach(function(x){ x.classList.toggle('is-active', x === b); });
        cards.forEach(function(c){
          var t = (c.getAttribute('data-terms') || '').split(' ');
          c.style.display = (f === '*' || t.indexOf(f) > -1) ? '' : 'none';
        });
      });
    });
  })();
  </script>
@endsection
