<x-layout bodyClass="g-sidenav-show bg-gray-200">
    <x-navbars.sidebar activePage="form"></x-navbars.sidebar>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <!-- Navbar -->
        <x-navbars.navs.auth titlePage="Dokumen Baru"></x-navbars.navs.auth>

        <div class="container-fluid py-4">
            <!-- Form Input Dokumen -->
            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                                <h6 class="text-white text-capitalize ps-3">Edit Dokumen</h6>
                            </div>
                        </div>
                        <div class="card-body px-4 pb-2">

                            <!-- SweetAlert Success -->
                            @if(session('success'))
                                <script>
                                    document.addEventListener('DOMContentLoaded', function () {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Sukses',
                                            text: '{{ session('success') }}',
                                            timer: 2000,
                                            showConfirmButton: false
                                        });
                                    });
                                </script>
                            @endif

                            <!-- SweetAlert Error -->
                            @if($errors->any())
                                <script>
                                    document.addEventListener('DOMContentLoaded', function () {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Gagal',
                                            html: `<ul style="text-align:left;">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>`,
                                            confirmButtonText: 'OK'
                                        });
                                    });
                                </script>
                            @endif

                            <form action="{{ route('jenis-dokumen.update', $jenisDokumen->id) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <!-- Nama Dokumen -->
                                <div class="mb-3">
                                    <label for="nama_dokumen" class="form-label">Nama Dokumen</label>
                                    <input type="text" name="nama_dokumen" value="{{ old('nama_dokumen', $jenisDokumen->nama_dokumen) }}" class="form-control">
                                </div>

                                <!-- Tahun -->
                                <div class="mb-3">
                                    <label for="tahun" class="form-label">Tahun</label>
                                    <input type="number" id="tahun" name="tahun" class="form-control"
                                           value="{{ old('tahun', date('Y')) }}">
                                </div>

                                <!-- Tipe Periode -->
                                <div class="mb-3">
                                    <label for="periode_tipe" class="form-label">Tipe Periode</label>
                                    <select name="periode_tipe" id="periode_tipe" class="form-control">
                                        <option value="">-- Pilih Tipe --</option>
                                        <option value="bulanan" {{ old('periode_tipe') == 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                                        <option value="triwulanan" {{ old('periode_tipe') == 'triwulanan' ? 'selected' : '' }}>Triwulanan</option>
                                        <option value="tahunan" {{ old('periode_tipe') == 'tahunan' ? 'selected' : '' }}>Tahunan</option>
                                    </select>
                                </div>

                                <!-- Pegawai Ditugaskan -->
                                <div class="mb-3">
                                    <label for="pegawai_type" class="form-label">Pegawai Ditugaskan</label>
                                    <select name="pegawai_type" id="pegawai_type" class="form-control">
                                        <option value="">-- Pilih Opsi --</option>
                                        <option value="all" {{ old('pegawai_type') == 'all' ? 'selected' : '' }}>Seluruh Pegawai</option>
                                        <option value="specific" {{ old('pegawai_type', 'specific') == 'specific' ? 'selected' : '' }}>Pegawai Tertentu</option>
                                    </select>
                                </div>

                                <!-- Checkbox Pegawai -->
                                <div class="mb-3" id="pegawai_checkbox_container">
                                    <label class="form-label">Pilih Pegawai</label>
                                    <div class="border p-2" style="max-height:300px; overflow-y:auto;">
                                        <table class="table table-bordered table-sm mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Pilih</th>
                                                    <th>Nama</th>
                                                    <th>Email</th>
                                                    <th>NIP</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($pegawaiList as $pegawai)
                                                    <tr>
                                                        <td class="text-center">
                                                            <input type="checkbox" name="pegawai_ids[]" value="{{ $pegawai->id }}"
                                                            @if(is_array(old('pegawai_ids')) && in_array($pegawai->id, old('pegawai_ids'))) checked @endif>
                                                        </td>
                                                        <td>{{ $pegawai->name }}</td>
                                                        <td>{{ $pegawai->email }}</td>
                                                        <td>{{ $pegawai->nip ?? '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preview Tabel Dokumen & Periode -->
            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-info shadow-info border-radius-lg pt-4 pb-3">
                                <h6 class="text-white text-capitalize ps-3">Dokumen yang Dimuat</h6>
                            </div>
                        </div>
                        <div class="card-body px-4 pb-2">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Nama Dokumen</th>
                                        <th>Tipe Periode</th>
                                        <th>Tahun Terbaru</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $no = 1; @endphp
                                    @foreach($jenisDokumen->sortByDesc(fn($jd) => $jd->periode->max('tahun')) as $jd)
                                        @php
                                            $latestPeriode = $jd->periode->sortByDesc('tahun')->first();
                                        @endphp
                                        <tr>
                                            <td>{{ $no++ }}</td>
                                            <td>{{ $jd->nama_dokumen }}</td>
                                            <td>{{ $jd->periode_tipe }}</td>
                                            <td>{{ $latestPeriode?->tahun ?? '-' }}</td>
                                            <td>
                                                <a href="{{ route('jenis-dokumen.edit', $jd->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                                <form action="{{ route('jenis-dokumen.destroy') }}" method="POST" style="display:inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="nama_dokumen" value="{{ $jd->nama_dokumen }}">
                                                    <input type="hidden" name="periode_tipe" value="{{ $jd->periode_tipe }}">
                                                    <input type="hidden" name="tahun" value="{{ $jd->periode->max('tahun') }}">
                                                    <button type="submit" class="btn btn-sm btn-danger btn-hapus">Hapus</button>
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

            <x-footers.auth></x-footers.auth>
        </div>
    </main>
    <x-plugins></x-plugins>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const pegawaiType = document.getElementById('pegawai_type');
        const checkboxContainer = document.getElementById('pegawai_checkbox_container');
        const dokumenForm = document.getElementById('dokumenForm');
        const btnSubmit = document.getElementById('btnSubmit');
        const periodeTipe = document.getElementById('periode_tipe');
        const namaDokumen = document.getElementById('nama_dokumen');

        function togglePegawaiCheckbox() {
            checkboxContainer.style.display = 'block'; // selalu tampil

            if (pegawaiType.value === 'all') {
                checkboxContainer.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = true);
            } else if (pegawaiType.value === 'specific') {
                checkboxContainer.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
            }
        }
        pegawaiType.addEventListener('change', togglePegawaiCheckbox);
        window.addEventListener('DOMContentLoaded', togglePegawaiCheckbox);

        // Konfirmasi submit + cek field wajib
        btnSubmit.addEventListener('click', function(e) {
            e.preventDefault();

            if (namaDokumen.value.trim() === '') {
                Swal.fire({ icon: 'warning', title: 'Peringatan', text: 'Nama Dokumen wajib diisi.' });
                return;
            }

            if (periodeTipe.value === '') {
                Swal.fire({ icon: 'warning', title: 'Peringatan', text: 'Silakan pilih Tipe Periode terlebih dahulu.' });
                return;
            }

            const isChecked = Array.from(checkboxContainer.querySelectorAll('input[type="checkbox"]'))
                                   .some(cb => cb.checked);

            if (!isChecked) {
                Swal.fire({ icon: 'warning', title: 'Peringatan', text: 'Minimal pilih 1 pegawai.' });
                return;
            }

            Swal.fire({
                title: 'Yakin membuat dokumen?',
                text: "Pastikan data sudah benar.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Buat!'
            }).then((result) => {
                if (result.isConfirmed) {
                    dokumenForm.submit();
                }
            });
        });

        // Konfirmasi hapus
        document.querySelectorAll('.btn-hapus').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const form = this.closest('form');
                Swal.fire({
                    title: 'Yakin ingin hapus?',
                    text: "Data tidak bisa dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
</x-layout>
