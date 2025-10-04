<x-layout bodyClass="g-sidenav-show bg-gray-200">

    <x-navbars.sidebar activePage="user-profile"></x-navbars.sidebar>
    <div class="main-content position-relative bg-gray-100 max-height-vh-100 h-100">
        <!-- Navbar -->
        <x-navbars.navs.auth titlePage='Profil Saya'></x-navbars.navs.auth>
        <!-- End Navbar -->

        <div class="container-fluid px-2 px-md-4">
            <div class="page-header min-height-300 border-radius-xl mt-4"
                style="background-image: url('/assets/img/monas.jpg'); background-position: center;">
                <span class="mask bg-gradient-primary opacity-6"></span>
            </div>

            <div class="card card-body mx-3 mx-md-4 mt-n6">

                <!-- Avatar & Nama -->
                <div class="row gx-4 mb-4">
                    <div class="col-auto">
                        <div class="avatar avatar-xxl position-relative">
                            @php
                                $photo = auth()->user()->photo;
                                $isUrl = $photo && str_starts_with($photo, 'http');
                            @endphp
                            <img src="{{ $photo ? ($isUrl ? $photo : asset('storage/' . $photo)) : asset('assets/img/bruce-mars.jpg') }}"
                                 alt="profile_image" class="w-100 border-radius-xl shadow">
                        </div>
                    </div>
                    <div class="col my-auto">
                        <h4 class="mb-1">{{ auth()->user()->name }}</h4>
                        <p class="mb-0 text-sm text-muted">
                            {{ auth()->user()->jabatan ?? '' }} — {{ auth()->user()->unit_kerja ?? '' }}
                        </p>
                    </div>
                </div>

                <!-- Info Profile -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card border shadow-sm p-3 h-100">
                            <h6 class="text-uppercase text-muted text-xs mb-3">Informasi Dasar</h6>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item px-0 py-1"><strong>Email:</strong> {{ auth()->user()->email }}</li>
                                <li class="list-group-item px-0 py-1"><strong>NIP:</strong> {{ auth()->user()->nip ?? '-' }}</li>
                                <li class="list-group-item px-0 py-1"><strong>Status:</strong> {{ auth()->user()->role ?? 'Pegawai' }}</li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="card border shadow-sm p-3 h-100">
                            <h6 class="text-uppercase text-muted text-xs mb-3">Kepegawaian</h6>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item px-0 py-1"><strong>Unit Kerja:</strong> {{ auth()->user()->unit_kerja ?? '-' }}</li>
                                <li class="list-group-item px-0 py-1"><strong>Jabatan:</strong> {{ auth()->user()->jabatan ?? '-' }}</li>
                                <li class="list-group-item px-0 py-1"><strong>Pangkat:</strong> {{ auth()->user()->pangkat ?? '-' }}</li>
                                <li class="list-group-item px-0 py-1"><strong>Golongan:</strong> {{ auth()->user()->golongan ?? '-' }}</li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <x-footers.auth></x-footers.auth>
    </div>

    <x-plugins></x-plugins>
</x-layout>
