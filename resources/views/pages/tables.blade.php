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
                                    </div>
                                </form>
                            </div>

                            {{-- Tombol Download & Hapus Multiple --}}
                            <div class="mb-3 d-flex gap-2">
                                <button type="button" id="btnDownloadSelected" class="btn btn-primary" disabled>
                                    <i class="bi bi-download me-1"></i>Download Terpilih (<span id="countSelected">0</span>)
                                </button>
                                <button type="button" id="btnDeleteSelected" class="btn btn-danger" disabled>
                                    <i class="bi bi-trash me-1"></i>Hapus Terpilih (<span id="countSelectedDelete">0</span>)
                                </button>
                            </div>

                            {{-- Tabel --}}
                            <div class="table-responsive mt-4">
                                <table class="table table-bordered table-striped align-middle mb-0">
                                    <thead class="table-dark text-center">
                                        <tr>
                                            <th style="width: 50px;">
                                                <div class="form-check d-flex justify-content-center mb-0">
                                                    <input class="form-check-input" type="checkbox" id="checkAll" style="cursor: pointer;">
                                                </div>
                                            </th>
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
                                                <td class="text-center">
                                                    <div class="form-check d-flex justify-content-center mb-0">
                                                        <input class="form-check-input dokumen-checkbox" type="checkbox" 
                                                            value="{{ $d->id }}" 
                                                            data-path="{{ $d->path }}"
                                                            style="cursor: pointer;">
                                                    </div>
                                                </td>
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
                                                <td colspan="7" class="text-center">Tidak ada data ditemukan</td>
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
                document.addEventListener('DOMContentLoaded', function() {
                    const checkAll = document.getElementById('checkAll');
                    const checkboxes = document.querySelectorAll('.dokumen-checkbox');
                    const btnDownload = document.getElementById('btnDownloadSelected');
                    const countSelected = document.getElementById('countSelected');
                    const countSelectedDelete = document.getElementById('countSelectedDelete');
                    const btnDelete = document.getElementById('btnDeleteSelected');

                    // Function untuk update counter dan status tombol
                    function updateDownloadButton() {
                        const checkedBoxes = document.querySelectorAll('.dokumen-checkbox:checked');
                        const count = checkedBoxes.length;
                        
                        countSelected.textContent = count;
                        countSelectedDelete.textContent = count;
                        btnDownload.disabled = count === 0;
                        btnDelete.disabled = count === 0;
                    }

                    // Check/Uncheck All
                    checkAll.addEventListener('change', function() {
                        checkboxes.forEach(checkbox => {
                            checkbox.checked = this.checked;
                        });
                        updateDownloadButton();
                    });

                    // Individual checkbox change
                    checkboxes.forEach(checkbox => {
                        checkbox.addEventListener('change', function() {
                            // Update check all status
                            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                            const someChecked = Array.from(checkboxes).some(cb => cb.checked);
                            
                            checkAll.checked = allChecked;
                            checkAll.indeterminate = someChecked && !allChecked;
                            
                            updateDownloadButton();
                        });
                    });

                    // Download Selected
                    btnDownload.addEventListener('click', function() {
                        const checkedBoxes = document.querySelectorAll('.dokumen-checkbox:checked');
                        
                        if (checkedBoxes.length === 0) {
                            alert('Pilih minimal satu dokumen untuk didownload');
                            return;
                        }

                        // Tampilkan loading
                        const originalText = btnDownload.innerHTML;
                        btnDownload.disabled = true;
                        btnDownload.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Downloading...';

                        const dokumenIds = Array.from(checkedBoxes).map(cb => cb.value);
                        
                        // Gunakan fetch API untuk download
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
                            if (!response.ok) {
                                throw new Error('Download gagal');
                            }
                            return response.blob();
                        })
                        .then(blob => {
                            // Buat URL untuk blob
                            const url = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.style.display = 'none';
                            a.href = url;
                            
                            // Set nama file
                            const fileName = checkedBoxes.length === 1 
                                ? 'dokumen.pdf' 
                                : 'dokumen_' + new Date().getTime() + '.zip';
                            a.download = fileName;
                            
                            document.body.appendChild(a);
                            a.click();
                            
                            // Cleanup
                            window.URL.revokeObjectURL(url);
                            document.body.removeChild(a);
                            
                            // Reset button
                            btnDownload.disabled = false;
                            btnDownload.innerHTML = originalText;
                            
                            // Tampilkan success message
                            alert('Download berhasil!');
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Gagal mendownload file. Silakan coba lagi.');
                            
                            // Reset button
                            btnDownload.disabled = false;
                            btnDownload.innerHTML = originalText;
                        });
                    });

                    // Delete Selected
                    btnDelete.addEventListener('click', function() {
    const checkedBoxes = document.querySelectorAll('.dokumen-checkbox:checked');
    if (checkedBoxes.length === 0) {
        alert('Pilih minimal satu dokumen untuk dihapus');
        return;
    }

    // Tampilkan loading
    const originalText = btnDelete.innerHTML;
    btnDelete.disabled = true;
    btnDelete.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Deleting...';

    const dokumenIds = Array.from(checkedBoxes).map(cb => cb.value);
    
    // Kirim request ke server (tanpa download file)
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
    .then(response => {
        if (!response.ok) {
            throw new Error('Delete gagal');
        }
        return response.json(); // ubah dari blob ke JSON
    })
    .then(data => {
        // Misalnya server kirim { success: true, message: 'Dokumen berhasil dihapus' }
        alert(data.message || 'Delete berhasil!');

        // Hapus baris dokumen dari tabel (opsional)
        checkedBoxes.forEach(cb => {
            const row = cb.closest('tr');
            if (row) row.remove();
        });

        // Reset tombol
        btnDelete.disabled = false;
        btnDelete.innerHTML = originalText;
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Gagal menghapus file. Silakan coba lagi.');

        // Reset tombol
        btnDelete.disabled = false;
        btnDelete.innerHTML = originalText;
    });
});


                    // Initial update
                    updateDownloadButton();
                });
            </script>

        </div>
    </main>
    <x-plugins></x-plugins>
</x-layout>
