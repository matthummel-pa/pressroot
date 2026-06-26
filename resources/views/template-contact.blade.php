{{--
  Template Name: Contact
--}}

@extends('layouts.app')

@section('content')
  @include('partials.page-header')

  <div class="contact-wrap">
    @php($prtStatus = isset($_GET['contact']) ? sanitize_key($_GET['contact']) : '')
    @if ($prtStatus === 'success')
      <p class="form-success">{{ __("Thanks â€” your message has been sent. I'll get back to you soon.", 'pressroot') }}</p>
    @elseif ($prtStatus === 'error')
      <p class="form-error">{{ __('Sorry, something went wrong. Please check the fields and try again.', 'pressroot') }}</p>
    @endif

    @php($prtContactIntro = get_theme_mod('prt_contact_intro', ''))
    @if ($prtContactIntro)
      <div class="entry-content post-prose">{!! wp_kses_post(wpautop($prtContactIntro)) !!}</div>
    @endif

    @if (get_the_content())
      <div class="entry-content post-prose">
        @php(the_content())
      </div>
    @endif

    <form class="contact-form" method="post" action="">
      @php(wp_nonce_field('prt_contact', 'prt_contact_nonce'))
      <input type="hidden" name="action" value="prt_contact">
      <p class="hp"><label>{{ __('Leave this field empty', 'pressroot') }}<input type="text" name="prt_hp" tabindex="-1" autocomplete="off"></label></p>

      <div class="field">
        <label for="cf-name">{{ __('Name', 'pressroot') }}</label>
        <input id="cf-name" type="text" name="prt_name" required>
      </div>
      <div class="field">
        <label for="cf-email">{{ __('Email', 'pressroot') }}</label>
        <input id="cf-email" type="email" name="prt_email" required>
      </div>
      <div class="field">
        <label for="cf-subject">{{ __('Subject', 'pressroot') }}</label>
        <input id="cf-subject" type="text" name="prt_subject">
      </div>
      <div class="field">
        <label for="cf-message">{{ __('Message', 'pressroot') }}</label>
        <textarea id="cf-message" name="prt_message" rows="6" required></textarea>
      </div>
      <button class="btn" type="submit">{{ __('Send message', 'pressroot') }}</button>
    </form>
  </div>
@endsection
