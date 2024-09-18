<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'DocTalk' }}</title>

    <link rel="stylesheet" href="{{ asset('vendor/doctalk/assets/code-highlight-light-plus-theme.css') }}"/>
    <link rel="stylesheet" href="{{ asset('vendor/doctalk/assets/doctalk.css') }}"/>
    <script defer src="{{ asset('vendor/doctalk/assets/doctalk.js') }}"></script>

    @yield('styles')
</head>
<body>

{{--<x-doctalk::autoloading/>--}}

<div class="chat-container">

    <div class="main-content" id="main-content">
        <div class="topbar">
            <h3>{{ $title ?? 'DocTalk' }}</h3>

            <div class="alignCenter flex">
                <span style="margin-right: 10px;">@yield('topbar')</span>
                <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
            </div>
        </div>

        {{--<livewire:doctalk.offline/>--}}

        {{ $slot }}
    </div>
</div>

<x-doctalk::flash-message/>
<x-doctalk::page-expired/>
<x-doctalk::loading/>

@yield('scripts')

</body>
</html>
