<div>
    <ul class="nav nav-underline nav-underline-primary nav-underline-text-dark nav-underline-gap-x-0" id="tabMyProfileBottom" role="tablist">
        <li class="nav-item ms-1" role="presentation">
            <a wire:click="setTab('overview')"
               class="nav-link py-3 border-3 text-black {{ $activeTab === 'overview' ? 'active' : '' }}"
               style="cursor: pointer">
                Overview
            </a>
        </li>
        <li class="nav-item ms-1" role="presentation">
            <a wire:click="setTab('settings')"
               class="nav-link py-3 border-3 text-black {{ $activeTab === 'settings' ? 'active' : '' }}"
               style="cursor: pointer">
                Settings
            </a>
        </li>
        {{-- ... other tabs ... --}}
    </ul>

    {{-- Content sections --}}
    <div class="tab-content mt-4">
        @if($activeTab === 'overview')
            <div>
                {{-- Overview content --}}
                Overview content here
            </div>
        @elseif($activeTab === 'settings')
            <div>
                {{-- Settings content --}}
                Settings content here
            </div>
        @endif
        {{-- ... other tab contents ... --}}
    </div>
</div>
