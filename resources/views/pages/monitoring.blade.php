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
                    <form class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="nama_dokumen" class="form-label">Nama Dokumen</label>
                            <select id="nama_dokumen" class="form-select p-2">
                                <option value="" selected disabled>Pilih Dokumen</option>
                                @foreach($dokumenList as $nama)
                                    <option value="{{ $nama }}" {{ $selectedDokumen == $nama ? 'selected' : '' }}>
                                        {{ $nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="tahun" class="form-label">Tahun</label>
                            <input type="text" id="tahun" class="form-control p-2" readonly value="{{ $selectedTahun ?? '' }}">
                        </div>

                        <div class="col-md-4">
                            <label for="periode" class="form-label">Periode</label>
                            <input type="text" id="periode" class="form-control p-2" readonly value="{{ $selectedPeriode ?? '' }}">
                        </div>
                    </form>

                    {{-- TABEL MONITORING --}}
                    <div class="mb-4">
                        <div class="table-responsive custom-table-wrapper">
                            @if(!empty($monitoring))
                                @include('pages.monitoring.table', ['monitoring' => $monitoring, 'selectedTahun' => $selectedTahun])
                            @else
                                <p class="text-center text-muted">Silakan pilih nama dokumen terlebih dahulu.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <x-plugins></x-plugins>

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    {{-- CSS Sticky --}}
    <style>
        .custom-table-wrapper {
            overflow-x:auto;
            max-height:500px;
            border:1px solid #dee2e6;
            border-radius:8px;
        }

        .table thead th {
            position: sticky;
            top:0;
            background:#f8f9fa;
            z-index:3;
        }

        .sticky-col {
            position: sticky;
            left:0;
            z-index:5;
            background:#fff;
        }

        .table th, .table td {
            white-space: nowrap;
            vertical-align: middle;
        }

        .table thead tr:nth-child(2) th {
            background: #f8f9fa;
            z-index: 3;
        }

        .table tbody tr:nth-child(even) td {
            background-color: #fcfcfc;
        }

        .table thead th:first-child {
            z-index: 6;
            background:#e9ecef;
        }
    </style>

    {{-- JS Auto Update Tabel --}}
    <script>
        const tableWrapper = document.querySelector('.custom-table-wrapper');

        const bulanIndonesia = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const triwulanLabel = ['Triwulan 1', 'Triwulan 2', 'Triwulan 3', 'Triwulan 4'];

        document.getElementById('nama_dokumen').addEventListener('change', function() {
            const namaDokumen = this.value;
            if (!namaDokumen) return;

            fetch(`/monitoring/data/${namaDokumen}`)
                .then(res => res.json())
                .then(data => {
                    // Update tahun & periode
                    document.getElementById('tahun').value = data.tahun;
                    document.getElementById('periode').value = data.periode_tipe;

                    // Kosongkan tabel jika tidak ada data
                    if (data.monitoring.tabel.length === 0) {
                        tableWrapper.innerHTML = '<p class="text-center text-muted">Data monitoring kosong.</p>';
                        return;
                    }

                    // Bangun tabel HTML
                    let html = '<table class="table border border-1 border-secondary-subtle text-center align-middle">';
                    html += '<thead class="bg-light"><tr>';
                    html += '<th class="sticky-col bg-white fw-bold" ';

                    let periode = data.periode_tipe.toLowerCase();
                    if(periode === 'tahunan') {
                        html += 'rowspan="1">Nama Pegawai</th>';
                    } else {
                        html += 'rowspan="2">Nama Pegawai</th>';
                    }

                    // Header baris 1
                    if(periode === 'bulanan') {
                        html += `<th colspan="12">${data.tahun}</th>`;
                    } else if(periode === 'triwulanan') {
                        html += `<th colspan="4">${data.tahun}</th>`;
                    } else if(periode === 'tahunan') {
                        html += `<th>${data.tahun}</th>`;
                    }

                    html += '</tr>';

                    // Header baris 2
                    if(periode === 'bulanan') {
                        html += '<tr>';
                        bulanIndonesia.forEach(b => html += `<th>${b}</th>`);
                        html += '</tr>';
                    } else if(periode === 'triwulanan') {
                        html += '<tr>';
                        triwulanLabel.forEach(t => html += `<th>${t}</th>`);
                        html += '</tr>';
                    }

                    html += '</thead><tbody>';

                    data.monitoring.tabel.forEach(row => {
                        html += '<tr>';
                        html += `<td class="sticky-col bg-white fw-bold">${row.nama}</td>`;

                        if(periode === 'bulanan') {
                            for(let i=1;i<=12;i++){
                                let status = row[i] ?? 0;
                                html += status==1 
                                    ? '<td><i class="fas fa-check-circle text-success"></i></td>' 
                                    : '<td><i class="fas fa-times-circle text-danger"></i></td>';
                            }
                        } else if(periode === 'triwulanan') {
                            for(let i=1;i<=4;i++){
                                let status = row['Triwulan '+i] ?? 0;
                                html += status==1 
                                    ? '<td><i class="fas fa-check-circle text-success"></i></td>' 
                                    : '<td><i class="fas fa-times-circle text-danger"></i></td>';
                            }
                        } else if(periode === 'tahunan') {
                            let status = row.tahun ?? 0;
                            html += status==1 
                                ? '<td><i class="fas fa-check-circle text-success"></i></td>' 
                                : '<td><i class="fas fa-times-circle text-danger"></i></td>';
                        }

                        html += '</tr>';
                    });

                    html += '</tbody></table>';
                    tableWrapper.innerHTML = html;
                });
        });
    </script>
</x-layout>
