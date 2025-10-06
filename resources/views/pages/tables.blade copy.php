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
                            <div class="table-responsive p-0">
                                <form method="GET" action="{{ route('dokumen.index') }}" class="row mb-3">
                                    <div class="col-md-3">
                                        <select name="jenis_dokumen_id" class="form-select">
                                            <option value="">-- Semua Jenis --</option>
                                            @foreach($jenisDokumen as $jenis)
                                                <option value="{{ $jenis->id }}" {{ request('jenis_dokumen_id') == $jenis->id ? 'selected' : '' }}>
                                                    {{ $jenis->nama_dokumen }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select name="jenis_dokumen_id" class="form-select">
                                            <option value="">-- Tahun --</option>
                                            @foreach($jenisDokumen as $jenis)
                                                <option value="{{ $jenis->id }}" {{ request('jenis_dokumen_id') == $jenis->id ? 'selected' : '' }}>
                                                    {{ $jenis->nama_dokumen }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select name="periode_id" class="form-select">
                                            <option value="">-- Semua Periode --</option>
                                            @foreach($periode as $p)
                                                <option value="{{ $p->id }}" {{ request('periode_id') == $p->id ? 'selected' : '' }}>
                                                    {{ $p->nama_periode }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-primary">Filter</button>
                                        <a href="{{ route('dokumen.index') }}" class="btn btn-secondary">Reset</a>
                                    </div>
                                </form>
                                @if(($dokumens ?? null) && $dokumens->count())
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Jenis Dokumen
                                            </th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                Periode
                                            </th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Tahun
                                            </th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Tanggal Unggah
                                            </th>
                                            <th class="text-secondary opacity-7"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- @foreach($dokumens as $d)
                                        <tr>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    {{ $d->jenisDokumen->nama_dokumen }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    {{ $d->periode->tipe }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="justify-content-center d-flex px-2 py-1">
                                                    {{ $d->periode->tahun }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="justify-content-center d-flex px-2 py-1">
                                                    {{ $d->tanggal_unggah }}
                                                </div>
                                            </td> --}}
                                            @forelse ($dokumen as $index => $d)
                                                <tr>
                                                    <td hidden>{{ $dokumen->firstItem() + $index }}</td>
                                                    <td>{{ $d->jenisDokumen->nama_dokumen ?? '-' }}</td>
                                                    <td>{{ $d->periode->tipe ?? '-' }}</td>
                                                    <td>
                                                        <div class="justify-content-center d-flex px-2 py-1">
                                                            {{ $d->periode->tahun ?? '-' }}</td>
                                                        </div>
                                                    <td>
                                                        <div class="justify-content-center d-flex px-2 py-1">
                                                            {{ $d->tanggal_unggah }}
                                                        </div>
                                                    </td>
                                                    <td class="align-middle">
                                                        <a href="{{ route('dokumen.preview', $d->id) }}"
                                                           target="_blank"
                                                           class="btn btn-primary btn-sm">
                                                            Preview
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center">Tidak ada data</td>
                                                </tr>
                                            @endforelse
                                        {{-- </tr>
                                        @endforeach --}}
                                    </tbody>
                                </table>
                                {{-- Pagination --}}
                                <div class="d-flex justify-content-center">
                                    {{ $dokumen->links() }}
                                </div>
                                @else
                                <div class="justify-content-center d-flex px-2 py-1">
                                    Tidak ada dokumen
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        

            <x-footers.auth></x-footers.auth>
        </div>
    </main>

    <x-plugins></x-plugins>
</x-layout>
