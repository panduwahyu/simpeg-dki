{{-- <script>
            document.addEventListener('DOMContentLoaded', function() {
                const dokumenSelect = document.getElementById('dokumenSelect');
                const periodeSelect = document.getElementById('periodeSelect');
                const showDetailsBtn = document.getElementById('showDetails');
                
                // Jika belum dipilih, sembunyikan ringkasan & detail
                function toggleShowDetailsButton() {
                    if (dokumenSelect.value !=0 && periodeSelect.value != 0) {
                        showDetailsBtn.classList.remove('d-none');
                    } else {
                        showDetailsBtn.classList.add('d-none');
                    }
                }

                [dokumenSelect, periodeSelect].forEach(select => {
                    select.addEventListener('change', () => {
                        toggleShowDetailsButton();
                        loadData();
                    });
                });


                function loadData() {
                    const dokumenId = dokumenSelect.value;
                    const periodeId = periodeSelect.value;

                    fetch(`/dashboard/filter?dokumen_id=${dokumenId}&periode_id=${periodeId}`)
                        .then(res => res.json())
                        .then(data => {
                            // Update progres
                            document.querySelector('.progress-bar').style.width = data.progressSummary.percent + '%';
                            document.querySelector('.progress-bar').setAttribute('aria-valuenow', data.progressSummary.percent);
                            document.getElementById('progressText').innerText = data.progressSummary.percent + '% Sudah Mengumpulkan';
                            document.getElementById('progressCount').innerText = `Total: ${data.progressSummary.done} / ${data.progressSummary.total} pegawai`;

                            // Update tabel ringkasan
                            const summaryBody = document.querySelector('#summaryTable tbody');
                            summaryBody.innerHTML = '';
                            data.summaryData.forEach(item => {
                                summaryBody.innerHTML += `
                                    <tr>
                                        <td>
                                            <h6 class="mx-3  mb-0 text-sm">
                                                <a href="#" class="dokumen-link" data-dokumen="${item.dokumen_id}" data-periode="${item.periode_id}">
                                                    ${item.nama_dokumen}
                                                </a>
                                            </h6>
                                        </td>
                                        <td>
                                            <h6 class="mx-3  mb-0 text-sm">
                                            ${item.periode}
                                            </h6>
                                        </td>
                                        <td>
                                            <h6 class="mx-3  mb-0 text-sm">
                                            ${item.done}
                                            </h6>
                                        </td>
                                        <td>
                                            <h6 class="mx-3  mb-0 text-sm">
                                            ${item.total}
                                            </h6>
                                        </td>
                                        <td>
                                            <h6 class="mx-3  mb-0 text-sm">
                                                <small>${item.percent}%</small>
                                            </h6>
                                        </td>
                                    </tr>
                                `;
                            });

                            // Event klik nama dokumen untuk tampilkan detail pegawai
                            document.querySelectorAll('.dokumen-link').forEach(link => {
                                link.addEventListener('click', function (e) {
                                    e.preventDefault();
                                    const dokumenId = this.dataset.dokumen;
                                    const periodeId = this.dataset.periode;
                                    loadPegawai(dokumenId, periodeId);
                                });
                            });

                            // Update tabel pegawai
                            const tbody = document.querySelector('#pegawaiCard tbody');
                            tbody.innerHTML = '';
                            data.pegawaiData.forEach(p => {
                                tbody.innerHTML += `
                                    <tr>
                                        <td><h6 class="mx-3  mb-0 text-sm"> ${p.nama}</h6></td>
                                        <td><h6 class="mb-0 text-sm"> ${p.unit_kerja}</h6></td>
                                        <td>
                                            ${p.is_uploaded === 1
                                                ? '<span class="mx-1 badge bg-success">Sudah</span>' 
                                                : '<span class="mx-1 badge bg-danger">Belum</span>'}
                                        </td>
                                        <td class="text-center">${p.tanggal_upload ?? '-'}</td>
                                    </tr>
                                `;
                            });
                        })
                        .catch(err => console.error('Fetch error:', err));
                }
            });
</script>

<x-layout bodyClass="g-sidenav-show  bg-gray-200">
    <x-navbars.sidebar activePage="monitoring"></x-navbars.sidebar>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <x-navbars.navs.auth titlePage="Monitoring"></x-navbars.navs.auth>
        <!-- End Navbar -->
        {{-- Monitoring --}}
        <div class="container-fluid py-4">
            <!-- Filter Dokumen & Periode -->
            <div class="row mb-4">
                <div class="col-md-6">
                   <label for="dokumenSelect" class="form-label block text-gray-700 font-semibold mb-2">
                        Dokumen
                    </label>
                    <select id="dokumenSelect"
                        class="form-select appearance-none rounded-xl border-gray-300 bg-white text-gray-800 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition ease-in-out duration-150 w-full p-2">
                        <option value="0" >-- Pilih Dokumen --</option>
                        @foreach($dokumenList as $dokumen)
                            <option value="{{ $dokumen->id }}">{{ $dokumen->nama_dokumen }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                   <label for="periodeSelect" class="form-label block text-gray-700 font-semibold mb-2">
                        Periode
                    </label>
                    <select id="periodeSelect"
                        class="form-select appearance-none rounded-xl border-gray-300 bg-white text-gray-800 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition ease-in-out duration-150 w-full p-2">
                        <option value="0">-- Pilih Periode --</option>
                        @foreach($periodeList as $periode)
                            <option value="{{ $periode->id }}">{{ $periode->periode_key }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Tabel Ringkasan Dokumen & Periode -->
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Ringkasan Dokumen & Periode</h6>
                </div>
                <div class="card-body px-0 pb-2">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0" id="summaryTable">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama Dokumen</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Periode</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Sudah Upload</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Total Pegawai</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Progres</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Akan diisi dari JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


            <!-- Ringkasan Progres -->
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Ringkasan Progres</h6>
                </div>
                <div class="card-body">
                    <h5 id="progressText">{{ $progressSummary['percent'] ?? 0 }}% Sudah Mengumpulkan</h5>
                    @php $percent = $progressSummary['percent'] ?? 0; @endphp
                    <div class="progress mb-3" style="height: 20px;">
                        <!-- data-percent berisi angka, bukan CSS -->
                        <div id="progressBar"
                            class="progress-bar bg-gradient-info"
                            role="progressbar"
                            aria-valuenow="{{ $percent }}"
                            aria-valuemin="0"
                            aria-valuemax="100"
                            data-percent="{{ $percent }}">
                            <span class="visually-hidden">{{ $percent }}%</span>
                        </div>
                    </div>
                    <p id="progressCount">Total: {{ $progressSummary['done'] ?? 0 }} / {{ $progressSummary['total'] ?? 0 }} pegawai</p>
                    <button class="btn btn-primary mt-2 d-none" id="showDetails">Lihat Detail Pegawai</button>
                </div>
            </div>

            <!-- Tabel Pegawai (Tersembunyi awalnya) -->
            <div class="card d-none" id="pegawaiCard">
                <div class="card-header pb-0">
                    <h6>Detail Pegawai</h6>
                </div>
                <div class="card-body px-0 pb-2">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama Pegawai</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Unit Kerja</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tanggal Upload</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($pegawaiData as $pegawai)
                                <tr>
                                    <td>
                                        <h6 class="mx-3  mb-0 text-sm">{{ $pegawai->nama }}</h6>
                                    </td>
                                    <td>
                                        <h6 class=" mb-0 text-sm">    
                                            {{ $pegawai->unit_kerja ?? '-' }}
                                        </h6>
                                    </td>
                                    <td>
                                        @if($pegawai->is_uploaded == '1')
                                            <span class="mx-1 badge bg-success">Sudah</span>
                                        @else
                                            <span class="mx-1 badge bg-danger">Belum</span>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $pegawai->tanggal_upload ?? '-' }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    <!-- Script untuk show/hide tabel pegawai -->
    <script>
        document.getElementById('showDetails').addEventListener('click', function() {
            document.getElementById('pegawaiCard').classList.toggle('d-none');
        });

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-percent]').forEach(function(el){
                const p = el.getAttribute('data-percent') || '0';
                el.style.width = p + '%';
                el.setAttribute('aria-valuenow', p);
            });
        });
    </script>
    @push('js')
    <script src="{{ asset('assets') }}/js/plugins/chartjs.min.js"></script>
    <script>
        var ctx = document.getElementById("chart-bars").getContext("2d");

        new Chart(ctx, {
            type: "bar",
            data: {
                labels: ["M", "T", "W", "T", "F", "S", "S"],
                datasets: [{
                    label: "Sales",
                    tension: 0.4,
                    borderWidth: 0,
                    borderRadius: 4,
                    borderSkipped: false,
                    backgroundColor: "rgba(255, 255, 255, .8)",
                    data: [50, 20, 10, 22, 50, 10, 40],
                    maxBarThickness: 6
                }, ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                scales: {
                    y: {
                        grid: {
                            drawBorder: false,
                            display: true,
                            drawOnChartArea: true,
                            drawTicks: false,
                            borderDash: [5, 5],
                            color: 'rgba(255, 255, 255, .2)'
                        },
                        ticks: {
                            suggestedMin: 0,
                            suggestedMax: 500,
                            beginAtZero: true,
                            padding: 10,
                            font: {
                                size: 14,
                                weight: 300,
                                family: "Roboto",
                                style: 'normal',
                                lineHeight: 2
                            },
                            color: "#fff"
                        },
                    },
                    x: {
                        grid: {
                            drawBorder: false,
                            display: true,
                            drawOnChartArea: true,
                            drawTicks: false,
                            borderDash: [5, 5],
                            color: 'rgba(255, 255, 255, .2)'
                        },
                        ticks: {
                            display: true,
                            color: '#f8f9fa',
                            padding: 10,
                            font: {
                                size: 14,
                                weight: 300,
                                family: "Roboto",
                                style: 'normal',
                                lineHeight: 2
                            },
                        }
                    },
                },
            },
        });


        var ctx2 = document.getElementById("chart-line").getContext("2d");

        new Chart(ctx2, {
            type: "line",
            data: {
                labels: ["Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                datasets: [{
                    label: "Mobile apps",
                    tension: 0,
                    borderWidth: 0,
                    pointRadius: 5,
                    pointBackgroundColor: "rgba(255, 255, 255, .8)",
                    pointBorderColor: "transparent",
                    borderColor: "rgba(255, 255, 255, .8)",
                    borderColor: "rgba(255, 255, 255, .8)",
                    borderWidth: 4,
                    backgroundColor: "transparent",
                    fill: true,
                    data: [50, 40, 300, 320, 500, 350, 200, 230, 500],
                    maxBarThickness: 6

                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                scales: {
                    y: {
                        grid: {
                            drawBorder: false,
                            display: true,
                            drawOnChartArea: true,
                            drawTicks: false,
                            borderDash: [5, 5],
                            color: 'rgba(255, 255, 255, .2)'
                        },
                        ticks: {
                            display: true,
                            color: '#f8f9fa',
                            padding: 10,
                            font: {
                                size: 14,
                                weight: 300,
                                family: "Roboto",
                                style: 'normal',
                                lineHeight: 2
                            },
                        }
                    },
                    x: {
                        grid: {
                            drawBorder: false,
                            display: false,
                            drawOnChartArea: false,
                            drawTicks: false,
                            borderDash: [5, 5]
                        },
                        ticks: {
                            display: true,
                            color: '#f8f9fa',
                            padding: 10,
                            font: {
                                size: 14,
                                weight: 300,
                                family: "Roboto",
                                style: 'normal',
                                lineHeight: 2
                            },
                        }
                    },
                },
            },
        });

        var ctx3 = document.getElementById("chart-line-tasks").getContext("2d");

        new Chart(ctx3, {
            type: "line",
            data: {
                labels: ["Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                datasets: [{
                    label: "Mobile apps",
                    tension: 0,
                    borderWidth: 0,
                    pointRadius: 5,
                    pointBackgroundColor: "rgba(255, 255, 255, .8)",
                    pointBorderColor: "transparent",
                    borderColor: "rgba(255, 255, 255, .8)",
                    borderWidth: 4,
                    backgroundColor: "transparent",
                    fill: true,
                    data: [50, 40, 300, 220, 500, 250, 400, 230, 500],
                    maxBarThickness: 6

                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                scales: {
                    y: {
                        grid: {
                            drawBorder: false,
                            display: true,
                            drawOnChartArea: true,
                            drawTicks: false,
                            borderDash: [5, 5],
                            color: 'rgba(255, 255, 255, .2)'
                        },
                        ticks: {
                            display: true,
                            padding: 10,
                            color: '#f8f9fa',
                            font: {
                                size: 14,
                                weight: 300,
                                family: "Roboto",
                                style: 'normal',
                                lineHeight: 2
                            },
                        }
                    },
                    x: {
                        grid: {
                            drawBorder: false,
                            display: false,
                            drawOnChartArea: false,
                            drawTicks: false,
                            borderDash: [5, 5]
                        },
                        ticks: {
                            display: true,
                            color: '#f8f9fa',
                            padding: 10,
                            font: {
                                size: 14,
                                weight: 300,
                                family: "Roboto",
                                style: 'normal',
                                lineHeight: 2
                            },
                        }
                    },
                },
            },
        });

    </script>
    @endpush
</x-layout> --}}