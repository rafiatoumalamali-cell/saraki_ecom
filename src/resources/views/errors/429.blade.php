@extends('errors.layout')

@section('title', __('errors.throttled.title'))

@section('content')
    <p class="font-display text-display-lg text-gold-700">429</p>
    <h1 class="mt-4 font-display text-heading-lg">{{ __('errors.throttled.title') }}</h1>
    <p class="mt-4 text-body-md text-ink-soft">{{ __('errors.throttled.message') }}</p>

    <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
        <a href="{{ route('home', app()->getLocale()) }}" class="label-upper inline-flex h-11 items-center rounded-sm bg-gold px-5 text-charcoal transition-colors duration-200 ease-brand hover:bg-gold-600">
            {{ __('errors.home') }}
        </a>
        <a href="{{ route('contact.show', app()->getLocale()) }}" class="label-upper inline-flex h-11 items-center rounded-sm border border-charcoal px-5 text-charcoal transition-colors duration-200 ease-brand hover:bg-charcoal hover:text-beige">
            {{ __('errors.contact') }}
        </a>
    </div>
@endsection
