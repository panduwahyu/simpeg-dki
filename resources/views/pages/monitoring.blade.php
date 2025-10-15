<x-layout bodyClass="g-sidenav-show bg-gray-200">
    <x-navbars.sidebar activePage="monitoring-pegawai"></x-navbars.sidebar>

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-navbars.navs.auth titlePage="Monitoring"></x-navbars.navs.auth>

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
                                    <option value="{{ $nama }}">{{ $nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="tahun" class="form-label">Tahun</label>
                            <input type="text" id="tahun" class="form-control p-2" readonly>
                        </div>
                        <div class="col-md-4">
                            <label for="periode" class="form-label">Periode</label>
                            <input type="text" id="periode" class="form-control p-2" readonly>
                        </div>
                    </form>

                    {{-- PROGRESS BAR --}}
                    <div id="progressBars" class="mb-4" style="display:none;">
                        <div class="mb-2">
                            <label>Progres Pegawai Unggah Dokumen: <span id="progressUploadedText"></span></label>
                            <div class="progress">
                                <div id="progressUploadedBar" class="progress-bar bg-primary progress-bar-striped progress-bar-animated" role="progressbar" style="width:0%"></div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label>Progres Penandatanganan Dokumen: <span id="progressSignedText"></span></label>
                            <div class="progress">
                                <div id="progressSignedBar" class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width:0%"></div>
                            </div>
                        </div>
                    </div>

                    {{-- FILTER STATUS TAMBAHAN --}}
                    <div id="filterStatusContainer" class="row g-3 mb-4 ps-2 pe-2" style="display:none;">
                        <div class="col-md-4">
                            <label for="statusFilter" class="form-label">Filter Status Pegawai</label>
                            <select id="statusFilter" class="form-select p-2">
                                <option value="">-- Semua Pegawai --</option>
                                <option value="belum-unggah">Pegawai belum unggah</option>
                                <option value="menunggu">Belum ditandatangani</option>
                                <option value="selesai">Dokumen lengkap</option>
                            </select>
                        </div>
                    </div>

                    {{-- TABEL MONITORING --}}
                    <div class="mb-4">
                        <div class="table-responsive custom-table-wrapper">
                            <p class="text-center text-muted">Silakan pilih nama dokumen terlebih dahulu.</p>
                        </div>
                    </div>

                    {{-- LEGENDA --}}
                    <div class="mt-3 d-flex gap-4 align-items-center justify-content-center">
                        <div><i class="fas fa-times-circle text-danger"></i> Belum Unggah</div>
                        <div><i class="fas fa-exclamation-circle text-warning"></i> Belum Ditandatangani</div>
                        <div><i class="fas fa-check-circle text-success"></i> Dokumen Lengkap</div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    {{-- ========================================================== --}}
    {{-- MODAL UNTUK TANDA TANGAN DOKUMEN --}}
    {{-- ========================================================== --}}
    <div class="modal fade" id="signModal" tabindex="-1" aria-labelledby="signModalLabel" aria-hidden="true">
        {{-- DIUBAH: Ukuran modal dari 'modal-xl' menjadi 'modal-lg' --}}
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="signModalLabel">Tandatangani Dokumen</h5>
                    {{-- DIUBAH: Tombol close menggunakan Font Awesome agar pasti terlihat --}}
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="signModalForm">
                        @csrf
                        <input type="hidden" name="user_id" id="modal_user_id">
                        <input type="hidden" name="jenis_dokumen_id" id="modal_jenis_dokumen_id">
                        <input type="hidden" name="periode_id" id="modal_periode_id">
                        <div class="alert alert-light" role="alert">
                            <h6 class="alert-heading" id="modal_nama_pegawai"></h6>
                            <p id="modal_info_dokumen" class="mb-0"></p>
                        </div>
                        <div class="mb-3">
                            <label for="sigFileModal" class="form-label">Pilih Tanda Tangan (PNG/JPG)</label>
                            <input type="file" id="sigFileModal" class="form-control" accept="image/*" multiple required>
                        </div>
                        <div id="viewerWrapModal" class="border" style="height:60vh; overflow:auto; position:relative; background:#efefef;">
                            <div id="pdfViewerModal" style="position:relative; width:fit-content; margin:auto;"></div>
                        </div>
                        <p class="mt-3 text-muted small">Geser tanda tangan ke posisi yang diinginkan → atur ukuran → klik "Simpan Tanda Tangan".</p>
                        <div id="sigControlsModal" class="mt-3"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" id="submitSignModalBtn" class="btn btn-primary">Simpan Tanda Tangan</button>
                </div>
            </div>
        </div>
    </div>

    <x-plugins></x-plugins>

    {{-- Dependensi & CSS --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.13.216/pdf.min.js"></script>
    <style>
        .custom-table-wrapper { overflow-x: auto; max-height: 500px; border: 1px solid #dee2e6; border-radius: 8px; }
        .table thead th { position: sticky; top: 0; background: #f8f9fa; z-index: 3; }
        .sticky-col { position: sticky; left: 0; z-index: 5; background: #fff; }
        .table th, .table td { white-space: nowrap; vertical-align: middle; }
        .table thead tr:nth-child(2) th { top: 50px; }
        .page-wrap { position: relative; margin-bottom: 18px; display: inline-block; }
        .sign-img { position: absolute; cursor: grab; user-select: none; touch-action: none; z-index: 999; }
        
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
    document.addEventListener('DOMContentLoaded', function() {
        if (pdfjsLib && !pdfjsLib.GlobalWorkerOptions.workerSrc) {
            pdfjsLib.GlobalWorkerOptions.workerSrc = `https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.13.216/pdf.worker.min.js`;
        }
        
        const signModalEl = document.getElementById('signModal');
        const signModalInstance = new bootstrap.Modal(signModalEl);
        const pdfViewerModal = document.getElementById('pdfViewerModal');
        const sigFileModalInput = document.getElementById('sigFileModal');
        const sigControlsModal = document.getElementById('sigControlsModal');
        const submitSignModalBtn = document.getElementById('submitSignModalBtn');

        let modalPdfDoc = null;
        let modalPageViews = [];
        let modalSignatures = [];

        function resetModalState() {
            pdfViewerModal.innerHTML = '';
            sigControlsModal.innerHTML = '';
            document.getElementById('signModalForm').reset();
            modalPdfDoc = null;
            modalPageViews = [];
            modalSignatures = [];
        }

        document.getElementById('nama_dokumen').addEventListener('change', function() {
            const namaDokumen = this.value;
            if (!namaDokumen) return;

            const tableWrapper = document.querySelector('.custom-table-wrapper');
            tableWrapper.innerHTML = '<p class="text-center text-muted">Memuat data...</p>';

            fetch(`/monitoring/data/${encodeURIComponent(namaDokumen)}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('tahun').value = data.tahun;
                    document.getElementById('periode').value = data.periode_tipe;
                    updateProgressBars(data);
                    
                    if (!data.monitoring || !data.monitoring.tabel || data.monitoring.tabel.length === 0) {
                        tableWrapper.innerHTML = '<p class="text-center text-muted">Data monitoring kosong.</p>';
                        document.getElementById('filterStatusContainer').style.display = 'none';
                        return;
                    }
                    tableWrapper.innerHTML = buildTable(data);
                    attachIconClickListeners();
                    document.getElementById('filterStatusContainer').style.display = 'flex';
                    applyStatusFilter();
                });
        });

        function updateProgressBars(data) {
            document.getElementById('progressBars').style.display = 'block';
            document.getElementById('progressUploadedText').innerText = data.progressUploadedText;
            document.getElementById('progressSignedText').innerText = data.progressSignedText;
            
            let uploadedParts = data.progressUploadedText.split(' ');
            let signedParts = data.progressSignedText.split(' ');
            
            let upCount = parseInt(uploadedParts[0]) || 0;
            let upTotal = parseInt(uploadedParts[2]) || 0;
            let sigCount = parseInt(signedParts[0]) || 0;
            let sigTotal = parseInt(signedParts[2]) || 0;

            const upPerc = upTotal > 0 ? (upCount / upTotal * 100) : 0;
            const sigPerc = sigTotal > 0 ? (sigCount / sigTotal * 100) : 0;

            document.getElementById('progressUploadedBar').style.width = `${upPerc}%`;
            document.getElementById('progressSignedBar').style.width = `${sigPerc}%`;
        }

        function buildTable(data) {
            let html = '<table class="table border border-1 border-secondary-subtle text-center align-middle">';
            let periode = data.periode_tipe.toLowerCase();
            html += '<thead class="bg-light"><tr>';
            html += `<th class="sticky-col bg-white fw-bold" ${periode === 'tahunan' ? '' : 'rowspan="2"'}>Nama Pegawai</th>`;
            if (periode === 'bulanan') html += `<th colspan="12">${data.tahun}</th>`;
            else if (periode === 'triwulanan') html += `<th colspan="4">${data.tahun}</th>`;
            else if (periode === 'tahunan') html += `<th>${data.tahun}</th>`;
            html += '</tr>';
            if (periode === 'bulanan') {
                html += '<tr>';
                ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'].forEach(b => html += `<th>${b}</th>`);
                html += '</tr>';
            } else if (periode === 'triwulanan') {
                html += '<tr>';
                ['Triwulan 1','Triwulan 2','Triwulan 3','Triwulan 4'].forEach(t => html += `<th>${t}</th>`);
                html += '</tr>';
            }
            html += '</thead><tbody>';

            const renderCell = (status, penilaian, userId, jenisId, periodeId, namaPegawai, namaDokumen, periodeKey) => {
                let iconClass, statusKey, dataAttrs;
                dataAttrs = `data-user-id="${userId || ''}" data-jenis-id="${jenisId || ''}" data-periode-id="${periodeId || ''}" data-nama-pegawai="${namaPegawai}" data-nama-dokumen="${namaDokumen}" data-periode-key="${periodeKey}"`;
                if (status == 0) { statusKey = 'belum-unggah'; iconClass = 'fas fa-times-circle text-danger';
                } else if (status == 1 && penilaian == 0) { statusKey = 'menunggu'; iconClass = 'fas fa-exclamation-circle text-warning';
                } else if (status == 1 && penilaian == 1) { statusKey = 'selesai'; iconClass = 'fas fa-check-circle text-success';
                } else { return '<td></td>'; }
                return `<td class="status-icon" style="cursor: pointer;" ${dataAttrs} data-status="${statusKey}"><i class="${iconClass}"></i></td>`;
            };

            const selectedDocName = document.getElementById('nama_dokumen').value;
            data.monitoring.tabel.forEach(row => {
                html += `<tr data-pegawai-row>`;
                html += `<td class="sticky-col bg-white fw-bold">${row.nama}</td>`;
                if (periode === 'bulanan') {
                    for (let i = 1; i <= 12; i++) {
                        const periodeKey = new Date(data.tahun, i - 1, 1).toLocaleString('id-ID', { month: 'long' });
                        html += renderCell(row[i] ?? 0, row[i+'_penilaian'] ?? 0, row.user_id, row[i+'_jenis_id'], row[i+'_periode_id'], row.nama, selectedDocName, periodeKey);
                    }
                } else if (periode === 'triwulanan') {
                    for (let i = 1; i <= 4; i++) {
                        const periodeKey = `Triwulan ${i}`;
                        html += renderCell(row[periodeKey] ?? 0, row[`Triwulan_${i}_penilaian`] ?? 0, row.user_id, row[`Triwulan_${i}_jenis_id`], row[`Triwulan_${i}_periode_id`], row.nama, selectedDocName, periodeKey);
                    }
                } else if (periode === 'tahunan') {
                    html += renderCell(row.tahun ?? 0, row.tahun_penilaian ?? 0, row.user_id, row.tahun_jenis_id, row.tahun_periode_id, row.nama, selectedDocName, data.tahun);
                }
                html += '</tr>';
            });
            html += '</tbody></table>';
            return html;
        }

        function attachIconClickListeners() {
            document.querySelectorAll('.status-icon').forEach(td => {
                td.addEventListener('click', async function() {
                    const { status, userId, jenisId, periodeId, namaPegawai, namaDokumen, periodeKey } = this.dataset;
                    if (!userId || !jenisId || !periodeId || status === '') return;

                    if (status === 'belum-unggah') {
                        window.open(`/pdf/supervisoradmin?user_id=${userId}&jenis_dokumen_id=${jenisId}&periode_id=${periodeId}`, '_blank');
                    } else if (status === 'menunggu') {
                        resetModalState();
                        document.getElementById('modal_user_id').value = userId;
                        document.getElementById('modal_jenis_dokumen_id').value = jenisId;
                        document.getElementById('modal_periode_id').value = periodeId;
                        document.getElementById('modal_nama_pegawai').textContent = namaPegawai;
                        document.getElementById('modal_info_dokumen').textContent = `${namaDokumen} - Periode ${periodeKey}`;
                        signModalInstance.show();
                        try {
                            const response = await fetch(`/ajax-preview-url/${userId}/${jenisId}/${periodeId}`);
                            const data = await response.json();
                            if (data.success) await renderPdfInModal(data.url);
                            else pdfViewerModal.innerHTML = `<p class="text-danger p-5 text-center">${data.message}</p>`;
                        } catch (e) { pdfViewerModal.innerHTML = `<p class="text-danger p-5 text-center">Gagal memuat dokumen.</p>`; }
                    } else if (status === 'selesai') {
                        window.open(`/monitoring/preview/${userId}/${jenisId}/${periodeId}`, '_blank');
                    }
                });
            });
        }
        
        async function renderPdfInModal(pdfUrl) {
            pdfViewerModal.innerHTML = '<p class="text-center p-5">Memuat preview...</p>';
            try {
                const loadingTask = pdfjsLib.getDocument(pdfUrl);
                modalPdfDoc = await loadingTask.promise;
                pdfViewerModal.innerHTML = '';
                const containerWidth = pdfViewerModal.clientWidth > 0 ? pdfViewerModal.clientWidth : 800;
                for (let i = 1; i <= modalPdfDoc.numPages; i++) {
                    const page = await modalPdfDoc.getPage(i);
                    const viewport = page.getViewport({ scale: 1 });
                    const scale = (containerWidth - 40) / viewport.width;
                    const scaledViewport = page.getViewport({ scale });
                    const pageWrap = document.createElement('div');
                    pageWrap.className = 'page-wrap'; pageWrap.style.width = `${scaledViewport.width}px`; pageWrap.style.height = `${scaledViewport.height}px`; pageWrap.dataset.pageNumber = i;
                    const canvas = document.createElement('canvas');
                    canvas.width = scaledViewport.width; canvas.height = scaledViewport.height;
                    pageWrap.appendChild(canvas);
                    pdfViewerModal.appendChild(pageWrap);
                    await page.render({ canvasContext: canvas.getContext('2d'), viewport: scaledViewport }).promise;
                    modalPageViews.push({ pageNumber: i, elem: pageWrap });
                }
            } catch (error) { pdfViewerModal.innerHTML = `<p class="text-center p-5 text-danger">Gagal memuat preview PDF.</p>`; }
        }

        function enableDragFor(el, container) {
            let isDragging=false,startX=0,startY=0,origLeft=0,origTop=0;
            el.addEventListener('pointerdown',(ev)=>{
                ev.preventDefault(); isDragging=true; el.setPointerCapture(ev.pointerId);
                const rect=el.getBoundingClientRect(); const parentRect=container.getBoundingClientRect();
                startX=ev.clientX; startY=ev.clientY;
                origLeft=rect.left-parentRect.left; origTop=rect.top-parentRect.top;
            });
            document.addEventListener('pointermove',(ev)=>{
                if(!isDragging) return;
                const parentRect=container.getBoundingClientRect();
                let left=origLeft+(ev.clientX-startX), top=origTop+(ev.clientY-startY);
                left=Math.max(0,Math.min(left,parentRect.width-el.offsetWidth));
                top=Math.max(0,Math.min(top,parentRect.height-el.offsetHeight));
                el.style.left=left+'px'; el.style.top=top+'px';
            });
            document.addEventListener('pointerup',(ev)=>{ if(!isDragging) return; isDragging=false; try{el.releasePointerCapture(ev.pointerId);}catch(e){} });
        }

        sigFileModalInput.addEventListener('change', (e) => {
            Array.from(e.target.files).forEach(file => {
                const url = URL.createObjectURL(file);
                const img = document.createElement('img');
                img.src = url; img.className = 'sign-img';
                img.style.width = '180px'; img.style.top = '16px'; img.style.left = '16px';
                pdfViewerModal.appendChild(img);
                enableDragFor(img, pdfViewerModal);

                // Tombol close ×

                const idx = modalSignatures.length;

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
                    pdfViewerModal.removeChild(img);
                    pdfViewerModal.removeChild(btn);
                    const sidx = modalSignatures.findIndex(s => s.imgElem === img);
                    if (sidx >= 0) {
                        const sliderDiv = document.getElementById(`slider-modal-${sidx}`);
                        if (sliderDiv) sliderDiv.remove();
                        modalSignatures.splice(sidx, 1);
                    }
                });
                pdfViewerModal.appendChild(btn);

                const sliderDiv = document.createElement('div');
                sliderDiv.id = `slider-modal-${idx}`; sliderDiv.className = 'mt-2';
                sliderDiv.innerHTML = `<label>Atur Ukuran TTD ${idx+1}: </label><input type="range" min="30" max="600" value="180">`;
                sigControlsModal.appendChild(sliderDiv);
                sliderDiv.querySelector('input').addEventListener('input', (e) => { img.style.width = e.target.value + 'px'; });
                modalSignatures.push({ file, imgElem: img });
            });
            e.target.value = '';
        });

        submitSignModalBtn.addEventListener('click', async () => {
            if (modalSignatures.length === 0) {
                Swal.fire('Peringatan', 'Silakan pilih minimal satu file tanda tangan.', 'warning'); return;
            }
            submitSignModalBtn.disabled = true;
            submitSignModalBtn.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Menyimpan...`;

            const formData = new FormData(document.getElementById('signModalForm'));
            modalSignatures.forEach((s, i) => {
                const sigRect = s.imgElem.getBoundingClientRect();
                let matchedPage = modalPageViews.find(pv => {
                    const pageRect = pv.elem.getBoundingClientRect();
                    return sigRect.top >= pageRect.top && sigRect.bottom <= pageRect.bottom;
                }) || modalPageViews[0];

                const finalPageRect = matchedPage.elem.getBoundingClientRect();
                formData.append(`files[${i}]`, s.file);
                formData.append(`signatures[${i}][page]`, matchedPage.pageNumber);
                formData.append(`signatures[${i}][x]`, (sigRect.left - finalPageRect.left) / finalPageRect.width);
                formData.append(`signatures[${i}][y]`, (sigRect.top - finalPageRect.top) / finalPageRect.height);
                formData.append(`signatures[${i}][w]`, sigRect.width / finalPageRect.width);
            });
            
            try {
                const response = await fetch('{{ route("pdf.sign.ajax") }}', {
                    method: 'POST', body: formData, headers: { 'Accept': 'application/json' }
                });
                const result = await response.json();
                if (!response.ok) throw new Error(result.message || 'Terjadi kesalahan di server.');
                Swal.fire('Berhasil!', result.message, 'success').then(() => location.reload());
            } catch (error) {
                Swal.fire('Error!', error.message, 'error');
            } finally {
                submitSignModalBtn.disabled = false;
                submitSignModalBtn.innerHTML = 'Simpan Tanda Tangan';
            }
        });
        
        function applyStatusFilter() {
            const val = document.getElementById('statusFilter').value;
            document.querySelectorAll('tbody [data-pegawai-row]').forEach(row => {
                if (val === '') {
                    row.style.display = '';
                    return;
                }
                let hasVisibleStatus = Array.from(row.querySelectorAll('.status-icon')).some(iconCell => iconCell.dataset.status === val);
                row.style.display = hasVisibleStatus ? '' : 'none';
            });
        }
        
        // Daftarkan listener untuk filter status HANYA SEKALI.
        document.getElementById('statusFilter').addEventListener('change', applyStatusFilter);
    });
    </script>
</x-layout>