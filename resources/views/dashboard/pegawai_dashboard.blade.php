<x-layout bodyClass="g-sidenav-show bg-gray-200">
    <x-navbars.sidebar activePage="pegawai_dashboard"></x-navbars.sidebar>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-navbars.navs.auth titlePage="Dashboard Pegawai"></x-navbars.navs.auth>

        <div class="container-fluid py-4">

            <!-- Ringkasan Progres -->
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Ringkasan Progres Upload</h6>
                </div>
                <div class="card-body">
                    @php
                        $total = $ringkasan['total'];
                        $sudah = $ringkasan['sudah'];
                        $belum = $ringkasan['belum'];
                        $percent = $total > 0 ? round(($sudah / $total) * 100, 2) : 0;
                    @endphp

                    <h5>{{ $percent }}% Sudah Mengumpulkan</h5>
                    <div class="progress mb-3" style="height: 20px;">
                        <div class="progress-bar bg-gradient-info" role="progressbar"
                            style="width: {{ $percent }}%" aria-valuenow="{{ $percent }}"
                            aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                    <p>Total: {{ $sudah }} / {{ $total }} file | Belum: {{ $belum }}</p>
                </div>
            </div>

            <!-- Daftar File Belum Diunggah -->
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>File Belum Diunggah</h6>
                </div>
                <div class="card-body px-0 pb-2">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th>Nama Dokumen</th>
                                    <th>Periode</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($belumUpload as $item)
                                    <tr>
                                        <td>{{ $item->nama_dokumen }}</td>
                                        <td>{{ $item->periode_key }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center">Semua dokumen sudah diunggah</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Daftar Semua Dokumen -->
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Daftar Semua Dokumen</h6>
                </div>
                <div class="card-body px-0 pb-2">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th>Nama Dokumen</th>
                                    <th>Periode</th>
                                    <th>Status</th>
                                    <th>Tanggal Upload</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($uploads as $item)
                                    <tr>
                                        <td>{{ $item->nama_dokumen }}</td>
                                        <td>{{ $item->periode_key }}</td>
                                        <td>
                                            @if($item->is_uploaded == 1)
                                                <span class="badge bg-success">Sudah</span>
                                            @else
                                                <span class="badge bg-danger">Belum</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $item->tanggal_upload ? \Carbon\Carbon::parse($item->tanggal_upload)->format('d/m/Y') : '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <x-footers.auth></x-footers.auth>
        </div>
    </main>

    <x-plugins></x-plugins>
</x-layout>
