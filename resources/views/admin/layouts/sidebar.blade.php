
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
                    <li class="menu-title">INVENTORY</li>
                    
                    <!-- <li class="{{ request()->routeIs('products.*') ? 'active' : '' }}">
                        <a href="{{ route('products.index') }}">
                            <i class="menu-icon fa fa-cubes"></i> Products
                        </a>
                    </li> -->
                    <li class="{{ request()->routeIs('cargo-items.*') ? 'active' : '' }}">
                        <a href="{{ route('cargo-items.index') }}">
                            <i class="menu-icon fa fa-cubes"></i> Items
                        </a>
                    </li>
                    
                    <!-- <li class="{{ request()->routeIs('boxes.*') ? 'active' : '' }}">
                        <a href="{{ route('boxes.index') }}">
                            <i class="menu-icon fa fa-archive"></i> Boxes
                        </a>
                    </li> -->
                    
                    <!-- Operations -->
                    <li class="menu-title">OPERATIONS</li>
<!--                     
                    <li class="{{ request()->routeIs('shipments.*') ? 'active' : '' }}">
                        <a href="{{ route('shipments.index') }}">
                            <i class="menu-icon fa fa-truck"></i> Shipments
                        </a>
                    </li> -->
                    <li class="{{ request()->routeIs('itemsshipments.*') ? 'active' : '' }}">
                        <a href="{{ route('itemsshipments.index') }}">
                            <i class="menu-icon fa fa-truck"></i> Item shipments
                        </a>
                    </li>
          

                    <!-- Reports -->
                    <li class="menu-title">REPORTS</li>
                        <li class="{{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
                            <a href="{{ route('admin.reports') }}">
                                <i class="menu-icon fa fa-list-alt"></i> Reports
                            </a>
                        </li>
                    
                       <!-- Master Data -->
                    <li class="menu-title">MASTER DATA</li>
                    
                    <li class="{{ request()->routeIs('customers.*') ? 'active' : '' }}">
                        <a href="{{ route('customers.index') }}">
                            <i class="menu-icon fa fa-users"></i> Customers
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