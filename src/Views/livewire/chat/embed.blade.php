<div class="doctalk">
    <link rel="stylesheet" href="{{ asset('vendor/doctalk/assets/code-highlight-light-plus-theme.css') }}"/>
    <link rel="stylesheet" href="{{ asset('vendor/doctalk/assets/doctalk.css') }}"/>
    <script defer src="{{ asset('vendor/doctalk/assets/doctalk.js') }}"></script>

    <div class="chat-container">

        <div class="main-content" id="main-content">
            <div class="topbar">
                <h3>Chat With Documents</h3>

                <div class="alignCenter flex">
                    <span style="margin-right: 10px;">@yield('topbar')</span>
                    <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
                </div>
            </div>

            <livewire:doctalk.chat/>
        </div>
    </div>
</div>
