<x-layout bodyClass="g-sidenav-show bg-gray-200">

    <x-navbars.sidebar activePage="user-management"></x-navbars.sidebar>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <x-navbars.navs.auth titlePage="Manajemen Pegawai"></x-navbars.navs.auth>

        <div class="container-fluid py-4">

            {{-- SweetAlert untuk notifikasi sukses --}}
            @if (session('status'))
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sukses!',
                            text: '{{ session('status') }}',
                            timer: 2500,
                            showConfirmButton: false
                        });
                    });
                </script>
            @endif

            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                                <h6 class="text-white mx-3">
                                    <strong>Kelola Akun User</strong>
                                </h6>
                            </div>
                        </div>

                        {{-- Tombol Tambah User --}}
                        <div class="me-3 my-3 text-end">
                            <a class="btn bg-gradient-dark mb-0" href="{{ route('user-management.create') }}">
                                <i class="material-icons text-sm">add</i>&nbsp;&nbsp;Tambah User Baru
                            </a>
                        </div>

                        {{-- Tombol Export & Import --}}
                        <div class="mb-3 ms-3">
                            <a href="{{ route('user.export') }}" class="btn btn-success">Export Users</a>

                            <form action="{{ route('user.import') }}" method="POST" enctype="multipart/form-data" style="display:inline-block;">
                                @csrf
                                <input type="file" name="file" required>
                                <button type="submit" class="btn btn-primary">Import Users</button>
                            </form>
                        </div>

                        {{-- Tabel Users --}}
                        <div class="card-body px-0 pb-2">
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ID</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">FOTO</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">NAMA LENGKAP</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">EMAIL</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">STATUS</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">TANGGAL DIBUAT</th>
                                            <th class="text-secondary opacity-7"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($users as $user)
                                        <tr>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <p class="mb-0 text-sm">{{ $user->id }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div>
                                                        @php
                                                            $photo = $user->photo 
                                                                ? (Str::startsWith($user->photo, ['http://','https://']) 
                                                                    ? $user->photo 
                                                                    : asset('storage/' . $user->photo)) 
                                                                : asset('assets/img/bruce-mars.jpg');
                                                        @endphp
                                                        <img src="{{ $photo }}" 
                                                            class="avatar avatar-sm me-3 border-radius-lg" 
                                                            alt="{{ $user->name }}">
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">{{ $user->name }}</h6>
                                                </div>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                <p class="text-xs text-secondary mb-0">{{ $user->email }}</p>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="text-secondary text-xs font-weight-bold">{{ $user->role }}</span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="text-secondary text-xs font-weight-bold">
                                                    {{ optional($user->created_at)->format('d/m/Y') }}
                                                </span>
                                            </td>
                                            <td class="align-middle">
                                                {{-- Tombol Edit --}}
                                                <a href="{{ route('user-management.edit', $user->id) }}" class="btn btn-success btn-link">
                                                    <i class="material-icons">edit</i>
                                                </a>

                                                {{-- Tombol Delete dengan SweetAlert --}}
                                                <button type="button" class="btn btn-danger btn-link" onclick="deleteUser({{ $user->id }})">
                                                    <i class="material-icons">close</i>
                                                </button>

                                                <form id="delete-form-{{ $user->id }}" action="{{ route('user-management.destroy', $user->id) }}" method="POST" style="display:none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </main>

    <x-plugins></x-plugins>

    {{-- SweetAlert Delete --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function deleteUser(id) {
            Swal.fire({
                title: 'Yakin?',
                text: "User ini akan dihapus!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            });
        }
    </script>

</x-layout>
