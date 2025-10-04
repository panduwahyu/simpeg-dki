<x-layout bodyClass="g-sidenav-show bg-gray-200">
    <x-navbars.sidebar activePage="pegawai_dashboard"></x-navbars.sidebar>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-navbars.navs.auth titlePage="Monitoring"></x-navbars.navs.auth>

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
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama Dokumen</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Periode</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($belumUpload as $item)
                                    <tr>
                                        <td>
                                            <h6 class="mx-3  mb-0 text-sm">     
                                                {{ $item->nama_dokumen }}
                                            </h6>
                                        </td>
                                        <td>
                                            <h6 class="mx-3  mb-0 text-sm"> 
                                                {{ $item->periode_key }}
                                            </h6>
                                        </td>
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
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama Dokumen</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Periode</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tanggal Upload</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($uploads as $item)
                                    <tr>
                                        <td>
                                            <h6 class="mx-3  mb-0 text-sm">    
                                                {{ $item->nama_dokumen }}
                                            </h6>
                                        </td>
                                        <td>
                                            <h6 class="mx-3  mb-0 text-sm">
                                                {{ $item->periode_key }}
                                            </h6>
                                        </td>
                                        <td>
                                            @if($item->is_uploaded == 1)
                                                <span class="mx-1 badge bg-success">Sudah</span>
                                            @else
                                                <span class="mx-1 badge bg-danger">Belum</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
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
