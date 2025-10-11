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

                        <div class="d-flex justify-content-between align-items-center my-3 mx-3">
                            <!-- Kolom Pencarian -->
                            <div class="col-md-4 p-0">
                                <input type="text" id="searchUser" class="form-control border border-primary rounded" placeholder="Cari user..." />
                            </div>

                            <!-- Tombol Tambah & Export -->
                            <div class="me-3 text-end">
                                <div class="btn-group">
                                    <button type="button" class="btn bg-gradient-dark dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="material-icons text-sm">add</i>&nbsp;&nbsp;Tambah User
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('user-management.create') }}">
                                                Tambah Manual
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#importModal">
                                                Impor dari Excel
                                            </a>
                                        </li>
                                    </ul>
                                </div>

                                <a href="{{ route('user.export') }}" class="btn btn-success ms-2">
                                    <i class="material-icons text-sm">download</i>&nbsp;&nbsp;Ekspor User
                                </a>
                            </div>
                        </div>

                        {{-- Tabel Users --}}
                        <div class="card-body px-0 pb-2">
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">NO.</th>
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
                                                        <p class="mb-0 text-sm">{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    $photo = $user->photo 
                                                        ? (Str::startsWith($user->photo, ['http://','https://']) 
                                                            ? $user->photo 
                                                            : asset('storage/' . $user->photo)) 
                                                        : asset('assets/img/bruce-mars.jpg');
                                                @endphp
                                                <img src="{{ $photo }}" class="avatar avatar-sm me-3 border-radius-lg" alt="{{ $user->name }}">
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
                                                <a href="{{ route('user-management.edit', $user->id) }}" class="btn btn-success btn-link">
                                                    <i class="material-icons">edit</i>
                                                </a>
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

                                {{-- Pagination --}}
                                <div class="d-flex justify-content-end mt-3 me-3">
                                    {{ $users->links('vendor.pagination.bootstrap-5') }}
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </main>

    <x-plugins></x-plugins>

    {{-- Modal Import --}}
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="importForm" action="{{ route('user.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="importModalLabel">Import Users dari Excel / CSV</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="file" class="form-label">Pilih File (.xlsx / .csv)</label>
                            <input type="file" name="file" id="file" class="form-control" accept=".xlsx,.csv" required>
                        </div>
                        <div class="alert alert-secondary" role="alert">
                            <span class="text-white">Pastikan format sesuai template.</span>  
                            <a href="{{ asset('storage/template/users_template.xlsx') }}" class="fw-bold text-primary" target="_blank">
                                Download Template Unggah User
                            </a>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Import</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- SweetAlert Delete & Validasi Import --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#searchUser').on('keyup', function() {
                let query = $(this).val();

                $.ajax({
                    url: "{{ route('user-management.search') }}",
                    type: "GET",
                    data: { keyword: query },
                    success: function(response) {
                        // Ganti tbody dengan hasil baru
                        $('table tbody').html(response);
                    }
                });
            });
        });
        
        // Konfirmasi hapus user
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

        // Validasi file import Excel
        document.getElementById('importForm').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('file');
            const filePath = fileInput.value;
            const allowedExtensions = /(\.xlsx|\.csv)$/i;

            if (!allowedExtensions.exec(filePath)) {
                e.preventDefault(); // batalkan submit
                Swal.fire({
                    icon: 'error',
                    title: 'Format tidak valid!',
                    text: 'Silakan pilih file dengan format .xlsx atau .csv',
                });
                fileInput.value = ''; // reset input
                return false;
            }
        });
    </script>

</x-layout>
