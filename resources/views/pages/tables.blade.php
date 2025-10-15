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
                            <div class="filter-container">
                                <form method="GET" action="{{ route('dokumen.index') }}">
                                    <div class="filter-group">
                                        <div style="flex: 3; display: flex; gap: 1rem; flex-wrap: wrap;">
                                            @if (in_array(Auth::user()->role, ['Admin', 'Supervisor']))
                                            <div class="filter-item">
                                                <label for="user_id">Nama Pegawai</label>
                                                <select name="user_id" class="form-select">
                                                    <option value="">-- Semua Pegawai --</option>
                                                    @foreach ($pegawai as $u)
                                                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                                            {{ $u->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @endif

                                            <div class="filter-item">
                                                <label for="jenis_dokumen_id">Jenis Dokumen</label>
                                                <select name="jenis_dokumen_id" id="jenis_dokumen_id" class="form-select">
                                                    <option value="">-- Semua Jenis Dokumen --</option>
                                                    @foreach($jenisDokumen as $jenis)
                                                        <option value="{{ $jenis->id }}" {{ request('jenis_dokumen_id') == $jenis->id ? 'selected' : '' }}>
                                                            {{ $jenis->nama_dokumen }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="filter-item" style="min-width: 140px;">
                                                <label for="tipe">Periode</label>
                                                <select name="tipe" id="tipe" class="form-select">
                                                    <option value="">-- Semua Periode --</option>
                                                    @foreach($periode->unique('tipe') as $p)
                                                        <option value="{{ $p->tipe }}" {{ request('tipe') == $p->tipe ? 'selected' : '' }}>
                                                            {{ $p->tipe }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="filter-item" style="min-width: 140px;">
                                                <label for="tahun">Tahun</label>
                                                <select name="tahun" id="tahun" class="form-select">
                                                    <option value="">-- Semua Tahun --</option>
                                                    @foreach($periode->unique('tahun') as $p)
                                                        <option value="{{ $p->tahun }}" {{ request('tahun') == $p->tahun ? 'selected' : '' }}>
                                                            {{ $p->tahun }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="action-buttons">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-funnel"></i>Filter
                                            </button>
                                            <a href="{{ route('dokumen.index') }}" class="btn btn-secondary">
                                                <i class="bi bi-arrow-clockwise"></i>Reset
                                            </a>
                                            {{-- <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#tambahDokumenModal">
                                                <i class="bi bi-plus-circle"></i>Tambah
                                            </button> --}}
                                        </div>
                                    </div>
                                </form>
                            </div>

                            {{-- Bulk Actions --}}
                            <div class="bulk-actions">
                                <button type="button" id="btnDownloadSelected" class="btn btn-primary" disabled>
                                    <i class="bi bi-download"></i>
                                    Download
                                    <span class="btn-count" id="countSelected">0</span>
                                </button>
                                
                                
                                <button type="button" id="btnDeleteSelected" class="btn btn-danger" disabled>
                                    <i class="bi bi-trash"></i>
                                    Hapus
                                    <span class="btn-count" id="countSelectedDelete">0</span>
                                </button>
                                
                            </div>

                            {{-- Table --}}
                            <div class="table-container">
                                <div class="table-responsive">
                                    <table class="table align-middle">
                                        <thead>
                                            <tr>
                                                <th style="width: 50px;">
                                                    <div class="form-check d-flex justify-content-center mb-0">
                                                        <input class="form-check-input" type="checkbox" id="checkAll">
                                                    </div>
                                                </th>
                                                <th>Nama Pegawai</th>
                                                <th>Jenis Dokumen</th>
                                                <th>Periode</th>
                                                <th>Tahun</th>
                                                <th>Tanggal Upload</th>
                                                <th style="width: 120px;">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($dokumen as $index => $d)
                                                <tr>
                                                    <td class="text-center">
                                                        <div class="form-check d-flex justify-content-center mb-0">
                                                            <input class="form-check-input dokumen-checkbox" type="checkbox" 
                                                                value="{{ $d->id }}" 
                                                                data-path="{{ $d->path }}">
                                                        </div>
                                                    </td>
                                                    <td>{{ $d->pegawai->name ?? '-' }}</td>
                                                    <td>{{ $d->jenisDokumen->nama_dokumen ?? '-' }}</td>
                                                    <td class="text-center">{{ $d->periode->tipe ?? '-' }}</td>
                                                    <td class="text-center">{{ $d->periode->tahun ?? '-' }}</td>
                                                    <td class="text-center">{{ $d->tanggal_unggah }}</td>
                                                    <td class="text-center">
                                                        <a href="{{ route('dokumen.preview', $d->id) }}" target="_blank" class="btn btn-sm btn-info">
                                                            <i class="bi bi-eye"></i> Preview
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center" style="padding: 2rem;">
                                                        <i class="bi bi-inbox" style="font-size: 3rem; color: #dee2e6;"></i>
                                                        <p class="text-muted mt-2 mb-0">Tidak ada data ditemukan</p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
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

                /* Custom Styling untuk Form Filter */
                .filter-container {
                    background: #f8f9fa;
                    padding: 1.5rem;
                    border-radius: 12px;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
                    margin-bottom: 1.5rem;
                }

                .filter-group {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 1rem;
                    align-items: flex-end;
                }

                .filter-item {
                    flex: 1;
                    min-width: 180px;
                }

                .filter-item label {
                    display: block;
                    font-weight: 600;
                    font-size: 0.875rem;
                    color: #495057;
                    margin-bottom: 0.5rem;
                }

                .filter-item select {
                    width: 100%;
                    height: 38px;
                    padding: 0.375rem 0.75rem;
                    font-size: 0.875rem;
                    border: 1px solid #ced4da;
                    border-radius: 8px;
                    transition: all 0.3s ease;
                }

                .filter-item select:focus {
                    border-color: #0d6efd;
                    box-shadow: 0 0 0 0.2rem rgba(13,110,253,.15);
                }

                .action-buttons {
                    display: flex;
                    gap: 0.75rem;
                    align-items: flex-end;
                    flex-wrap: wrap;
                }

                .action-buttons .btn {
                    height: 38px;
                    padding: 0 1.25rem;
                    font-size: 0.875rem;
                    font-weight: 500;
                    border-radius: 8px;
                    display: inline-flex;
                    align-items: center;
                    gap: 0.5rem;
                    transition: all 0.3s ease;
                    white-space: nowrap;
                }

                /* Bulk Action Buttons */
                .bulk-actions {
                    display: flex;
                    gap: 0.75rem;
                    margin-bottom: 1.5rem;
                    padding: 1rem;
                    background: #ffffff;
                    border-radius: 12px;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
                }

                .bulk-actions .btn {
                    height: 42px;
                    padding: 0 1.5rem;
                    font-size: 0.9rem;
                    font-weight: 600;
                    border-radius: 10px;
                    display: inline-flex;
                    align-items: center;
                    gap: 0.5rem;
                    transition: all 0.3s ease;
                    border: none;
                }

                .bulk-actions .btn:disabled {
                    opacity: 0.5;
                    cursor: not-allowed;
                }

                .bulk-actions .btn-primary {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                }

                .bulk-actions .btn-primary:hover:not(:disabled) {
                    transform: translateY(-2px);
                    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
                }

                .bulk-actions .btn-danger {
                    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
                }

                .bulk-actions .btn-danger:hover:not(:disabled) {
                    transform: translateY(-2px);
                    box-shadow: 0 4px 12px rgba(245, 87, 108, 0.4);
                }

                .btn-count {
                    background: rgba(255,255,255,0.25);
                    padding: 2px 8px;
                    border-radius: 12px;
                    font-size: 0.85rem;
                    font-weight: 700;
                }

                /* Table Styling */
                .table-container {
                    background: #ffffff;
                    border-radius: 12px;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
                    overflow: hidden;
                }

                .table {
                    margin-bottom: 0;
                }

                .table thead th {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: #ffffff;
                    font-weight: 600;
                    font-size: 0.875rem;
                    padding: 1rem 0.75rem;
                    border: none;
                    text-align: center;
                    vertical-align: middle;
                }

                .table tbody td {
                    padding: 0.875rem 0.75rem;
                    vertical-align: middle;
                    font-size: 0.875rem;
                    border-bottom: 1px solid #f1f3f5;
                }

                .table tbody tr:last-child td {
                    border-bottom: none;
                }

                .table tbody tr:hover {
                    background-color: #f8f9fa;
                    transition: background-color 0.2s ease;
                }

                /* Checkbox Styling */
                .form-check-input {
                    width: 1.25rem;
                    height: 1.25rem;
                    border: 2px solid #ced4da;
                    border-radius: 6px;
                    cursor: pointer;
                    transition: all 0.2s ease;
                }

                .form-check-input:checked {
                    background-color: #667eea;
                    border-color: #667eea;
                }

                .form-check-input:focus {
                    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
                }

                /* Button Styling */
                .btn-sm {
                    padding: 0.375rem 0.875rem;
                    font-size: 0.8rem;
                    border-radius: 6px;
                    font-weight: 500;
                }

                .btn-info {
                    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
                    border: none;
                    color: #ffffff;
                }

                .btn-info:hover {
                    transform: translateY(-1px);
                    box-shadow: 0 4px 8px rgba(79, 172, 254, 0.3);
                }

                /* Responsive */
                @media (max-width: 768px) {
                    .filter-group {
                        flex-direction: column;
                    }

                    .filter-item {
                        width: 100%;
                    }

                    .action-buttons {
                        width: 100%;
                        justify-content: stretch;
                    }

                    .action-buttons .btn {
                        flex: 1;
                    }

                    .bulk-actions {
                        flex-direction: column;
                    }

                    .bulk-actions .btn {
                        width: 100%;
                    }
                }

                /* Loading Spinner */
                .spinner-border-sm {
                    width: 1rem;
                    height: 1rem;
                    border-width: 0.15em;
                }
                .swal2-popup {
                    border-radius: 20px !important;
                    padding: 2rem !important;
                    box-shadow: 0 20px 60px rgba(0,0,0,0.3) !important;
                }

                .swal2-title {
                    font-size: 1.75rem !important;
                    font-weight: 700 !important;
                    color: #2d3748 !important;
                }

                .swal2-html-container {
                    font-size: 1rem !important;
                    color: #4a5568 !important;
                }

                .swal2-icon {
                    border-width: 3px !important;
                }

                .swal2-success {
                    border-color: #48bb78 !important;
                }

                .swal2-success [class^='swal2-success-line'] {
                    background-color: #48bb78 !important;
                }

                .swal2-success-ring {
                    border-color: rgba(72, 187, 120, 0.3) !important;
                }

                .swal2-error {
                    border-color: #f56565 !important;
                }

                .swal2-warning {
                    border-color: #ed8936 !important;
                    color: #ed8936 !important;
                }

                .swal2-confirm {
                    border-radius: 12px !important;
                    padding: 0.75rem 2rem !important;
                    font-weight: 600 !important;
                    font-size: 1rem !important;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
                    transition: all 0.3s ease !important;
                }

                .swal2-confirm:hover {
                    transform: translateY(-2px) !important;
                    box-shadow: 0 6px 20px rgba(0,0,0,0.2) !important;
                }

                .swal2-cancel {
                    border-radius: 12px !important;
                    padding: 0.75rem 2rem !important;
                    font-weight: 600 !important;
                    font-size: 1rem !important;
                }

                .swal2-actions {
                    gap: 1rem !important;
                }

                /* Loading Animation */
                .swal2-loader {
                    border-color: #667eea transparent #667eea transparent !important;
                }
            </style>

            {{-- Sweet Alert 2 Library --}}
            <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-material-ui/material-ui.css" rel="stylesheet">
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const checkAll = document.getElementById('checkAll');
                    const checkboxes = document.querySelectorAll('.dokumen-checkbox');
                    const btnDownload = document.getElementById('btnDownloadSelected');
                    const btnDelete = document.getElementById('btnDeleteSelected');
                    const countSelected = document.getElementById('countSelected');
                    const countSelectedDelete = document.getElementById('countSelectedDelete');

                    // Toast Configuration
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                        }
                    });

                    // Function untuk update counter dan status tombol
                    function updateButtons() {
                        const checkedBoxes = document.querySelectorAll('.dokumen-checkbox:checked');
                        const count = checkedBoxes.length;
                        
                        countSelected.textContent = count;
                        if (countSelectedDelete) {
                            countSelectedDelete.textContent = count;
                        }
                        btnDownload.disabled = count === 0;
                        if (btnDelete) {
                            btnDelete.disabled = count === 0;
                        }
                    }

                    // Check/Uncheck All
                    checkAll.addEventListener('change', function() {
                        checkboxes.forEach(checkbox => {
                            checkbox.checked = this.checked;
                        });
                        updateButtons();
                    });

                    // Individual checkbox change
                    checkboxes.forEach(checkbox => {
                        checkbox.addEventListener('change', function() {
                            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                            const someChecked = Array.from(checkboxes).some(cb => cb.checked);
                            
                            checkAll.checked = allChecked;
                            checkAll.indeterminate = someChecked && !allChecked;
                            
                            updateButtons();
                        });
                    });

                    // Download Selected
                    btnDownload.addEventListener('click', function() {
                        const checkedBoxes = document.querySelectorAll('.dokumen-checkbox:checked');
                        
                        if (checkedBoxes.length === 0) {
                            Toast.fire({
                                icon: 'warning',
                                title: 'Pilih minimal satu dokumen!'
                            });
                            return;
                        }

                        const originalText = btnDownload.innerHTML;
                        btnDownload.disabled = true;
                        btnDownload.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Downloading...';

                        const dokumenIds = Array.from(checkedBoxes).map(cb => cb.value);
                        
                        fetch('{{ route("dokumen.download-multiple") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                dokumen_ids: dokumenIds
                            })
                        })
                        .then(response => {
                            if (!response.ok) throw new Error('Download gagal');
                            return response.blob();
                        })
                        .then(blob => {
                            const url = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.style.display = 'none';
                            a.href = url;
                            a.download = checkedBoxes.length === 1 ? 'dokumen.pdf' : 'dokumen_' + new Date().getTime() + '.zip';
                            
                            document.body.appendChild(a);
                            a.click();
                            window.URL.revokeObjectURL(url);
                            document.body.removeChild(a);
                            
                            btnDownload.disabled = false;
                            btnDownload.innerHTML = originalText;
                            
                            Toast.fire({
                                icon: 'success',
                                title: 'Download berhasil!'
                            });
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Toast.fire({
                                icon: 'error',
                                title: 'Download gagal!'
                            });
                            
                            btnDownload.disabled = false;
                            btnDownload.innerHTML = originalText;
                        });
                    });

                    // Delete Selected
                    if (btnDelete) {
                        btnDelete.addEventListener('click', function() {
                            const checkedBoxes = document.querySelectorAll('.dokumen-checkbox:checked');
                            
                            if (checkedBoxes.length === 0) {
                                Toast.fire({
                                    icon: 'warning',
                                    title: 'Pilih minimal satu dokumen!'
                                });
                                return;
                            }

                            const dokumenIds = Array.from(checkedBoxes).map(cb => cb.value);
                            const count = dokumenIds.length;

                            Swal.fire({
                                title: 'Hapus Dokumen?',
                                html: `<p style="margin: 0; font-size: 1rem;">Anda akan menghapus <strong style="color: #f56565;">${count} dokumen</strong></p><p style="margin: 0.5rem 0 0 0; font-size: 0.9rem; color: #718096;">Data yang dihapus tidak dapat dikembalikan!</p>`,
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#f56565',
                                cancelButtonColor: '#a0aec0',
                                confirmButtonText: '<i class="bi bi-trash me-2"></i>Ya, Hapus!',
                                cancelButtonText: '<i class="bi bi-x-circle me-2"></i>Batal',
                                reverseButtons: true,
                                customClass: {
                                    confirmButton: 'btn-delete-confirm',
                                    cancelButton: 'btn-delete-cancel'
                                }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    Swal.fire({
                                        title: 'Menghapus...',
                                        html: 'Mohon tunggu sebentar',
                                        allowOutsideClick: false,
                                        didOpen: () => {
                                            Swal.showLoading();
                                        }
                                    });

                                    fetch('{{ route("dokumen.delete-multiple") }}', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                        },
                                        body: JSON.stringify({
                                            dokumen_ids: dokumenIds
                                        })
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            Swal.fire({
                                                icon: 'success',
                                                title: 'Berhasil!',
                                                text: data.message,
                                                confirmButtonColor: '#48bb78',
                                                timer: 2000,
                                                timerProgressBar: true
                                            }).then(() => {
                                                window.location.reload();
                                            });
                                        } else {
                                            throw new Error(data.message || 'Gagal menghapus dokumen');
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error:', error);
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Gagal!',
                                            text: error.message || 'Terjadi kesalahan saat menghapus dokumen',
                                            confirmButtonColor: '#f56565'
                                        });
                                    });
                                }
                            });
                        });
                    }

                    // Initial update
                    updateButtons();
                });
            </script>

        </div>
    </main>
    <x-plugins></x-plugins>
</x-layout>
