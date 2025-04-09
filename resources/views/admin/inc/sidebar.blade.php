<aside class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4 "
    id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
            aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0" href="{{ route('admin.home') }}">
            <img src="{{ asset('assets/img/estudiante.png') }}" class="navbar-brand-img h-100" alt="main_logo">
            <span class="ms-1 font-weight-bold">保育園</span>
        </a>
    </div>
    <hr class="horizontal dark mt-0">
    <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
        <ul class="navbar-nav">
            {{-- Dashboard --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.home') ? 'active' : '' }}" href="{{ route('admin.home') }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-tv-2 text-primary text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Dashboard</span>
                </a>
            </li>

            {{-- Reservaciones --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.reservations.*') ? 'active' : '' }}" href="{{ route('admin.reservations.index') }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-calendar-grid-58 text-warning text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Reservaciones</span>
                </a>
            </li>

            {{-- Cuartos --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.rooms.*') ? 'active' : '' }}" href="{{ route('admin.rooms.index') }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-building text-success text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Cuartos</span>
                </a>
            </li>

            {{-- Clientes --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}" href="{{ route('admin.clients.index') }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-single-02 text-info text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Clientes</span>
                </a>
            </li>

            {{-- Niños --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.children.*') ? 'active' : '' }}" href="{{ route('admin.children.index') }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-favourite-28 text-danger text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Niños</span>
                </a>
            </li>

            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Cuenta</h6>
            </li>

            <li class="nav-item">
                <a class="nav-link " href="../pages/sign-up.html">
                    <div
                        class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-collection text-info text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Sign Up</span>
                </a>
            </li>
        </ul>
    </div>
</aside>
