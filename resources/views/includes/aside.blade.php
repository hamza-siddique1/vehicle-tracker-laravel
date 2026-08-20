@php
    $role = Auth()->user()->role;
@endphp
<nav id="sidebar" class="sidebar">
    <div class="sidebar-content js-simplebar">
        <a class="sidebar-brand" href="{{ route('dashboard.index') }}">
            <span class="align-middle me-3">
                <img src="{{ asset('assets/img/kaj-tracker.png') }}" alt="kajtracker" width="200" />
            </span>
        </a>

        <ul class="sidebar-nav">
            <li class="sidebar-header">
                General
            </li>
            <li class="sidebar-item {{ request()->is('dashboard') ? 'active' : '' }}">
                <a class="sidebar-link" href="{{ route('dashboard.index') }}">
                    <i class="align-middle" data-feather="sliders"></i>
                    <span class="align-middle">Dashboard</span>
                </a>
            </li>

            <li class="sidebar-header">
                Vehicles
            </li>


            <li class="sidebar-item {{ request()->is('vehicles/sold') || request()->is('vehicles') ? 'active' : '' }} ">
                <a data-target="#vehicles" data-toggle="collapse"
                    class="sidebar-link {{ request()->is('vehicles/sold') || request()->is('vehicles') ? 'collapsed' : '' }}">
                    <i class="align-middle" data-feather="plus-square"></i>
                    <span class="align-middle">Vehicles</span>
                </a>
                <ul id="vehicles"
                    class="sidebar-dropdown list-unstyled collapse {{ request()->is('vehicles/sold') || request()->is('vehicles') ? 'show' : '' }}"
                    data-parent="#sidebar">

                    <li class="sidebar-item {{ request()->is('vehicles') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('vehicles.index') }}">
                            <i class="align-middle" data-feather="truck"></i>
                            <span class="align-middle">All Vehicles</span>
                        </a>
                    </li>

                    <li class="sidebar-item {{ request()->is('vehicles/sold') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('vehicles.sold') }}">
                            <i class="align-middle" data-feather="truck"></i>
                            <span class="align-middle">Sold Vehicles</span>
                        </a>
                    </li>





                </ul>
            </li>
            @if (in_array($role, ['admin', 'vehicle_manager']))

                <li
                    class="sidebar-item {{ request()->is('vehicles/upload*') || request()->is('headers') ? 'active' : '' }} ">
                    <a data-target="#upload" data-toggle="collapse"
                        class="sidebar-link {{ request()->is('vehicles/upload/*') ? 'collapsed' : '' }}">
                        <i class="align-middle" data-feather="plus-square"></i>
                        <span class="align-middle">Upload Files</span>
                    </a>
                    <ul id="upload"
                        class="sidebar-dropdown list-unstyled collapse {{ request()->is('vehicles/upload*') || request()->is('headers') ? 'show' : '' }}"
                        data-parent="#sidebar">

                        <li class="sidebar-item {{ request()->is('vehicles/upload/buy') ? 'active' : '' }}">
                            <a class="sidebar-link" href="{{ route('upload.create.buy') }}">
                                <i class="align-middle" data-feather="plus-square"></i>
                                <span class="align-middle">Buy</span>
                            </a>
                        </li>

                        <li class="sidebar-item {{ request()->is('vehicles/upload/inventory') ? 'active' : '' }}">
                            <a class="sidebar-link" href="{{ route('upload.create.inventory') }}">
                                <i class="align-middle" data-feather="plus-square"></i>
                                <span class="align-middle">Inventory</span>
                            </a>
                        </li>

                        <li class="sidebar-item {{ request()->is('vehicles/upload/sold') ? 'active' : '' }}">
                            <a class="sidebar-link" href="{{ route('upload.create.sold') }}">
                                <i class="align-middle" data-feather="plus-square"></i>
                                <span class="align-middle">Sold</span>
                            </a>
                        </li>

                        @if (in_array($role, ['admin']))
                            <li class="sidebar-item {{ request()->is('headers') ? 'active' : '' }}">
                                <a class="sidebar-link" href="{{ route('headers.index') }}">
                                    <i class="align-middle" data-feather="plus-square"></i>
                                    <span class="align-middle">CSV Headers</span>
                                </a>
                            </li>
                        @endif


                    </ul>
                </li>

                <li class="sidebar-item {{ request()->is('runlist*') ? 'active' : '' }} ">
                    <a data-target="#run-list" data-toggle="collapse"
                        class="sidebar-link {{ request()->is('runlist/upload*') ? 'collapsed' : '' }}">
                        <i class="align-middle" data-feather="clipboard"></i>
                        <span class="align-middle">Run List</span>
                    </a>
                    <ul id="run-list"
                        class="sidebar-dropdown list-unstyled collapse {{ request()->is('runlists') || request()->is('runlist/upload') ? 'show' : '' }}"
                        data-parent="#sidebar">

                        <li class="sidebar-item {{ request()->is('runlists') ? 'active' : '' }}">
                            <a class="sidebar-link" href="{{ route('vehicles.runlists.index') }}">
                                <i class="align-middle" data-feather="clipboard"></i>
                                <span class="align-middle">Run Lists</span>
                            </a>
                        </li>

                        <li class="sidebar-item {{ request()->is('runlist/upload') ? 'active' : '' }}">
                            <a class="sidebar-link" href="{{ route('upload.create.runlist') }}">
                                <i class="align-middle" data-feather="plus-square"></i>
                                <span class="align-middle">Add New List</span>
                            </a>
                        </li>
                    </ul>
                </li>


            @endif

            @if ($role != 'viewer')
                <li class="sidebar-item {{ request()->is('vehicles/create') ? 'active' : '' }}">
                    <a class="sidebar-link" href="{{ route('vehicles.index', ['create' => 'new']) }}">
                        <i class="align-middle" data-feather="plus-square"></i>
                        <span class="align-middle">Add New Vehicle</span>
                    </a>
                </li>

                <li class="sidebar-item {{ request()->is('vehicles/create') ? 'active' : '' }}">
                    <a class="sidebar-link" href="{{ route('vinocr.showform') }}">
                        <i class="align-middle" data-feather="plus-square"></i>
                        <span class="align-middle">Intake</span>
                    </a>
                </li>

                <li class="sidebar-item ">
                    <a class="sidebar-link" href="/duplicate/vehicles">
                        <i class="align-middle" data-feather="copy"></i>
                        <span class="align-middle">Duplicated Vehicles</span>
                    </a>
                </li>
            @endif

            @if ($role == 'admin')
                <li class="sidebar-header">
                    Manage
                </li>
                <li class="sidebar-item {{ request()->is('users*') ? 'active' : '' }} ">
                    <a data-target="#users" data-toggle="collapse"
                        class="sidebar-link {{ request()->is('users/*') ? 'collapsed' : '' }}">
                        <i class="align-middle" data-feather="users"></i>
                        <span class="align-middle">Users</span>
                    </a>
                    <ul id="users"
                        class="sidebar-dropdown list-unstyled collapse {{ request()->is('users*') ? 'show' : '' }}"
                        data-parent="#sidebar">

                        <li class="sidebar-item {{ request()->is('users') ? 'active' : '' }}">
                            <a class="sidebar-link" href="{{ route('users.index') }}">
                                <i class="align-middle" data-feather="users"></i>
                                <span class="align-middle">All Users</span>
                            </a>
                        </li>
                        <li class="sidebar-item {{ request()->is('users/create') ? 'active' : '' }}">
                            <a class="sidebar-link" href="{{ route('users.create') }}">
                                <i class="align-middle" data-feather="user-plus"></i>
                                <span class="align-middle">Add New User</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="sidebar-item ">
                    <a class="sidebar-link" href="/reset?confirm=">
                        <i class="align-middle" data-feather="plus-square"></i>
                        <span class="align-middle">RESET VEHICLES</span>
                    </a>
                </li>
            @endif


        </ul>
    </div>
</nav>
