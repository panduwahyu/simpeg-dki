<x-layout bodyClass="g-sidenav-show bg-gray-200">
    <x-navbars.sidebar activePage="monitoring-pegawai"></x-navbars.sidebar>

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-navbars.navs.auth titlePage="Monitoring Dokumen"></x-navbars.navs.auth>

        <div class="container-fluid py-4">
            <div class="card mb-4 shadow-sm">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6>Monitoring Kelengkapan Dokumen</h6>
                </div>

                <div class="card-body">
                    {{-- FILTER FORM --}}
                    <form method="GET" action="{{ route('monitoring.index') }}" class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="nama_dokumen" class="form-label">Nama Dokumen</label>
                            <select name="nama_dokumen" id="nama_dokumen" class="form-select p-2" required>
                                @foreach($dokumenList as $nama)
                                    <option value="{{ $nama }}" {{ $selectedDokumen == $nama ? 'selected' : '' }}>
                                        {{ $nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="tahun" class="form-label">Tahun</label>
                            <select name="tahun" id="tahun" class="form-select p-2" required>
                                @foreach($tahunList as $t)
                                    <option value="{{ $t }}" {{ $selectedTahun == $t ? 'selected' : '' }}>
                                        {{ $t }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-4 align-self-end">
                                <button type="submit" class="btn bg-gradient-info w-100">Tampilkan</button>
                            </div>
                        </div>
                    </form>

                    {{-- TABEL MONITORING --}}
                    @if(!empty($monitoring))
                        <div class="mb-4">
                            <h5 class="mb-3 text-primary">
                                {{ strtoupper($selectedDokumen) }} - {{ $selectedTahun }}
                            </h5>

                            <div class="table-responsive custom-table-wrapper">
                                <table class="table border border-1 border-secondary-subtle text-center align-middle">
                                    <thead class="bg-light">
                                        {{-- HEADER BARIS 1 --}}
                                        <tr>
                                            <th rowspan="2" class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 sticky-col align-middle">
                                                Nama Pegawai
                                            </th>

                                            @if(!empty($monitoring['triwulan']))
                                                <th colspan="{{ count($monitoring['triwulan']) }}" class="text-center text-secondary text-xs fw-bold bg-light">
                                                    Triwulanan
                                                </th>
                                            @else
                                                <th colspan="{{ count($monitoring['bulan']) }}" class="text-center text-secondary text-xs fw-bold bg-light">
                                                    Bulanan
                                                </th>
                                                <th colspan="1" class="text-center text-secondary text-xs fw-bold bg-light">
                                                    Tahunan
                                                </th>
                                            @endif
                                        </tr>

                                        {{-- HEADER BARIS 2 --}}
                                        <tr>
                                            @if(!empty($monitoring['triwulan']))
                                                @foreach($monitoring['triwulan'] as $p)
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">{{ $p->label }}</th>
                                                @endforeach
                                            @else
                                                @foreach($monitoring['bulan'] as $p)
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                        {{ DateTime::createFromFormat('!m', $p->bulan)->format('M') }}
                                                    </th>
                                                @endforeach
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">{{ $selectedTahun }}</th>
                                            @endif
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach($monitoring['tabel'] as $row)
                                            <tr>
                                                <td class="fw-bold text-sm sticky-col bg-white">
                                                    {{ $row['nama'] }}
                                                </td>

                                                @if(!empty($monitoring['triwulan']))
                                                    @foreach($monitoring['triwulan'] as $p)
                                                        @php $status = $row[$p->label] ?? 0; @endphp
                                                        <td>
                                                            @if($status == 1)
                                                                <i class="fas fa-check-circle text-success"></i>
                                                            @else
                                                                <i class="fas fa-times-circle text-danger"></i>
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                @else
                                                    @foreach($monitoring['bulan'] as $p)
                                                        @php $status = $row[$p->bulan] ?? 0; @endphp
                                                        <td>
                                                            @if($status == 1)
                                                                <i class="fas fa-check-circle text-success"></i>
                                                            @else
                                                                <i class="fas fa-times-circle text-danger"></i>
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                    @php $statusTahun = $row['tahun'] ?? 0; @endphp
                                                    <td>
                                                        @if($statusTahun == 1)
                                                            <i class="fas fa-check-circle text-success"></i>
                                                        @else
                                                            <i class="fas fa-times-circle text-danger"></i>
                                                        @endif
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <p class="text-center text-muted">Silakan pilih nama dokumen dan tahun terlebih dahulu.</p>
                    @endif
                </div>
            </div>
        </div>
    </main>

    <x-plugins></x-plugins>

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    {{-- Sticky Table CSS --}}
    <style>
        .custom-table-wrapper {
            overflow-x: auto;
            overflow-y: auto;
            max-height: 500px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
        }

        .table thead th {
            position: sticky;
            top: 0;
            background: #f8f9fa;
            z-index: 3;
        }

        .sticky-col {
            position: sticky;
            left: 0;
            z-index: 4;
            background: #fff;
        }

        .table th, .table td {
            white-space: nowrap;
            vertical-align: middle;
        }

        .table tbody tr:nth-child(even) td {
            background-color: #fcfcfc;
        }

        .table thead th:first-child {
            z-index: 5;
            background: #e9ecef;
        }
    </style>
</x-layout>
