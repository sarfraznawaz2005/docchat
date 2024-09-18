<div class="sidebar" id="sidebar">
    <h2>
        <a href="{{ route('doctalk.admin') }}" wire:navigate.hover style="color:inherit; text-decoration: none; font-size: 16px;">
            Administration
        </a>
    </h2>
    <ul>
        <li class="{{ Request::routeIs('doctalk.admin*') ? 'active' : '' }}">
            <a href="{{ route('doctalk.admin') }}"
               wire:navigate.hover>
                Dashboard
            </a>
        </li>
        <li class="{{ Request::routeIs('doctalk.docs*') ? 'active' : '' }}">
            <a href="{{ route('doctalk.docs') }}"
               wire:navigate.hover>
                Manage Documents
            </a>
        </li>
        <li class="{{ Request::routeIs('doctalk.setting*') ? 'active' : '' }}">
            <a href="{{ route('doctalk.settings') }}"
               wire:navigate.hover>
                Settings
            </a>
        </li>
    </ul>
</div>
