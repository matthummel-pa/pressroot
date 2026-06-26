{{--
  Template Name: Services
--}}

@extends('layouts.app')

@section('content')
  @include('partials.page-header')

  <div class="container">
    @if (get_the_content())
      <div class="entry-content post-prose">@php(the_content())</div>
    @endif

    <div class="card-grid services-grid">
      <div class="service-card">
        <h3>{{ __('Web Development', 'pressroot') }}</h3>
        <p>{{ __('Fast, accessible sites and web apps â€” modern front-end, performance, and SEO.', 'pressroot') }}</p>
      </div>
      <div class="service-card">
        <h3>{{ __('WordPress Development', 'pressroot') }}</h3>
        <p>{{ __('Custom themes, Gutenberg blocks, and code-first Sage builds without page-builder bloat.', 'pressroot') }}</p>
      </div>
      <div class="service-card">
        <h3>{{ __('Power Platform', 'pressroot') }}</h3>
        <p>{{ __('Power Apps, Power Automate, and Dataverse solutions across Microsoft 365.', 'pressroot') }}</p>
      </div>
    </div>
  </div>

  @include('partials.cta')
@endsection
