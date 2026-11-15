@php
    $cards = [
        [
            'key' => 'gallery',
            'title' => __('admin.content.gallery.title'),
            'text' => __('admin.content.gallery.subtitle'),
            'count' => $counts['gallery'],
            'live' => $counts['gallery_live'],
            'href' => route('admin.content.gallery.index'),
            'create' => route('admin.content.gallery.create'),
            'create_label' => __('admin.gallery.create'),
        ],
        [
            'key' => 'posts',
            'title' => __('admin.content.posts.title'),
            'text' => __('admin.content.posts.subtitle'),
            'count' => $counts['posts'],
            'live' => $counts['posts_live'],
            'href' => route('admin.content.posts.index'),
            'create' => route('admin.content.posts.create'),
            'create_label' => __('admin.posts.create'),
        ],
        [
            'key' => 'pages',
            'title' => __('admin.content.pages.title'),
            'text' => __('admin.content.pages.subtitle'),
            'count' => $counts['pages'],
            'live' => $counts['pages_live'],
            'href' => route('admin.content.pages.index'),
            'create' => route('admin.content.pages.create'),
            'create_label' => __('admin.pages.create'),
        ],
    ];
@endphp

<x-admin-layout :title="__('admin.content.title')">
    @include('admin.content.nav')

    <p class="max-w-prose text-body-md text-ink-soft">{{ __('admin.content.subtitle') }}</p>

    <div class="mt-8 grid gap-4 lg:grid-cols-3">
        @foreach ($cards as $card)
            <section class="flex flex-col rounded-xl border border-line bg-canvas shadow-card p-6">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <h2 class="font-display text-heading-sm">{{ $card['title'] }}</h2>
                        <p class="mt-2 text-body-sm text-ink-muted">{{ $card['text'] }}</p>
                    </div>

                    <x-ui.admin-icon :name="$card['key']" class="text-ink-muted" />
                </div>

                <dl class="mt-5 flex gap-8 text-body-sm">
                    <div>
                        <dt class="label-upper text-ink-muted">{{ __('admin.content.total') }}</dt>
                        <dd class="tabular mt-1 font-display text-heading-md">{{ $card['count'] }}</dd>
                    </div>
                    <div>
                        <dt class="label-upper text-ink-muted">{{ __('admin.content.published') }}</dt>
                        <dd class="tabular mt-1 font-display text-heading-md">{{ $card['live'] }}</dd>
                    </div>
                </dl>

                <div class="mt-auto flex flex-wrap gap-3 pt-6">
                    <x-ui.button :href="$card['create']" size="compact">{{ $card['create_label'] }}</x-ui.button>
                    <x-ui.button :href="$card['href']" variant="secondary" size="compact">{{ __('admin.content.manage') }}</x-ui.button>
                </div>
            </section>
        @endforeach
    </div>

    <div class="mt-12 grid gap-6 lg:grid-cols-2">
        <section class="rounded-xl border border-line bg-canvas shadow-card p-6">
            <div class="flex items-center justify-between gap-4">
                <h2 class="font-display text-heading-sm">{{ __('admin.content.latest_posts') }}</h2>
                <a href="{{ route('admin.content.posts.index') }}" class="link text-body-sm">{{ __('admin.content.manage') }}</a>
            </div>

            @if ($latestPosts->isEmpty())
                <p class="mt-4 text-body-sm text-ink-muted">{{ __('admin.posts.empty') }}</p>
            @else
                <ul class="mt-4 divide-y divide-line">
                    @foreach ($latestPosts as $post)
                        <li class="flex items-center justify-between gap-4 py-3 text-body-sm">
                            <a href="{{ route('admin.content.posts.edit', $post->id) }}" class="truncate text-ink hover:underline">
                                {{ $post->title_fr }}
                            </a>
                            <x-ui.badge :tone="$post->status->badgeTone()">{{ $post->status->label() }}</x-ui.badge>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="rounded-xl border border-line bg-canvas shadow-card p-6">
            <div class="flex items-center justify-between gap-4">
                <h2 class="font-display text-heading-sm">{{ __('admin.content.latest_messages') }}</h2>
                <a href="{{ route('admin.messages.index') }}" class="link text-body-sm">{{ __('admin.messages.title') }}</a>
            </div>

            @if ($counts['messages_new'] > 0)
                <x-ui.alert type="warning" class="mt-4">
                    {{ trans_choice('admin.messages.new_count', $counts['messages_new']) }}
                </x-ui.alert>
            @endif

            @if ($latestMessages->isEmpty())
                <p class="mt-4 text-body-sm text-ink-muted">{{ __('admin.messages.empty') }}</p>
            @else
                <ul class="mt-4 divide-y divide-line">
                    @foreach ($latestMessages as $message)
                        <li class="flex items-center justify-between gap-4 py-3 text-body-sm">
                            <a href="{{ route('admin.messages.show', $message) }}" class="min-w-0 truncate text-ink hover:underline">
                                {{ $message->name }} — {{ $message->subject ?: __('admin.messages.no_subject') }}
                            </a>
                            <x-ui.badge :tone="$message->status->badgeTone()">{{ $message->status->label() }}</x-ui.badge>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
</x-admin-layout>
