<aside id="left-panel" class="tl-sidebar">
    <div class="tl-brand">
        <a href="/admin/dashboard">
            <img class="tl-logo-full" src="{{ asset('admin/images/logo.png') }}" alt="Logo">
            <img class="tl-logo-mini" src="{{ asset('admin/images/logo2.png') }}" alt="Logo">
        </a>
    </div>

    <div class="tl-scroll">
        <ul class="tl-nav">

            {{-- Dashboard --}}
            <li class="tl-item {{ request()->is('admin/dashboard') || request()->is('/') ? 'active' : '' }}">
                <a href="/admin/dashboard" title="Dashboard">
                    <span class="tl-ico"><i class="fa fa-dashboard"></i></span><span class="tl-txt">Dashboard</span>
                </a>
            </li>

            {{-- Warehouse --}}
            @can('view-warehouse')
            <li class="tl-title">Warehouse</li>
            <li class="tl-item {{ request()->routeIs('cargo-items.*') && !request()->routeIs('cargo-items.arrived-bd') ? 'active' : '' }}">
                <a href="{{ route('cargo-items.index') }}" title="China Warehouse">
                    <span class="tl-ico"><i class="fa fa-cubes"></i></span><span class="tl-txt">China Warehouse</span>
                </a>
            </li>
            <li class="tl-item {{ request()->routeIs('cargo-items.arrived-bd') ? 'active' : '' }}">
                <a href="{{ route('cargo-items.arrived-bd') }}" title="BD Warehouse">
                    <span class="tl-ico"><i class="fa fa-building"></i></span><span class="tl-txt">BD Warehouse</span>
                </a>
            </li>
            <li class="tl-item {{ request()->routeIs('warehouse-bills.*') ? 'active' : '' }}">
                <a href="{{ route('warehouse-bills.index') }}" title="Warehouse Bill">
                    <span class="tl-ico"><i class="fa fa-file-text-o"></i></span><span class="tl-txt">Warehouse Bill</span>
                </a>
            </li>
            @endcan

            {{-- Operations --}}
            @can('view-shipments')
            <li class="tl-title">Operations</li>
            <li class="tl-item {{ request()->routeIs('itemsshipments.*') ? 'active' : '' }}">
                <a href="{{ route('itemsshipments.index') }}" title="Shipments">
                    <span class="tl-ico"><i class="fa fa-ship"></i></span><span class="tl-txt">Shipments</span>
                </a>
            </li>

            {{-- Old Shipments (dropdown) --}}
            @php
                $oldOpen = request()->routeIs('old-shipments.*', 'old-customers.*', 'old-payments.*');
            @endphp
            <li class="tl-item tl-has-sub {{ $oldOpen ? 'active open' : '' }}">
                <a href="#" class="tl-toggle" title="Old Shipments" aria-expanded="{{ $oldOpen ? 'true' : 'false' }}">
                    <span class="tl-ico"><i class="fa fa-history"></i></span>
                    <span class="tl-txt">Old Shipments</span>
                    <span class="tl-caret"><i class="fa fa-angle-down"></i></span>
                </a>
                <ul class="tl-sub">
                    <li class="{{ request()->routeIs('old-shipments.*') ? 'active' : '' }}">
                        <a href="{{ route('old-shipments.index') }}"><i class="fa fa-list"></i> Shipments</a>
                    </li>
                    <li class="{{ request()->routeIs('old-customers.*') ? 'active' : '' }}">
                        <a href="{{ route('old-customers.index') }}"><i class="fa fa-users"></i> Customers</a>
                    </li>
                    <li class="{{ request()->routeIs('old-payments.*') && !request()->routeIs('old-payments.report') ? 'active' : '' }}">
                        <a href="{{ route('old-payments.index') }}"><i class="fa fa-money"></i> Payments &amp; Collections</a>
                    </li>
                    <li class="{{ request()->routeIs('old-payments.report') ? 'active' : '' }}">
                        <a href="{{ route('old-payments.report') }}"><i class="fa fa-bar-chart"></i> Monthly Report</a>
                    </li>
                </ul>
            </li>
            @endcan

            {{-- Reports --}}
            @canany(['view-reports', 'view-legacy-uploads'])
            <li class="tl-title">Reports</li>
            @can('view-reports')
            <li class="tl-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <a href="{{ route('reports.dashboard') }}" title="Dashboard Reports">
                    <span class="tl-ico"><i class="fa fa-bar-chart"></i></span><span class="tl-txt">Dashboard Reports</span>
                </a>
            </li>
            @endcan
            @can('view-legacy-uploads')
            <li class="tl-item {{ request()->routeIs('legacy.*') ? 'active' : '' }}">
                <a href="{{ route('legacy.index') }}" title="Legacy Uploads">
                    <span class="tl-ico"><i class="fa fa-upload"></i></span><span class="tl-txt">Legacy Uploads</span>
                </a>
            </li>
            @endcan
            @endcanany

            {{-- Accounting --}}
            <li class="tl-title">Accounting</li>
            <li class="tl-item {{ request()->routeIs('ledger.*') ? 'active' : '' }}">
                <a href="{{ route('ledger.index') }}" title="Customer Ledger">
                    <span class="tl-ico"><i class="fa fa-book"></i></span><span class="tl-txt">Customer Ledger</span>
                </a>
            </li>
            <li class="tl-item {{ request()->routeIs('office-bills.*') ? 'active' : '' }}">
                <a href="{{ route('office-bills.index') }}" title="Office Bills">
                    <span class="tl-ico"><i class="fa fa-file-text"></i></span><span class="tl-txt">Office Bills</span>
                </a>
            </li>

            {{-- Organization --}}
            <li class="tl-title">Organization</li>
            <li class="tl-item {{ request()->routeIs('offices.*') ? 'active' : '' }}">
                <a href="{{ route('offices.index') }}" title="Offices">
                    <span class="tl-ico"><i class="fa fa-building-o"></i></span><span class="tl-txt">Offices</span>
                </a>
            </li>
            <li class="tl-item {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                <a href="{{ route('employees.index') }}" title="Employees">
                    <span class="tl-ico"><i class="fa fa-id-badge"></i></span><span class="tl-txt">Employees</span>
                </a>
            </li>

            {{-- Master data --}}
            @canany(['view-users', 'view-roles', 'view-permissions', 'view-customers', 'view-shipping-mark', 'view-agents', 'view-cost-types'])
            <li class="tl-title">Master data</li>
            @endcanany

            @can('view-customers')
            <li class="tl-item {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                <a href="{{ route('customers.index') }}" title="Customers">
                    <span class="tl-ico"><i class="fa fa-users"></i></span><span class="tl-txt">Customers</span>
                </a>
            </li>
            @endcan

            @can('view-shipping-mark')
            <li class="tl-item {{ request()->routeIs('customer-extra-shipping-marks.*') ? 'active' : '' }}">
                <a href="{{ route('customer-extra-shipping-marks.index') }}" title="Extra Shipping Marks">
                    <span class="tl-ico"><i class="fa fa-tags"></i></span><span class="tl-txt">Extra Shipping Marks</span>
                </a>
            </li>
            @endcan

            @can('view-agents')
            <li class="tl-item {{ request()->routeIs('agents.*') ? 'active' : '' }}">
                <a href="{{ route('agents.index') }}" title="Agents">
                    <span class="tl-ico"><i class="fa fa-handshake-o"></i></span><span class="tl-txt">Agents</span>
                </a>
            </li>
            @endcan

            @can('view-cost-types')
            <li class="tl-item {{ request()->routeIs('cost-types.*') ? 'active' : '' }}">
                <a href="{{ route('cost-types.index') }}" title="Cost Types">
                    <span class="tl-ico"><i class="fa fa-list-alt"></i></span><span class="tl-txt">Cost Types</span>
                </a>
            </li>
            @endcan

            @can('view-users')
            <li class="tl-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <a href="{{ route('users.index') }}" title="Admin Users">
                    <span class="tl-ico"><i class="fa fa-user-circle"></i></span><span class="tl-txt">Admin Users</span>
                </a>
            </li>
            @endcan

            @can('view-roles')
            <li class="tl-item {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                <a href="{{ route('roles.index') }}" title="Roles">
                    <span class="tl-ico"><i class="fa fa-shield"></i></span><span class="tl-txt">Roles</span>
                </a>
            </li>
            @endcan

            @can('view-permissions')
            <li class="tl-item {{ request()->routeIs('permissions.*') ? 'active' : '' }}">
                <a href="{{ route('permissions.index') }}" title="Permissions">
                    <span class="tl-ico"><i class="fa fa-key"></i></span><span class="tl-txt">Permissions</span>
                </a>
            </li>
            @endcan

        </ul>
    </div>
</aside>

{{-- Dropdown styles (move into your sidebar CSS file if you prefer) --}}
<style>
    .tl-sidebar .tl-has-sub > .tl-toggle { position: relative; cursor: pointer; }
    .tl-sidebar .tl-caret { margin-left: auto; transition: transform .2s ease; }
    .tl-sidebar .tl-has-sub.open > .tl-toggle .tl-caret { transform: rotate(180deg); }
    .tl-sidebar .tl-sub {
        list-style: none; margin: 0; padding: 0;
        max-height: 0; overflow: hidden; transition: max-height .25s ease;
    }
    .tl-sidebar .tl-has-sub.open > .tl-sub { max-height: 400px; }
    .tl-sidebar .tl-sub li a {
        display: block; padding: 8px 16px 8px 52px; font-size: 13px;
        color: inherit; text-decoration: none; opacity: .85;
    }
    .tl-sidebar .tl-sub li a i { width: 16px; margin-right: 6px; text-align: center; }
    .tl-sidebar .tl-sub li a:hover,
    .tl-sidebar .tl-sub li.active > a { opacity: 1; background: rgba(255,255,255,.08); }
</style>

{{-- Dropdown toggle (no Bootstrap dependency) --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('#left-panel .tl-toggle').forEach(function (toggle) {
            toggle.addEventListener('click', function (e) {
                e.preventDefault();
                var li = toggle.closest('.tl-has-sub');
                var open = li.classList.toggle('open');
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
        });
    });
</script>