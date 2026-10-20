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
            @endcan

            {{-- Operations --}}
            @can('view-shipments')
            <li class="tl-title">Operations</li>
            <li class="tl-item {{ request()->routeIs('itemsshipments.*') ? 'active' : '' }}">
                <a href="{{ route('itemsshipments.index') }}" title="Shipments">
                    <span class="tl-ico"><i class="fa fa-ship"></i></span><span class="tl-txt">Shipments</span>
                </a>
            </li>

            @can('view-notifications')
            <li class="tl-item {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                <a href="{{ route('notifications.index') }}" title="Customer Notifications">
                    <span class="tl-ico"><i class="fa fa-bell"></i></span><span class="tl-txt">Customer Notifications</span>
                </a>
            </li>
            @endcan
            @endcan

            {{-- Accounting --}}
            <li class="tl-title">Accounting</li>
            @can('view-shipment-cost')
            <li class="tl-item {{ request()->routeIs('shipment-bills.*') && !request()->routeIs('shipment-bills.ledger') || request()->routeIs('itemshipments.costs.*') || request()->routeIs('shipments.billing') ? 'active' : '' }}">
                <a href="{{ route('shipment-bills.index') }}" title="Shipment Billing">
                    <span class="tl-ico"><i class="fa fa-money"></i></span><span class="tl-txt">Shipment Billing</span>
                </a>
            </li>
            <li class="tl-item {{ request()->routeIs('shipment-bills.ledger') ? 'active' : '' }}">
                <a href="{{ route('shipment-bills.ledger') }}" title="Shipment Bill Ledger">
                    <span class="tl-ico"><i class="fa fa-book"></i></span><span class="tl-txt">Shipment Bill Ledger</span>
                </a>
            </li>
            @endcan
            <li class="tl-item {{ request()->routeIs('ledger.*') || request()->routeIs('payments.*') ? 'active' : '' }}">
                <a href="{{ route('ledger.index') }}" title="Customer Ledger">
                    <span class="tl-ico"><i class="fa fa-address-book-o"></i></span><span class="tl-txt">Customer Ledger</span>
                </a>
            </li>

            {{-- Reports (dropdown) --}}
            @can('shipment-report')
            @php $repOpen = request()->routeIs('reports.*'); @endphp
            <li class="tl-item tl-has-sub {{ $repOpen ? 'active open' : '' }}">
                <a href="#" class="tl-toggle" title="Reports" aria-expanded="{{ $repOpen ? 'true' : 'false' }}">
                    <span class="tl-ico"><i class="fa fa-bar-chart"></i></span>
                    <span class="tl-txt">Reports</span>
                    <span class="tl-caret"><i class="fa fa-angle-down"></i></span>
                </a>
                <ul class="tl-sub">
                    <li class="{{ request()->routeIs('reports.dashboard') ? 'active' : '' }}"><a href="{{ route('reports.dashboard') }}"><i class="fa fa-th-large"></i> Reports Home</a></li>
                    <li class="{{ request()->routeIs('reports.summary') ? 'active' : '' }}"><a href="{{ route('reports.summary') }}"><i class="fa fa-pie-chart"></i> Business Summary</a></li>
                    <li class="{{ request()->routeIs('reports.period') ? 'active' : '' }}"><a href="{{ route('reports.period') }}"><i class="fa fa-calendar"></i> Month / Date wise</a></li>
                    <li class="{{ request()->routeIs('reports.shipments') ? 'active' : '' }}"><a href="{{ route('reports.shipments') }}"><i class="fa fa-ship"></i> Shipment Report</a></li>
                    <li class="{{ request()->routeIs('reports.customers') ? 'active' : '' }}"><a href="{{ route('reports.customers') }}"><i class="fa fa-users"></i> Customer Report</a></li>
                </ul>
            </li>
            @endcan

            {{-- Old Data (completely separate) --}}
            @can('view-old-data')
            <li class="tl-title">Old Data</li>
            @php $oldOpen = request()->routeIs('old-shipments.*', 'old-customers.*', 'old-payments.*', 'legacy.*'); @endphp
            <li class="tl-item tl-has-sub {{ $oldOpen ? 'active open' : '' }}">
                <a href="#" class="tl-toggle" title="Old Data" aria-expanded="{{ $oldOpen ? 'true' : 'false' }}">
                    <span class="tl-ico"><i class="fa fa-history"></i></span>
                    <span class="tl-txt">Old Data</span>
                    <span class="tl-caret"><i class="fa fa-angle-down"></i></span>
                </a>
                <ul class="tl-sub">
                    <li class="{{ request()->routeIs('old-shipments.*') ? 'active' : '' }}"><a href="{{ route('old-shipments.index') }}"><i class="fa fa-list"></i> Old Shipments</a></li>
                    <li class="{{ request()->routeIs('old-customers.*') ? 'active' : '' }}"><a href="{{ route('old-customers.index') }}"><i class="fa fa-users"></i> Old Customers</a></li>
                    <li class="{{ request()->routeIs('old-payments.*') && !request()->routeIs('old-payments.report') ? 'active' : '' }}"><a href="{{ route('old-payments.index') }}"><i class="fa fa-money"></i> Payments &amp; Collections</a></li>
                    <li class="{{ request()->routeIs('old-payments.report') ? 'active' : '' }}"><a href="{{ route('old-payments.report') }}"><i class="fa fa-bar-chart"></i> Monthly Report</a></li>
           
                </ul>
            </li>
            @endcan

            {{-- Office & Expense (completely separate) --}}
            @can('view-office-expense')
            <li class="tl-title">Office &amp; Expense</li>
            @php $offOpen = request()->routeIs('offices.*', 'employees.*', 'office-bills.*', 'warehouse-bills.*', 'reports.office'); @endphp
            <li class="tl-item tl-has-sub {{ $offOpen ? 'active open' : '' }}">
                <a href="#" class="tl-toggle" title="Office &amp; Expense" aria-expanded="{{ $offOpen ? 'true' : 'false' }}">
                    <span class="tl-ico"><i class="fa fa-building-o"></i></span>
                    <span class="tl-txt">Office &amp; Expense</span>
                    <span class="tl-caret"><i class="fa fa-angle-down"></i></span>
                </a>
                <ul class="tl-sub">
                    <li class="{{ request()->routeIs('offices.*') ? 'active' : '' }}"><a href="{{ route('offices.index') }}"><i class="fa fa-building-o"></i> Offices</a></li>
                    <li class="{{ request()->routeIs('employees.*') ? 'active' : '' }}"><a href="{{ route('employees.index') }}"><i class="fa fa-id-badge"></i> Employees</a></li>
                    <li class="{{ request()->routeIs('office-bills.*') ? 'active' : '' }}"><a href="{{ route('office-bills.index') }}"><i class="fa fa-file-text"></i> Office Bills</a></li>
                    <li class="{{ request()->routeIs('warehouse-bills.*') ? 'active' : '' }}"><a href="{{ route('warehouse-bills.index') }}"><i class="fa fa-file-text-o"></i> Summary Bills</a></li>
                    <li class="{{ request()->routeIs('reports.office') ? 'active' : '' }}"><a href="{{ route('reports.office') }}"><i class="fa fa-bar-chart"></i> Expense Report</a></li>
                </ul>
            </li>
            @endcan

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
    /* Tweak these 4 values to match your sidebar theme */
    #left-panel {
        --tl-sub-bg: rgba(0,0,0,.20);
        --tl-sub-color: rgba(255,255,255,.75);
        --tl-sub-hover-bg: rgba(255,255,255,.08);
        --tl-sub-accent: #4da3ff;
    }

    /* Parent row */
    #left-panel .tl-nav .tl-has-sub { display: block; }
    #left-panel .tl-nav .tl-has-sub > .tl-toggle {
        display: flex; align-items: center; cursor: pointer; position: relative;
    }
    #left-panel .tl-nav .tl-has-sub > .tl-toggle .tl-txt { flex: 1; }
    #left-panel .tl-nav .tl-has-sub > .tl-toggle .tl-caret {
        display: inline-flex; margin-left: auto; font-size: 16px; transition: transform .2s ease;
    }
    #left-panel .tl-nav .tl-has-sub.open > .tl-toggle .tl-caret { transform: rotate(180deg); }

    /* Sub menu */
    #left-panel .tl-nav .tl-has-sub > .tl-sub {
        display: block; list-style: none; margin: 0; padding: 0;
        max-height: 0; overflow: hidden; background: var(--tl-sub-bg);
        transition: max-height .25s ease;
    }
    #left-panel .tl-nav .tl-has-sub.open > .tl-sub { max-height: 800px; }

    #left-panel .tl-nav .tl-sub > li { display: block; margin: 0; padding: 0; list-style: none; background: none; }
    #left-panel .tl-nav .tl-sub > li > a {
        display: flex; align-items: center; gap: 10px;
        padding: 9px 16px 9px 54px; font-size: 13px; line-height: 1.4;
        color: var(--tl-sub-color); text-decoration: none;
        border-left: 3px solid transparent; background: none;
        transition: background .15s ease, color .15s ease;
    }
    #left-panel .tl-nav .tl-sub > li > a i { width: 16px; text-align: center; font-size: 13px; opacity: .8; }
    #left-panel .tl-nav .tl-sub > li > a:hover { background: var(--tl-sub-hover-bg); color: #fff; }
    #left-panel .tl-nav .tl-sub > li.active > a {
        background: var(--tl-sub-hover-bg); color: #fff;
        border-left-color: var(--tl-sub-accent); font-weight: 600;
    }
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