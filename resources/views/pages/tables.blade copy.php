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
                                            <label for="tipe" class="form-label small mb-1">Tipe</label>
                                            <select name="tipe" id="tipe" class="form-select" style="min-width: 130px;">
                                                <option value="">-- Semua Tipe --</option>
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
                                            <th>Tipe Periode</th>
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

        </div>
    </main>
    <x-plugins></x-plugins>
</x-layout>
