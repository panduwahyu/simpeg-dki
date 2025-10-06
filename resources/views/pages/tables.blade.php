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
                        <div class="card-body px-0 pb-2">
                            {{-- Form Filter --}}
                            <div class="container-fluid px-3 mb-4">
                                <form method="GET" action="{{ route('dokumen.index') }}" class="row g-3 align-items-end justify-content-center">
                                    <div class="col-12 col-md-6 col-lg-3">
                                        <label for="jenis_dokumen_id" class="form-label small mb-1">Jenis Dokumen</label>
                                        <select name="jenis_dokumen_id" id="jenis_dokumen_id" class="form-select">
                                            <option value="">-- Semua Jenis Dokumen --</option>
                                            @foreach($jenisDokumen as $jenis)
                                                <option value="{{ $jenis->id }}" {{ request('jenis_dokumen_id') == $jenis->id ? 'selected' : '' }}>
                                                    {{ $jenis->nama_dokumen }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-6 col-md-3 col-lg-2">
                                        <label for="tipe" class="form-label small mb-1">Tipe</label>
                                        <select name="tipe" id="tipe" class="form-select">
                                            <option value="">-- Semua Tipe --</option>
                                            @foreach($periode->unique('tipe') as $p)
                                                <option value="{{ $p->tipe }}" {{ request('tipe') == $p->tipe ? 'selected' : '' }}>
                                                    {{ $p->tipe }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-6 col-md-3 col-lg-2">
                                        <label for="tahun" class="form-label small mb-1">Tahun</label>
                                        <select name="tahun" id="tahun" class="form-select">
                                            <option value="">-- Semua Tahun --</option>
                                            @foreach($periode->unique('tahun') as $p)
                                                <option value="{{ $p->tahun }}" {{ request('tahun') == $p->tahun ? 'selected' : '' }}>
                                                    {{ $p->tahun }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-6 col-lg-3">
                                        <div class="d-flex gap-2 justify-content-center justify-content-lg-start">
                                            <button type="submit" class="btn btn-primary flex-fill flex-lg-grow-0" style="min-width: 100px;">
                                                <i class="bi bi-funnel me-1"></i>Filter
                                            </button>
                                            <a href="{{ route('dokumen.index') }}" class="btn btn-secondary flex-fill flex-lg-grow-0" style="min-width: 100px;">
                                                <i class="bi bi-arrow-clockwise me-1"></i>Reset
                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            {{-- Tabel Data Dokumen --}}
                            <table class="table table-bordered table-striped align-middle">
                                <thead class="table-dark text-center">
                                    <tr>
                                        <th hidden>#</th>
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
                                            <td hidden>{{ $dokumen->firstItem() + $index }}</td>
                                            <td>{{ $d->jenisDokumen->nama_dokumen ?? '-' }}</td>
                                            <td>{{ $d->periode->tipe ?? '-' }}</td>
                                            <td>
                                                <div class="d-flex justify-content-center px-2 py-1">
                                                    {{ $d->periode->tahun ?? '-' }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-center px-2 py-1">
                                                    {{ $d->tanggal_unggah }}
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('dokumen.preview', $d->id) }}"
                                                target="_blank"
                                                class="btn btn-sm btn-primary">
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
                             {{-- Pagination --}}
                            <div class="d-flex justify-content-center">
                                {{ $dokumen->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

             <style>
                /* Tambahkan padding agar placeholder tidak menempel ke border */
                select.form-select {
                    padding-left: 12px; /* jarak kiri */
                    padding-right: 12px; /* jarak kanan */
                }

                /* Opsional: buat warna placeholder sedikit lebih pucat */
                select.form-select option[value=""] {
                    color: #6c757d; /* abu-abu */
                }
            </style>

            <x-footers.auth></x-footers.auth>
        </div>
    </main>
    <x-plugins></x-plugins>
</x-layout>
