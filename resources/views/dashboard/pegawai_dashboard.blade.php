<x-layout bodyClass="g-sidenav-show bg-gray-200">
   
    <style>
        .btn-action {
            border: 1px solid #e9ecef; /* Adds a light border */
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, .075) !important; /* Adds a subtle shadow */
            transition: background-color 0.2s ease-in-out;
            border-radius: 0.5rem; /* Makes corners rounded */
            line-height: 1; /* Helps vertically align the icon */
        }
        /* Hover effect for the info (blue) button */
        .btn-action.text-info:hover {
            background-color: rgba(23, 162, 184, 0.1); /* Faded info color background */
        }
        /* Hover effect for the dark (gray) button */
        .btn-action.text-dark:hover {
            background-color: rgba(52, 58, 64, 0.1); /* Faded dark color background */
        }

        /* Styling for the filter box */
        .filter-fieldset {
            border: 1px solid #dee2e6;
            border-radius: .5rem;
            padding: 1rem 1.5rem 1.5rem 1.5rem; /* top, right, bottom, left */
            margin: 1rem 0;
            position: relative;
        }
        .filter-fieldset legend {
            padding: 0 .5rem;
            margin-left: 1rem;
            width: auto;
            float: none; /* Required to reset bootstrap's default */
            font-size: 1rem;
            font-weight: 600;
            background: #fff; /* Match card background */
            position: absolute;
            top: -0.8rem;
        }

        .form-control{
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
        }

    </style>
      <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
   
    <x-navbars.sidebar activePage="pegawai_dashboard"></x-navbars.sidebar>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-navbars.navs.auth titlePage="Monitoring Dokumen"></x-navbars.navs.auth>

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
                            style="width: {{ $percent }} %" aria-valuenow="{{ $percent }}"
                            aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                    <p>Total: {{ $sudah }} / {{ $total }} file | Belum: {{ $belum }}</p>
                </div>
            </div>
            
            <!-- Daftar Semua Dokumen -->
            <div class="card">
                <div class="card-header pb-0">
                    <h6 class="mb-5">Daftar Semua Dokumen</h6>
                    <!-- Form Filter -->
                    <form action="{{ route('pegawai-dashboard') }}" method="GET">
                        <fieldset class="filter-fieldset p-4">
                            <legend>Filter Dokumen</legend>
                            <div class="row align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label">Periode</label>
                                    <select name="periode_id" class="form-control form-control-sm">
                                        <option value="">-- Semua Periode --</option>
                                        @foreach($periodeOptions as $periode)
                                            <option value="{{ $periode->id }}" {{ request('periode_id') == $periode->id ? 'selected' : '' }}>
                                                {{ $periode->periode_key }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Jenis Dokumen</label>
                                    <select name="jenis_dokumen_id" class="form-control form-control-sm">
                                        <option value="">-- Semua Jenis --</option>
                                        @foreach($jenisDokumenOptions as $jenis)
                                            <option value="{{ $jenis->id }}" {{ request('jenis_dokumen_id') == $jenis->id ? 'selected' : '' }}>
                                                {{ $jenis->nama_dokumen }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-control form-control-sm">
                                        <option value="">-- Semua Status --</option>
                                        <option value="belum" {{ request('status') == 'belum' ? 'selected' : '' }}>Belum Upload</option>
                                        <option value="menunggu" {{ request('status') == 'menunggu' ? 'selected' : '' }}>Menunggu Persetujuan</option>
                                        <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-primary btn-sm w-100 mb-0" type="submit">
                                        <i class="fas fa-filter me-1"></i> Terapkan Filter
                                    </button>
                                </div>
                            </div>
                        </fieldset>
                        <!-- Hidden search input to retain search query when filtering -->
                        <input type="hidden" name="search" value="{{ request('search') }}">
                         <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                    </form>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <!-- Table Controls: Entries per page & Search -->
                    <div class="d-flex justify-content-between align-items-center px-4 py-3">
                        <div>
                            <form action="{{ route('pegawai-dashboard') }}" method="GET">
                                <label for="per_page" class="text-sm">Tampilkan</label>
                                <select name="per_page" id="per_page" class="form-control form-control-sm d-inline-block w-auto" onchange="this.form.submit()">
                                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                </select>
                                <label for="per_page" class="text-sm">data</label>
                                <!-- Hidden inputs to retain other filters -->
                                <input type="hidden" name="periode_id" value="{{ request('periode_id') }}">
                                <input type="hidden" name="jenis_dokumen_id" value="{{ request('jenis_dokumen_id') }}">
                                <input type="hidden" name="status" value="{{ request('status') }}">
                                <input type="hidden" name="search" value="{{ request('search') }}">
                            </form>
                        </div>
                        <div>
                             <form action="{{ route('pegawai-dashboard') }}" method="GET">
                                <div class="input-group input-group-sm">
                                    <input type="text" name="search" class="form-control" placeholder="Cari dokumen..." value="{{ request('search') }}">
                                    <button class="btn btn-primary mb-0" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                                <!-- Hidden inputs to retain other filters -->
                                <input type="hidden" name="periode_id" value="{{ request('periode_id') }}">
                                <input type="hidden" name="jenis_dokumen_id" value="{{ request('jenis_dokumen_id') }}">
                                <input type="hidden" name="status" value="{{ request('status') }}">
                                <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                            </form>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama Dokumen</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Periode</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tanggal Upload</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($uploads as $item)
                                    <tr>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <h6 class="mb-0 text-sm">{{ $item->nama_dokumen }}</h6>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="text-sm font-weight-bold mb-0">{{ $item->periode_key }}</p>
                                        </td>
                                        <td class="align-middle text-center text-sm">
                                            @if($item->is_uploaded == 1 && $item->penilaian == 1)
                                                <span class="badge badge-sm bg-gradient-success">Selesai</span>
                                            @elseif($item->is_uploaded == 1 && $item->penilaian == 0)
                                                <span class="badge badge-sm bg-gradient-warning">Menunggu Persetujuan</span>
                                            @else
                                                <span class="badge badge-sm bg-gradient-danger">Belum Upload</span>
                                            @endif
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="text-secondary text-sm font-weight-bold">
                                                {{ $item->tanggal_upload ? \Carbon\Carbon::parse($item->tanggal_upload)->format('d/m/Y') : '-' }}
                                            </span>
                                        </td>
                                        <td class="align-middle text-center">
                                             @if($item->is_uploaded == 1 && $item->penilaian == 1)
                                                {{-- Status Selesai: Hanya bisa lihat --}}
                                                <!-- <a href="{{ route('dokumen.preview', $item->dokumen_id) }}" target="_blank" class="btn btn-action text-info p-2 mx-1 mb-0" data-bs-toggle="tooltip" data-bs-placement="top" title="Lihat Preview">
                                                    <i class="fas fa-eye"></i>
                                                </a> -->
                                                <a href="{{ route('dokumen.preview', $item->dokumen_id) }}" target="_blank" class="btn btn-sm btn-info  mb-0">
                                                    <i class="fas fa-eye me-1"></i> Preview
                                                </a>
                                            @elseif($item->is_uploaded == 1 && $item->penilaian == 0)
                                                {{-- Status Menunggu: Bisa lihat dan edit --}}
                                                <a href="{{ route('dokumen.preview', $item->dokumen_id) }}" target="_blank" class="btn btn-action text-info p-2 mx-1 mb-0" data-bs-toggle="tooltip" data-bs-placement="top" title="Lihat Preview">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button type="button" class="btn btn-action text-dark p-2 mx-1 mb-0 edit-dokumen-btn" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-placement="top" 
                                                    data-bs-toggle2="tooltip"
                                                    data-bs-target="#editDokumenModal"
                                                    data-dokumen-id="{{ $item->dokumen_id }}"
                                                    data-nama-dokumen="{{ $item->nama_dokumen }}"
                                                    data-periode-key="{{ $item->periode_key }}"
                                                    data-dokumen-path="{{ $item->path ? route('dokumen.preview', $item->dokumen_id) : '' }}"
                                                    title="Edit">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </button>
                                            @else
                                                <a href="{{ route('pdf.sign.form', ['jenis_dokumen_id' => $item->jenis_dokumen_id, 'periode_id' => $item->periode_id]) }}" class="btn btn-sm btn-primary mb-0">
                                                    <i class="fas fa-upload me-1"></i> Upload
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <p class="mb-0">Tidak ada data yang ditemukan.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                     <!-- Pagination -->
                    <div class="d-flex justify-content-start align-items-center mt-3 px-4">
                        <div>
                            {{ $uploads->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <x-plugins></x-plugins>
    
    
    
    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.13.216/pdf.min.js"></script>
  


    {{-- BARU: MODAL UNTUK EDIT DOKUMEN --}}
    <div class="modal fade" id="editDokumenModal" tabindex="-1" aria-labelledby="editDokumenModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editDokumenModalLabel">Edit Dokumen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editSignForm" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('POST') {{-- Akan diubah oleh JS jika perlu, tapi POST adalah default --}}

                        {{-- Info Dokumen (Read-only) --}}
                        <div class="alert alert-light" role="alert">
                            <h4 class="alert-heading" id="modal-nama-dokumen">Nama Dokumen</h4>
                            <p id="modal-periode-key">Periode</p>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Pilih file PDF Baru (opsional, jika ingin mengganti)</label>
                                <input type="file" id="pdfFileEdit" name="pdf" accept="application/pdf" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Pilih tanda tangan Baru (PNG/JPG) max. 5 mb</label>
                                <input type="file" id="sigFileEdit" accept="image/*" class="form-control" multiple>
                            </div>
                        </div>

                        <div id="viewerWrapEdit" class="border" style="height:60vh; overflow:auto; position:relative; background:#efefef;">
                            <div id="pdf-container-edit" style="position:relative; width:fit-content; margin:auto;">
                                {{-- Preview PDF akan dimuat di sini oleh JS --}}
                            </div>
                        </div>

                        <p class="mt-3 text-muted small">
                            Geser (drag) tanda tangan ke posisi yang diinginkan → atur ukuran dengan slider → klik "Simpan Perubahan".
                        </p>
                        <div id="signature-controls-edit" class="mt-3"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" id="updateAndSubmitBtn" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </div>
        </div>
    </div>


     <style>
        .page-wrap { position: relative; margin-bottom: 18px; display: inline-block; }
        .page-canvas { display: block; }
        .sign-img { position: absolute; cursor: grab; user-select: none; touch-action: none; z-index: 999; }

         /* Tambahkan padding agar placeholder tidak menempel ke border */
        select.form-select {
            padding-left: 12px; /* jarak kiri */
            padding-right: 12px; /* jarak kanan */
        }

        /* Opsional: buat warna placeholder sedikit lebih pucat */
        select.form-select option[value=""] {
            color: #6c757d; /* abu-abu */
        }

        .modal-header .btn-close {
            background: none;
            border: none;
            box-shadow: none;
            font-size: 1.2rem;
            opacity: 0.7;
            color: #ff0000ff;
        }
        .modal-header .btn-close:hover {
            opacity: 1;
        }
    </style>

    <script>
        // Inisialisasi PDF.js Worker
        if (pdfjsLib && !pdfjsLib.GlobalWorkerOptions.workerSrc) {
            pdfjsLib.GlobalWorkerOptions.workerSrc = `https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.13.216/pdf.worker.min.js`;
        }

        const editModalEl = document.getElementById('editDokumenModal');
        const editSignForm = document.getElementById('editSignForm');
        let currentDokumenId = null; // Untuk menyimpan ID dokumen yang sedang diedit

        // Variabel untuk proses PDF di modal
        let pdfDocEdit = null;
        let pageViewsEdit = [];
        let signaturesEdit = [];
        const pdfContainerEdit = document.getElementById('pdf-container-edit');
        const sigFileInputEdit = document.getElementById('sigFileEdit');
        const pdfFileInputEdit = document.getElementById('pdfFileEdit');
        const signatureControlsEdit = document.getElementById('signature-controls-edit');


        // Fungsi untuk mereset state modal
        function resetModalState() {
            pdfContainerEdit.innerHTML = '';
            signatureControlsEdit.innerHTML = '';
            editSignForm.reset();
            pdfDocEdit = null;
            pageViewsEdit = [];
            signaturesEdit = [];
            currentDokumenId = null;
        }

        

        // Fungsi untuk me-render PDF
        async function renderPdfPreview(pdfUrl) {
            if (!pdfUrl) {
                pdfContainerEdit.innerHTML = '<p class="text-center p-5">Dokumen belum diupload.</p>';
                return;
            }
            
            try {
                const loadingTask = pdfjsLib.getDocument(pdfUrl);
                pdfDocEdit = await loadingTask.promise;
                const containerWidth = pdfContainerEdit.clientWidth > 0 ? pdfContainerEdit.clientWidth : 800;
                
                for (let i = 1; i <= pdfDocEdit.numPages; i++) {
                    const page = await pdfDocEdit.getPage(i);
                    const viewport = page.getViewport({ scale: 1 });
                    const scale = (containerWidth - 40) / viewport.width;
                    const scaledViewport = page.getViewport({ scale });

                    const pageWrap = document.createElement('div');
                    pageWrap.className = 'page-wrap';
                    pageWrap.style.width = `${scaledViewport.width}px`;
                    pageWrap.style.height = `${scaledViewport.height}px`;
                    pageWrap.dataset.pageNumber = i;

                    const canvas = document.createElement('canvas');
                    canvas.width = scaledViewport.width;
                    canvas.height = scaledViewport.height;
                    pageWrap.appendChild(canvas);
                    pdfContainerEdit.appendChild(pageWrap);

                    await page.render({ canvasContext: canvas.getContext('2d'), viewport: scaledViewport }).promise;
                    pageViewsEdit.push({ pageNumber: i, elem: pageWrap });
                }
            } catch (error) {
                console.error('Error loading PDF:', error);
                pdfContainerEdit.innerHTML = `<p class="text-center p-5 text-danger">Gagal memuat preview PDF. File mungkin rusak atau tidak dapat diakses.</p>`;
            }
        }

        


        // Event Listener saat Modal akan ditampilkan
        editModalEl.addEventListener('show.bs.modal', async (event) => {
            resetModalState(); // Selalu reset saat modal baru dibuka

            const button = event.relatedTarget;
            currentDokumenId = button.dataset.dokumenId;
            const namaDokumen = button.dataset.namaDokumen;
            const periodeKey = button.dataset.periodeKey;
            const dokumenPath = button.dataset.dokumenPath;

            // Set judul dan info
            document.getElementById('modal-nama-dokumen').textContent = namaDokumen;
            document.getElementById('modal-periode-key').textContent = `Periode: ${periodeKey}`;
            
            // Set action form
            editSignForm.action = `/sign-pdf/update/${currentDokumenId}`;

            // Muat dan tampilkan PDF yang sudah ada
            await renderPdfPreview(dokumenPath);

            console.log('PDF URL:', dokumenPath);

        });

        // Event listener jika user memilih PDF baru untuk di-upload
        pdfFileInputEdit.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (!file) return;
            
            // Hapus preview lama dan signature
            pdfContainerEdit.innerHTML = '';
            signatureControlsEdit.innerHTML = '';
            pageViewsEdit = [];
            signaturesEdit = [];

            // Render preview PDF yang baru dipilih
            const fileUrl = URL.createObjectURL(file);
            await renderPdfPreview(fileUrl);
        });

        // (Fungsi untuk drag, resize, dan tambah signature bisa dicopy dari sign.blade.php dan disesuaikan)
        // ... Implementasi fungsi enableDragFor, listener untuk sigFileInputEdit, dll. ...
        // NOTE: Pastikan untuk mengganti ID elemen ke versi 'Edit' (e.g., pdfContainer -> pdfContainerEdit)
        // --- Drag & Resize Signature ---
            function enableDragFor(el) {
                let isDragging=false,startX=0,startY=0,origLeft=0,origTop=0;
                el.addEventListener('pointerdown',(ev)=>{
                    ev.preventDefault(); isDragging=true; el.setPointerCapture(ev.pointerId);
                    const rect=el.getBoundingClientRect();
                    const parentRect=pdfContainerEdit.getBoundingClientRect();
                    startX=ev.clientX; startY=ev.clientY;
                    origLeft=rect.left-parentRect.left; origTop=rect.top-parentRect.top;
                });
                document.addEventListener('pointermove',(ev)=>{
                    if(!isDragging) return;
                    const parentRect=pdfContainerEdit.getBoundingClientRect();
                    let left=origLeft+(ev.clientX-startX);
                    let top=origTop+(ev.clientY-startY);
                    left=Math.max(0,Math.min(left,parentRect.width-el.offsetWidth));
                    top=Math.max(0,Math.min(top,parentRect.height-el.offsetHeight));
                    el.style.left=left+'px'; el.style.top=top+'px';
                });
                document.addEventListener('pointerup',(ev)=>{
                    if(!isDragging) return; isDragging=false;
                    try{el.releasePointerCapture(ev.pointerId);}catch(e){}
                });
            }

            // --- Pilih tanda tangan: validasi tipe & ukuran ---
            sigFileInputEdit.addEventListener('change', (e) => {
                const files = Array.from(e.target.files);
                files.forEach(file => {
                    const validTypes = ['image/png', 'image/jpg', 'image/jpeg'];
                    if (!validTypes.includes(file.type)) {
                        Swal.fire('Peringatan', `File "${file.name}" bukan PNG/JPG/JPEG`, 'warning');
                        return;
                    }
                    if (file.size > 5 * 1024 * 1024) {
                        Swal.fire('Peringatan', `File "${file.name}" terlalu besar. Maksimal 5 MB`, 'warning');
                        return;
                    }

                    const url = URL.createObjectURL(file);
                    const img = document.createElement('img');
                    img.src = url;
                    img.className = 'sign-img';
                    img.style.width = '180px';
                    img.style.top = '16px';
                    img.style.left = '16px';
                    pdfContainerEdit.appendChild(img);
                    enableDragFor(img);

                    const idx = signaturesEdit.length;

                    // Tombol close ×
                    const btn = document.createElement('button');
                    btn.innerText = '×';
                    btn.style.position = 'absolute';
                    btn.style.top = '0';
                    btn.style.right = '0';
                    btn.style.zIndex = 10000;
                    btn.style.background = 'red';
                    btn.style.color = 'white';
                    btn.style.border = 'none';
                    btn.style.borderRadius = '50%';
                    btn.style.width = '20px';
                    btn.style.height = '20px';
                    btn.style.cursor = 'pointer';
                    btn.addEventListener('click', () => {
                        pdfContainerEdit.removeChild(img);
                        pdfContainerEdit.removeChild(btn);
                        const sidx = signaturesEdit.findIndex(s => s.imgElem === img);
                        if (sidx >= 0) {
                            const sliderDiv = document.getElementById(`slider-${sidx}`);
                            if (sliderDiv) sliderDiv.remove();
                            signaturesEdit.splice(sidx, 1);
                        }
                    });
                    pdfContainerEdit.appendChild(btn);

                    // Slider resize
                    const sliderDiv = document.createElement('div');
                    sliderDiv.id = `slider-${idx}`;
                    sliderDiv.className = 'mt-2';
                    sliderDiv.innerHTML = `<label>Resize tanda tangan ${idx+1}: </label>
                        <input type="range" min="30" max="600" value="180" class="individual-slider">`;
                    signatureControlsEdit.appendChild(sliderDiv);
                    const slider = sliderDiv.querySelector('input');
                    slider.addEventListener('input', () => { img.style.width = slider.value + 'px'; });

                    signaturesEdit.push({ file, imgElem: img, slider, page: 1, x: 0, y: 0, w: 0 });
                });

                // Reset input supaya bisa pilih file yang sama lagi
                sigFileInputEdit.value = '';
            });


        // Event listener untuk tombol Simpan Perubahan
        document.getElementById('updateAndSubmitBtn').addEventListener('click', async () => {
            if (!currentDokumenId) return;

            if (!pdfFileInputEdit.files[0] && signaturesEdit.length === 0) {
                Swal.fire('Info', 'Tidak ada perubahan yang dilakukan. Pilih PDF baru atau tambahkan tanda tangan baru.', 'info');
                return;
            }

            Swal.fire({
                title: 'Menyimpan perubahan...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            const formData = new FormData(editSignForm); // Form data dasar

            // KELENGKAPAN KODE DIMULAI DI SINI (LOGIKA UNTUK MENAMBAHKAN TANDA TANGAN)
            signaturesEdit.forEach((s, index) => {
                const sigRect = s.imgElem.getBoundingClientRect();
                const sigCenterX = sigRect.left + sigRect.width / 2;
                const sigCenterY = sigRect.top + sigRect.height / 2;
                
                let matchedPage = pageViewsEdit[0];
                for (const pv of pageViewsEdit) {
                    const pageRect = pv.elem.getBoundingClientRect();
                    if (sigCenterX >= pageRect.left && sigCenterX <= pageRect.right && 
                        sigCenterY >= pageRect.top && sigCenterY <= pageRect.bottom) {
                        matchedPage = pv;
                        break;
                    }
                }
                
                const finalPageRect = matchedPage.elem.getBoundingClientRect();
                const relativeX = (sigRect.left - finalPageRect.left) / finalPageRect.width;
                const relativeY = (sigRect.top - finalPageRect.top) / finalPageRect.height;
                const relativeW = sigRect.width / finalPageRect.width;

                // Gunakan 'files' dan 'signatures' agar cocok dengan controller
                formData.append(`files[${index}]`, s.file);
                formData.append(`signatures[${index}][page]`, matchedPage.pageNumber);
                formData.append(`signatures[${index}][x]`, relativeX);
                formData.append(`signatures[${index}][y]`, relativeY);
                formData.append(`signatures[${index}][w]`, relativeW);
            });
            // KELENGKAPAN KODE BERAKHIR DI SINI

            try {
                const response = await fetch(editSignForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        // Hapus 'Content-Type'. Browser akan set otomatis dengan boundary yang benar untuk multipart/form-data
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json' // Beri tahu server kita mengharapkan JSON
                    }
                });

                // Cek jika respons bukan JSON sebelum mencoba parse
                const contentType = response.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    // Jika bukan JSON, kemungkinan ini halaman error HTML
                    const errorText = await response.text();
                    console.error("Server Response (HTML):", errorText);
                    throw new Error("Server tidak memberikan respons JSON. Cek tab Network di DevTools untuk detail error server.");
                }
                
                const result = await response.json();

                if (response.ok) { // Status 200-299
                    Swal.fire('Berhasil!', result.message, 'success');
                    const modal = bootstrap.Modal.getInstance(editModalEl);
                    modal.hide();
                    
                    editModalEl.addEventListener('hidden.bs.modal', () => {
                        location.reload();
                    }, { once: true });
                } else {
                    // Tangani error dari JSON (misal, error validasi)
                    throw new Error(result.message || 'Terjadi kesalahan yang tidak diketahui.');
                }

            } catch (error) {
                Swal.fire('Error!', error.message, 'error');
            }
        });
    </script>
</x-layout>
