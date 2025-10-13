<x-layout bodyClass="g-sidenav-show  bg-gray-200">
    <x-navbars.sidebar activePage="tables"></x-navbars.sidebar>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <x-navbars.navs.auth titlePage="Dokumen"></x-navbars.navs.auth>
        <!-- End Navbar -->
        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                                <h6 class="text-white text-capitalize ps-3">Daftar Dokumen</h6>
                            </div>
                        </div>
                        <div class="card-body px-4 pb-4">
                            {{-- Form Filter --}}
                            <div class="row align-items-end mb-4 g-3">
                                <form method="GET" action="{{ route('dokumen.index') }}" class="d-flex flex-wrap align-items-end justify-content-between gap-3">

                                    {{-- Kolom Filter --}}
                                    <div class="d-flex flex-wrap gap-3">
                                        @if (in_array(Auth::user()->role, ['Admin', 'Supervisor']))
                                        <div>
                                            <label for="user_id" class="form-label small mb-1">Nama Pegawai</label>
                                            <select name="user_id" class="form-select" style="min-width: 180px;">
                                                <option value="">-- Semua Pegawai --</option>
                                                @foreach ($pegawai as $u)
                                                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                                        {{ $u->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @endif

                                        <div>
                                            <label for="jenis_dokumen_id" class="form-label small mb-1">Jenis Dokumen</label>
                                            <select name="jenis_dokumen_id" id="jenis_dokumen_id" class="form-select" style="min-width: 180px;">
                                                <option value="">-- Semua Jenis Dokumen --</option>
                                                @foreach($jenisDokumen as $jenis)
                                                    <option value="{{ $jenis->id }}" {{ request('jenis_dokumen_id') == $jenis->id ? 'selected' : '' }}>
                                                        {{ $jenis->nama_dokumen }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label for="tipe" class="form-label small mb-1">Periode</label>
                                            <select name="tipe" id="tipe" class="form-select" style="min-width: 130px;">
                                                <option value="">-- Semua Periode --</option>
                                                @foreach($periode->unique('tipe') as $p)
                                                    <option value="{{ $p->tipe }}" {{ request('tipe') == $p->tipe ? 'selected' : '' }}>
                                                        {{ $p->tipe }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label for="tahun" class="form-label small mb-1">Tahun</label>
                                            <select name="tahun" id="tahun" class="form-select" style="min-width: 130px;">
                                                <option value="">-- Semua Tahun --</option>
                                                @foreach($periode->unique('tahun') as $p)
                                                    <option value="{{ $p->tahun }}" {{ request('tahun') == $p->tahun ? 'selected' : '' }}>
                                                        {{ $p->tahun }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Tombol Aksi --}}
                                    <div class="d-flex flex-wrap gap-2 justify-content-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-funnel me-1"></i>Filter
                                        </button>
                                        <a href="{{ route('dokumen.index') }}" class="btn btn-secondary">
                                            <i class="bi bi-arrow-clockwise me-1"></i>Reset
                                        </a>
                                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#tambahDokumenModal">
                                            <i class="bi bi-plus-circle me-1"></i>Tambah Dokumen
                                        </button>
                                    </div>
                                </form>
                            </div>

                            {{-- Tabel --}}
                            <div class="table-responsive mt-4">
                                <table class="table table-bordered table-striped align-middle mb-0">
                                    <thead class="table-dark text-center">
                                        <tr>
                                            <th>Nama Pegawai</th>
                                            <th>Jenis Dokumen</th>
                                            <th>Periode</th>
                                            <th>Tahun</th>
                                            <th>Tanggal Upload</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($dokumen as $index => $d)
                                            <tr>
                                                <td>{{ $d->pegawai->name ?? '-' }}</td>
                                                <td>{{ $d->jenisDokumen->nama_dokumen ?? '-' }}</td>
                                                <td>{{ $d->periode->tipe ?? '-' }}</td>
                                                <td class="text-center">{{ $d->periode->tahun ?? '-' }}</td>
                                                <td class="text-center">{{ $d->tanggal_unggah }}</td>
                                                <td class="text-center">
                                                    <a href="{{ route('dokumen.preview', $d->id) }}" target="_blank" class="btn btn-sm btn-info">
                                                        Preview
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center">Tidak ada data ditemukan</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-center mt-3">
                                {{ $dokumen->links() }}
                            </div>
                        </div>

                    </div>
                    <div class="modal fade" id="tambahDokumenModal" tabindex="-1" aria-labelledby="tambahDokumenLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="tambahDokumenLabel">
                                        Tambah Dokumen
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                    </button>
                                </div>
                                {{-- modal upload dokumen popup --}}
                                <div class="modal-body">
                                    <form id="formTambahDokumen" action="{{ route('dokumen.store') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        
                                        {{-- Menampilkan pesan sukses --}}
                                        @if(session('success'))
                                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                            {{ session('success') }}
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                        @endif

                                        {{-- Menampilkan pesan error --}}
                                        @if(session('error'))
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            {{ session('error') }}
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                        @endif

                                        <div class="mb-3">
                                            <label for="nama_pegawai" class="form-label">
                                                Nama Pegawai
                                            </label>
                                            @if (in_array(Auth::user()->role, ['Admin', 'Supervisor']))
                                            <select name="nama_pegawai" id="nama_pegawai" class="form-select @error('nama_pegawai') is-invalid @enderror" required>
                                                <option value="">
                                                    -- Pilih Pegawai --
                                                </option>
                                                @foreach ($pegawai as $u)
                                                    <option value="{{ $u->id }}" {{ old('nama_pegawai', request('user_id')) == $u->id ? 'selected' : '' }}>
                                                        {{ $u->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('nama_pegawai')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            @else
                                            <input type="hidden" name="nama_pegawai" value="{{ Auth::user()->id }}">
                                            <input type="text" class="form-control" value="{{ Auth::user()->name }}" disabled>
                                            @endif
                                        </div>

                                        <div class="mb-3">
                                            <label for="jenis_dokumen_id" class="form-label">
                                                Nama Dokumen
                                            </label>
                                            <select name="jenis_dokumen_id" id="jenis_dokumen_id" class="form-select @error('jenis_dokumen_id') is-invalid @enderror" required>
                                                <option value="">
                                                    -- Pilih nama dokumen --
                                                </option>
                                                @foreach($jenisDokumen as $jenis)
                                                <option value="{{ $jenis->id }}" {{ old('jenis_dokumen_id', request('jenis_dokumen_id')) == $jenis->id ? 'selected' : '' }}>
                                                    {{ $jenis->nama_dokumen }}
                                                </option>
                                                @endforeach
                                            </select>
                                            @error('jenis_dokumen_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="periode" class="form-label">
                                                Tahun
                                            </label>
                                            <select name="periode" id="periode" class="form-select @error('periode') is-invalid @enderror" required>
                                                <option value="">
                                                    -- Pilih tahun --
                                                </option>
                                                @foreach($periode->unique('tahun') as $p)
                                                    <option value="{{ $p->tahun }}" {{ old('periode', request('tahun')) == $p->tahun ? 'selected' : '' }}>
                                                        {{ $p->tahun }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('periode')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="tipe" class="form-label">
                                                Periode
                                            </label>
                                            <select name="tipe" id="tipe" class="form-select @error('tipe') is-invalid @enderror" required>
                                                <option value="">
                                                    -- Pilih periode --
                                                </option>
                                                @foreach($periode->unique('tipe') as $p)
                                                    <option value="{{ $p->tipe }}" {{ old('tipe', request('tipe')) == $p->tipe ? 'selected' : '' }}>
                                                        {{ $p->tipe }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('tipe')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="penilai_id" class="form-label">
                                                Ditandatangani oleh
                                            </label>
                                            <select name="penilai_id" id="penilai_id" class="form-select @error('penilai_id') is-invalid @enderror" required>
                                                <option value="">
                                                    -- Pilih penandatangan --
                                                </option>
                                                <option value="" {{ old('penilai_id') == '' ? 'selected' : '' }}>Pegawai</option>
                                                <option value="1" {{ old('penilai_id') == '1' ? 'selected' : '' }}>Pegawai dan Pejabat</option>
                                            </select>
                                            @error('penilai_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="inputGroupFile01" class="form-label fw-semibold">Upload File</label>
                                            <input type="file" class="form-control border border-secondary rounded-3 shadow-sm @error('pdf_file') is-invalid @enderror" id="inputGroupFile01" name="pdf_file" accept=".pdf" required>
                                            <div class="form-text text-muted">
                                                📄 Pastikan file berformat <strong>PDF</strong> dengan ukuran <strong>&lt; 5MB</strong>.
                                            </div>
                                            @error('pdf_file')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <button type="submit" class="btn btn-success w-100" id="btnSubmit">
                                            <span id="btnText">&#128190; Simpan</span>
                                            <span id="btnSpinner" class="d-none"> <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Menyimpan... </span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
            </div>

            <style>
                /* Jarak antara card dan tabel */
                .card-body {
                    padding-top: 2rem !important;
                }

                /* Supaya tabel tidak menempel pada sisi card */
                .table-responsive {
                    padding: 0 10px;
                }

                /* Atur spasi antar elemen filter */
                form .form-select,
                form .form-label {
                    font-size: 0.9rem;
                }

                /* Responsif: tombol & filter tetap sejajar */
                @media (max-width: 768px) {
                    form.d-flex {
                        flex-direction: column;
                        align-items: stretch;
                    }
                    form .d-flex.flex-wrap.gap-2 {
                        justify-content: stretch;
                    }
                }
            </style>

            <script>
            document.getElementById('inputGroupFile01').addEventListener('change', function() {
                const file = this.files[0];
                const errorDiv = document.getElementById('fileError');
                const input = this;
                errorDiv.textContent = ''; // reset pesan error
                input.classList.remove('is-invalid'); // reset status error

                if (!file) return; // kalau belum pilih file, abaikan

                const maxSize = 5 * 1024 * 1024; // 5 MB
                const fileType = file.type;

                // Cek tipe file
                if (fileType !== 'application/pdf') {
                    input.classList.add('is-invalid');
                    errorDiv.textContent = '❌ Hanya file berformat PDF yang diperbolehkan.';
                    input.value = ''; // reset input
                    return;
                }

                // Cek ukuran file
                if (file.size > maxSize) {
                    input.classList.add('is-invalid');
                    errorDiv.textContent = '⚠️ Ukuran file melebihi 5MB. Silakan pilih file yang lebih kecil.';
                    input.value = ''; // reset input
                    return;
                }

                // Jika lolos semua
                input.classList.remove('is-invalid');
                errorDiv.textContent = '';
            });
            @if(session('success'))
                document.getElementById('formTambahDokumen').reset();
            @endif

            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('formTambahDokumen');
                const btnSubmit = document.getElementById('btnSubmit');
                const btnText = document.getElementById('btnText');
                const btnSpinner = document.getElementById('btnSpinner');

                form.addEventListener('submit', function(e) {
                    e.preventDefault(); // cegah reload halaman

                    // ganti tampilan tombol
                    btnText.classList.add('d-none');
                    btnSpinner.classList.remove('d-none');
                    btnSubmit.disabled = true;

                    // ambil data form
                    const formData = new FormData(form);

                    fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                    })
                    .then(response => response.json())
                    .then(data => {
                        // kembalikan tombol ke semula
                        btnText.classList.remove('d-none');
                        btnSpinner.classList.add('d-none');
                        btnSubmit.disabled = false;

                        // hapus alert lama
                        const oldAlerts = form.querySelectorAll('.alert');
                        oldAlerts.forEach(a => a.remove());

                        // tampilkan feedback sukses/gagal
                        let alertDiv = document.createElement('div');
                        alertDiv.classList.add('alert', 'alert-dismissible', 'fade', 'show', 'mt-2');
                        alertDiv.setAttribute('role', 'alert');

                        if (data.success) {
                            alertDiv.classList.add('alert-success');
                            alertDiv.innerHTML = data.message || '✅ Dokumen berhasil disimpan.';
                            form.reset(); // kosongkan form setelah berhasil
                        } else {
                            alertDiv.classList.add('alert-danger');
                            alertDiv.innerHTML = data.message || '❌ Gagal menyimpan dokumen.';
                        }

                        // tombol tutup alert
                        alertDiv.innerHTML += `
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        `;
                        form.prepend(alertDiv);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        btnText.classList.remove('d-none');
                        btnSpinner.classList.add('d-none');
                        btnSubmit.disabled = false;
                        
                        // tampilkan pesan error
                        let alertDiv = document.createElement('div');
                        alertDiv.classList.add('alert', 'alert-danger', 'alert-dismissible', 'fade', 'show', 'mt-2');
                        alertDiv.innerHTML = 'Terjadi kesalahan pada server.';
                        alertDiv.innerHTML += `
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        `;
                        form.prepend(alertDiv);
                    });
                });
            });
            </script>

        </div>
    </main>
    <x-plugins></x-plugins>
</x-layout>
