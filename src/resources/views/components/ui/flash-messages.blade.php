@php
    $flashes = [
        'success' => session('success'),
        'info' => session('info'),
        'warning' => session('warning'),
        'danger' => session('error') ?? session('danger'),
    ];
@endphp

@foreach ($flashes as $type => $message)
    @if ($message)
        <x-ui.alert :type="$type" class="mb-6">{{ $message }}</x-ui.alert>
    @endif
@endforeach
