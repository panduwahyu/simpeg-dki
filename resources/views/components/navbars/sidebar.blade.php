@props(['activePage'])

<aside
    class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3 bg-gradient-dark"
    id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-white opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
            aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0 d-flex text-wrap align-items-center" href="{{ route('dashboard') }}">
            <img src="{{ asset('assets/img/logo-ct.png') }}" class="navbar-brand-img h-100" alt="main_logo">
            <span class="ms-2 font-weight-bold text-white">SIPETRA</span>
        </a>
    </div>
    <hr class="horizontal light mt-0 mb-2">
    <div class="sidenav w-auto max-height-vh-100" id="sidenav-main">
        <ul class="navbar-nav">
            
            {{-- Section Kepegawaian --}}
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs text-white font-weight-bolder opacity-8">Kepegawaian</h6>
            </li>

            {{-- Profil Saya selalu tampil --}}
            <li class="nav-item">
                <a class="nav-link text-white {{ $activePage == 'user-profile' ? 'active bg-gradient-primary' : '' }}"
                    href="{{ route('user-profile') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">person</i>
                    </div>
                    <span class="nav-link-text ms-1">Profil Saya</span>
                </a>
            </li>

            {{-- Manajemen Pegawai hanya untuk Admin/Supervisor --}}
            @if(auth()->user()->role !== 'Pegawai')
                <li class="nav-item">
                    <a class="nav-link text-white {{ $activePage == 'user-management' ? 'active bg-gradient-primary' : '' }}"
                        href="{{ route('user-management') }}">
                        <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="material-icons opacity-10">group</i>
                        </div>
                        <span class="nav-link-text ms-1">Manajemen Pegawai</span>
                    </a>
                </li>
            @endif

            {{-- Section Laman --}}
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs text-white font-weight-bolder opacity-8">Laman</h6>
            </li>

            {{-- Monitoring --}}
            <li class="nav-item">
                <a class="nav-link text-white {{ $activePage == 'monitoring' || $activePage == 'pegawai_dashboard' ? 'active bg-gradient-primary' : '' }}"
                   href="{{ auth()->user()->role === 'Pegawai' ? route('pegawai-dashboard') : route('dashboard') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">dashboard</i>
                    </div>
                    <span class="nav-link-text ms-1">Monitoring</span>
                </a>
            </li>

            {{-- Dokumen Baru hanya untuk Admin & Supervisor --}}
            @if(auth()->user()->role === 'Admin' || auth()->user()->role === 'Supervisor')
                <li class="nav-item">
                    <a class="nav-link text-white {{ $activePage == 'form' ? 'active bg-gradient-primary' : '' }}"
                        href="{{ route('form.index') }}">
                        <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="material-icons opacity-10">assignment</i>
                        </div>
                        <span class="nav-link-text ms-1">Dokumen Baru</span>
                    </a>
                </li>

                <li class="nav-item">
                <a class="nav-link text-white {{ $activePage == 'monitoring-pegawai' ? 'active bg-gradient-primary' : '' }}"
                   href="{{ route('monitoring.index') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">insights</i>
                    </div>
                    <span class="nav-link-text ms-1">Monitoring Pegawai</span>
                </a>
            </li>
            @endif

            {{-- Dokumen --}}
            <li class="nav-item">
                <a class="nav-link text-white {{ $activePage == 'tables' ? 'active bg-gradient-primary' : '' }}"
                    href="{{ route('tables') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">table_view</i>
                    </div>
                    <span class="nav-link-text ms-1">Dokumen</span>
                </a>
            </li>

            {{-- Tanda Tangan PDF --}}
            <li class="nav-item">
                @if(auth()->user()->role === 'Pegawai')
                    <a class="nav-link text-white {{ $activePage == 'pdf-sign' ? 'active bg-gradient-primary' : '' }}"
                        href="{{ route('pdf.sign.form') }}">
                        <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="material-icons opacity-10">picture_as_pdf</i>
                        </div>
                        <span class="nav-link-text ms-1">Tanda Tangan PDF</span>
                    </a>
                @else
                    <a class="nav-link text-white {{ $activePage == 'pdf-sign-supervisor' ? 'active bg-gradient-primary' : '' }}"
                        href="{{ route('pdf.sign.supervisor') }}">
                        <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="material-icons opacity-10">picture_as_pdf</i>
                        </div>
                        <span class="nav-link-text ms-1">Tanda Tangan PDF</span>
                    </a>
                @endif
            </li>
        </ul>
    </div>
</aside>
