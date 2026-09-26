
<aside id="left-panel" class="left-panel">
        <nav class="navbar navbar-expand-sm navbar-default">

            <div class="navbar-header">
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#main-menu" aria-controls="main-menu" aria-expanded="false" aria-label="Toggle navigation">
                    <i class="fa fa-bars"></i>
                </button>
                <a class="navbar-brand" href="./"><img src="{{ asset('admin/images/logo.png') }}" alt="Logo"></a>
                <a class="navbar-brand hidden" href="./"><img src="{{ asset('admin/images/logo2.png') }}" alt="Logo"></a>
            </div>
            <div id="main-menu" class="main-menu collapse navbar-collapse">
                <ul class="nav navbar-nav">
                    <!-- Dashboard -->
                    <li class="{{ request()->is('admin/dashboard') || request()->is('/') ? 'active' : '' }}">
                        <a href="/admin/dashboard"> 
                            <i class="menu-icon fa fa-dashboard"></i>Dashboard 
                        </a>
                    </li>
                    
                 
                    
                    <!-- Inventory -->
                    <li class="menu-title">WARE HOUSE</li>
                    
                  
                    <li class="{{ request()->routeIs('cargo-items.*') ? 'active' : '' }}">
                        <a href="{{ route('cargo-items.index') }}">
                            <i class="menu-icon fa fa-cubes"></i> China Warehouse
                        </a>
                    </li>
                    <li>
    <a href="{{ route('cargo-items.pending') }}">
        <i class="fa fa-clock-o"></i> Pending
    </a>
</li>
<li>
    <a href="{{ route('cargo-items.packed') }}">
        <i class="fa fa-cube"></i> Packed
    </a>
</li>
<li>
    <a href="{{ route('cargo-items.arrived-bd') }}">
        <i class="fa fa-plane"></i> Arrived BD
    </a>
</li>
       
                    
                    
                    <!-- Operations -->
                    <li class="menu-title">OPERATIONS</li>

                    <li class="{{ request()->routeIs('itemsshipments.*') ? 'active' : '' }}">
                        <a href="{{ route('itemsshipments.index') }}">
                            <i class="menu-icon fa fa-truck"></i> Shipments
                        </a>
                    </li>
          

                    <!-- Reports -->
                    <li class="menu-title">REPORTS</li>
                    {{-- <li class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">
                        <a href="{{ route('reports.dashboard') }}">
                            <i class="menu-icon fa fa-truck"></i> Dashboard Reports
                        </a>
                    </li> --}}
                    
                       <!-- Master Data -->
                    <li class="menu-title">MASTER DATA</li>
                    <li class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
    <a href="{{ route('users.index') }}">
        <i class="menu-icon fa fa-user-circle"></i> Users
    </a>
</li>
                    <li class="{{ request()->routeIs('customers.*') ? 'active' : '' }}">
                        <a href="{{ route('customers.index') }}">
                            <i class="menu-icon fa fa-users"></i> Customers
                        </a>
                    </li>
                  <li class="{{ request()->routeIs('customer-extra-shipping-marks.*') ? 'active' : '' }}">
    <a href="{{ route('customer-extra-shipping-marks.index') }}">
        <i class="menu-icon fa fa-tags"></i> Extra Shipping Marks
    </a>
</li>
                    
                    <li class="{{ request()->routeIs('agents.*') ? 'active' : '' }}">
                        <a href="{{ route('agents.index') }}">
                            <i class="menu-icon fa fa-users"></i> Agents
                        </a>
                    </li>
                    
                    <li class="{{ request()->routeIs('cost-types.*') ? 'active' : '' }}">
                        <a href="{{ route('cost-types.index') }}">
                            <i class="menu-icon fa fa-list-alt"></i> Cost Types
                        </a>
                    </li>
                   
                </ul>
            </div>
        </nav>
    </aside>