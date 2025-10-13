<x-layout bodyClass="g-sidenav-show bg-gray-200">
    <x-navbars.sidebar activePage="pdf-sign-supervisoradmin"></x-navbars.sidebar>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-navbars.navs.auth titlePage="Tanda Tangan PDF"></x-navbars.navs.auth>

        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-lg-10 mx-auto">
                    <div class="card">
                        <div class="card-header pb-0 px-3">
                            <h6 class="mb-0">Upload & Tandatangani PDF (bisa lebih dari satu)</h6>
                        </div>
                        <div class="card-body pt-4 p-3">
                            <form id="signForm" action="{{ route('pdf.sign.supervisor.submit') }}" method="POST" enctype="multipart/form-data">
                                @csrf

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label for="userSelect" class="form-label">Pegawai</label>
                                        <select id="userSelect" name="user_id" class="form-select" required>
                                            <option value="">-- Pilih Pegawai --</option>
                                            @foreach($semuaDokumen->unique('user_id') as $doc)
                                                <option value="{{ $doc->user_id }}">{{ $doc->nama_pegawai }} ({{ $doc->email_pegawai }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="dokumenSelect" class="form-label">Dokumen</label>
                                        <select id="dokumenSelect" name="jenis_dokumen_id" class="form-select" required disabled>
                                            <option value="">-- Pilih Dokumen --</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label for="periodeSelect" class="form-label">Periode</label>
                                        <select id="periodeSelect" name="periode_id" class="form-select" required disabled>
                                            <option value="">-- Pilih Periode --</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Pilih file PDF</label>
                                        <input type="file" id="pdfFile" name="pdf" accept="application/pdf" class="form-control" >
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-12">
                                        <label class="form-label">Pilih tanda tangan (PNG/JPG) max. 5 MB</label>
                                        <input type="file" id="sigFile" accept="image/*" class="form-control" multiple>
                                    </div>
                                </div>

                                <div id="viewerWrap" class="border" style="height:70vh; overflow:auto; position:relative; background:#efefef;">
                                    <div id="pdf-container" style="position:relative; width:fit-content; margin:16px;"></div>
                                </div>

                                <p class="mt-3 text-muted small">
                                    Cara pakai: pilih PDF & PNG → tanda tangan muncul di atas PDF → geser (drag) ke posisi yang diinginkan → atur ukuran dengan slider → klik "Simpan & Download".
                                </p>

                                <div id="signature-controls" class="mt-3"></div>

                                <div class="mt-3 d-flex justify-content-end">
                                    <button type="button" id="placeAndSubmit" class="btn btn-dark">Simpan & Download</button>
                                </div>
                            </form>

                            @if ($errors->any())
                                <div class="alert alert-danger mt-3">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $err)
                                            <li>{{ $err }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <x-plugins></x-plugins>

    <div id="uploadStatus" style="
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 12px 20px;
        background: #333;
        color: #fff;
        border-radius: 8px;
        display: none;
        z-index: 20000;
    ">Status</div>

    <!-- PDF.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.13.216/pdf.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const pdfFileInput = document.getElementById('pdfFile');
        const sigFileInput = document.getElementById('sigFile');
        const userSelect = document.getElementById('userSelect');
        const dokumenSelect = document.getElementById('dokumenSelect');
        const periodeSelect = document.getElementById('periodeSelect');
        const pdfContainer = document.getElementById('pdf-container');
        const placeAndSubmitBtn = document.getElementById('placeAndSubmit');
        const signatureControls = document.getElementById('signature-controls');

        // **BARU**: Ambil data pra-seleksi dari controller
        const preselected = @json($preselected ?? []);

        let pdfDocument = null;
        let pageViews = [];
        let signatures = [];
        let uploadToast;

        // === PDF.js Worker Setup ===
        if (pdfjsLib && !pdfjsLib.GlobalWorkerOptions.workerSrc) {
            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.13.216/pdf.worker.min.js';
        }

        // === Helper Functions (SweetAlert, Reset, dll.) ===
        async function swalAlert(msg) {
            await Swal.fire({ icon: 'error', title: 'Peringatan', text: msg });
        }

         function showUploadStatus(msg, duration=10000) {
            if (uploadToast) uploadToast.close();
            uploadToast = Swal.fire({
                title: msg,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                didOpen: (toast) => {
                    Swal.showLoading();
                    setTimeout(() => { toast.close(); }, duration);
                }
            });
        }

        function resetPreview() {
            pdfContainer.innerHTML = '<p class="text-muted text-center p-5">Pilih file PDF untuk melihat preview.</p>';
            signatureControls.innerHTML = '';
            pageViews = [];
            pdfDocument = null;
            signatures = [];
        }

        // === Fungsi Render PDF (bisa menerima file atau URL) ===
        async function renderPdfPreview(fileOrUrl) {
            resetPreview();
            pdfContainer.innerHTML = '<p class="text-center p-5">Memuat preview...</p>';
            let url = (typeof fileOrUrl === 'string') ? fileOrUrl : URL.createObjectURL(fileOrUrl);

            try {
                const loadingTask = pdfjsLib.getDocument(url);
                pdfDocument = await loadingTask.promise;
                pdfContainer.innerHTML = ''; // Hapus pesan loading

                const containerWidth = Math.max(800, pdfContainer.clientWidth - 40);
                for (let i = 1; i <= pdfDocument.numPages; i++) {
                    const page = await pdfDocument.getPage(i);
                    const viewport = page.getViewport({ scale: 1 });
                    const scale = containerWidth / viewport.width;
                    const scaledViewport = page.getViewport({ scale });

                    const pageWrap = document.createElement('div');
                    pageWrap.className = 'page-wrap';
                    pageWrap.style.width = scaledViewport.width + 'px';
                    pageWrap.style.height = scaledViewport.height + 'px';
                    pageWrap.dataset.pageNumber = i;

                    const canvas = document.createElement('canvas');
                    canvas.width = scaledViewport.width;
                    canvas.height = scaledViewport.height;
                    pageWrap.appendChild(canvas);
                    pdfContainer.appendChild(pageWrap);

                    await page.render({ canvasContext: canvas.getContext('2d'), viewport: scaledViewport }).promise;
                    pageViews.push({ pageNumber: i, elem: pageWrap });
                }
            } catch (error) {
                console.error("Gagal memuat PDF:", error);
                pdfContainer.innerHTML = '<p class="text-center p-5 text-danger">Gagal memuat preview PDF.</p>';
            }
        }

        // === Logika Dropdown Bertingkat (dibuat async) ===
        async function populateDokumen(userId) {
            dokumenSelect.innerHTML = '<option value="">Memuat...</option>';
            periodeSelect.innerHTML = '<option value="">-- Pilih Periode --</option>';
            dokumenSelect.disabled = true;
            periodeSelect.disabled = true;
            if (!userId) return;

            const response = await fetch(`/ajax-dokumen/${userId}`);
            const data = await response.json();

            dokumenSelect.innerHTML = '<option value="">-- Pilih Dokumen --</option>';
            data.forEach(d => {
                dokumenSelect.add(new Option(d.nama_dokumen, d.id)); 
            });
            dokumenSelect.disabled = false;
        }

        async function populatePeriode(userId, dokumenId) {
            periodeSelect.innerHTML = '<option value="">Memuat...</option>';
            periodeSelect.disabled = true;
            if (!userId || !dokumenId) return;

            const response = await fetch(`/ajax-periode/${userId}/${dokumenId}`);
            const data = await response.json();

            periodeSelect.innerHTML = '<option value="">-- Pilih Periode --</option>';
            data.forEach(p => {
                periodeSelect.add(new Option(p.periode_key, p.id));
            });
            periodeSelect.disabled = false;
        }

        // === Event Listeners untuk Dropdown Manual ===
        userSelect.addEventListener('change', function() { populateDokumen(this.value); });
        dokumenSelect.addEventListener('change', function() { populatePeriode(userSelect.value, this.value); });

        // === **BARU**: Logika untuk menangani Pra-seleksi ===
        async function handlePreselection() {
            if (preselected && preselected.user_id) {
                userSelect.value = preselected.user_id;
                await populateDokumen(preselected.user_id); // Tunggu dokumen selesai dimuat

                if (preselected.jenis_dokumen_id) {
                    dokumenSelect.value = preselected.jenis_dokumen_id;
                    await populatePeriode(preselected.user_id, preselected.jenis_dokumen_id); // Tunggu periode

                    if (preselected.periode_id) {
                        periodeSelect.value = preselected.periode_id;
                    }
                }

                // Jika perlu preview otomatis
                if (preselected.needs_preview) {
                    try {
                        const response = await fetch(`/ajax-preview-url/${preselected.user_id}/${preselected.jenis_dokumen_id}/${preselected.periode_id}`);
                        const data = await response.json();
                        if (data.success && data.url) {
                            await renderPdfPreview(data.url);
                            // Karena PDF sudah ada, input file tidak lagi wajib
                            pdfFileInput.required = false;
                        } else {
                            await swalAlert(data.message || 'Gagal mengambil URL preview.');
                        }
                    } catch (e) {
                        await swalAlert('Terjadi kesalahan saat mengambil data preview.');
                    }
                }
            }
        }
        
        // --- Event Listeners untuk File Input ---
        pdfFileInput.addEventListener('change', (e) => {
            if (e.target.files[0]) {
                renderPdfPreview(e.target.files[0]);
                // Jika user memilih file baru, jadikan lagi wajib
                pdfFileInput.required = true;
            }
        });

        // === Drag & Resize ===
        function enableDragFor(el) {
            let isDragging = false, startX=0, startY=0, origLeft=0, origTop=0;
            el.addEventListener('pointerdown', (ev)=>{
                ev.preventDefault(); isDragging=true; el.setPointerCapture(ev.pointerId);
                const rect = el.getBoundingClientRect();
                const parentRect = pdfContainer.getBoundingClientRect();
                startX=ev.clientX; startY=ev.clientY;
                origLeft=rect.left - parentRect.left; origTop=rect.top - parentRect.top;
            });
            document.addEventListener('pointermove',(ev)=>{
                if(!isDragging) return;
                const parentRect=pdfContainer.getBoundingClientRect();
                let left=origLeft+(ev.clientX-startX), top=origTop+(ev.clientY-startY);
                left=Math.max(0, Math.min(left, parentRect.width - el.offsetWidth));
                top=Math.max(0, Math.min(top, parentRect.height - el.offsetHeight));
                el.style.left = left+'px'; el.style.top = top+'px';
            });
            document.addEventListener('pointerup',(ev)=>{
                if(!isDragging) return;
                isDragging=false;
                try{el.releasePointerCapture(ev.pointerId);}catch(e){}
            });
        }

        // === File Validation + Preview ===
        sigFileInput.addEventListener('change', (e) => {
            const files = Array.from(e.target.files);
            const validTypes = ['image/png', 'image/jpeg', 'image/jpg'];
            const maxSize = 5 * 1024 * 1024; // 5 MB
            let hasInvalid = false;

            files.forEach(file => {
                if (!validTypes.includes(file.type)) {
                    Swal.fire({ icon: 'error', title: 'File Tidak Valid', text: `${file.name} bukan file PNG/JPG` });
                    hasInvalid = true;
                } else if (file.size > maxSize) {
                    Swal.fire({ icon: 'error', title: 'File Terlalu Besar', text: `${file.name} lebih dari 5 MB` });
                    hasInvalid = true;
                }
            });

            if (hasInvalid) {
                sigFileInput.value = '';
                return;
            }

            files.forEach(file => {
                const url = URL.createObjectURL(file);
                const img = document.createElement('img');
                img.src = url; img.className='sign-img';
                img.style.width='180px'; img.style.top='16px'; img.style.left='16px';
                pdfContainer.appendChild(img); enableDragFor(img);

                const btn = document.createElement('button');
                btn.innerText='×'; btn.style.position='absolute'; btn.style.top='0';
                btn.style.right='0'; btn.style.zIndex=10000; btn.style.background='red';
                btn.style.color='white'; btn.style.border='none'; btn.style.borderRadius='50%';
                btn.style.width='20px'; btn.style.height='20px'; btn.style.cursor='pointer';
                btn.addEventListener('click', ()=>{
                    pdfContainer.removeChild(img); pdfContainer.removeChild(btn);
                    const sidx = signatures.findIndex(s=>s.imgElem===img);
                    if(sidx>=0){
                        const sliderDiv = document.getElementById(`slider-${sidx}`);
                        if(sliderDiv) sliderDiv.remove();
                        signatures.splice(sidx,1);
                    }
                });
                pdfContainer.appendChild(btn);

                const idx = signatures.length;
                const sliderDiv = document.createElement('div');
                sliderDiv.id=`slider-${idx}`; sliderDiv.className='mt-2';
                sliderDiv.innerHTML = `<label>Resize tanda tangan ${idx+1}: </label>
                    <input type="range" min="30" max="600" value="180" class="individual-slider">`;
                signatureControls.appendChild(sliderDiv);
                const slider = sliderDiv.querySelector('input');
                slider.addEventListener('input',()=>{ img.style.width=slider.value+'px'; });
                signatures.push({ file, imgElem: img, page: 1, x:0, y:0, w:0, slider });
            });
        });

        // === Dependent Dropdowns ===
        userSelect.addEventListener('change', function() {
            const userId = this.value;
            dokumenSelect.innerHTML = '<option value="">Loading...</option>';
            periodeSelect.innerHTML = '<option value="">-- Pilih Periode --</option>';
            dokumenSelect.disabled = true;
            periodeSelect.disabled = true;

            if(!userId) return;

            fetch(`/ajax-dokumen/${userId}`)
                .then(res => res.json())
                .then(data => {
                    dokumenSelect.innerHTML = '<option value="">-- Pilih Dokumen --</option>';
                    data.forEach(d => {
                        const opt = document.createElement('option');
                        opt.value = d.id; opt.text = d.nama_dokumen;
                        dokumenSelect.appendChild(opt);
                    });
                    dokumenSelect.disabled = false;
                });
        });

        dokumenSelect.addEventListener('change', function() {
            const dokumenId = this.value;
            const userId = userSelect.value;
            periodeSelect.innerHTML = '<option value="">Loading...</option>';
            periodeSelect.disabled = true;
            if(!userId || !dokumenId) return;

            fetch(`/ajax-periode/${userId}/${dokumenId}`)
                .then(res => res.json())
                .then(data => {
                    periodeSelect.innerHTML = '<option value="">-- Pilih Periode --</option>';
                    data.forEach(p => {
                        const opt = document.createElement('option');
                        opt.value = p.id; opt.text = p.periode_key;
                        periodeSelect.appendChild(opt);
                    });
                    periodeSelect.disabled = false;
                });
        });

        // === Submit PDF + Signature ===
        placeAndSubmitBtn.addEventListener('click', async ()=> {
            if (!userSelect.value){ await swalAlert('Pilih pegawai'); return; }
            if (!dokumenSelect.value){ await swalAlert('Pilih dokumen'); return; }
            if (!periodeSelect.value){ await swalAlert('Pilih periode'); return; }
            if (pdfFileInput.files.length === 0 && !pdfDocument) {
                await swalAlert('Pilih file PDF');
                return;
            }
            if (signatures.length===0){ await swalAlert('Pilih minimal 1 tanda tangan'); return; }
            if (pageViews.length===0){ await swalAlert('Preview PDF belum siap'); return; }

            showUploadStatus('File sedang diunggah...');

            signatures.forEach(s=>{
                const rect = s.imgElem.getBoundingClientRect();
                const sigCenterX = rect.left + rect.width/2;
                const sigCenterY = rect.top + rect.height/2;
                let matchedPage = pageViews[0];
                for(const pv of pageViews){
                    const pRect = pv.elem.getBoundingClientRect();
                    if(sigCenterX>=pRect.left && sigCenterX<=pRect.right && sigCenterY>=pRect.top && sigCenterY<=pRect.bottom){
                        matchedPage = pv; break;
                    }
                }
                const pageRect = matchedPage.elem.getBoundingClientRect();
                const sigLeft = rect.left - pageRect.left;
                const sigTop = rect.top - pageRect.top;
                s.page = matchedPage.pageNumber;
                s.x = sigLeft/pageRect.width;
                s.y = sigTop/pageRect.height;
                s.w = rect.width/pageRect.width;
            });

            const formData = new FormData();
            formData.append('_token','{{ csrf_token() }}');
            formData.append('user_id', userSelect.value);
            formData.append('jenis_dokumen_id', dokumenSelect.value);
            formData.append('periode_id', periodeSelect.value);
            formData.append('pdf', pdfFileInput.files[0]);
            signatures.forEach((s,i)=>{
                formData.append(`files[${i}]`, s.file);
                formData.append(`signatures[${i}][page]`, s.page);
                formData.append(`signatures[${i}][x]`, s.x);
                formData.append(`signatures[${i}][y]`, s.y);
                formData.append(`signatures[${i}][w]`, s.w);
            });

            try{
                const res = await fetch('{{ route("pdf.sign.supervisor.submit") }}', {
                    method:'POST', body:formData, credentials:'same-origin'
                });
                if(!res.ok){
                    const text = await res.text();
                    throw new Error(text || 'Upload gagal');
                }

                const blob = await res.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;

                const pegawaiName = userSelect.options[userSelect.selectedIndex].text;
                const dokumenName = dokumenSelect.options[dokumenSelect.selectedIndex].text;
                const periodeName = periodeSelect.options[periodeSelect.selectedIndex].text;

                a.download = `Disetujui_${pegawaiName}_${dokumenName}_${periodeName}.pdf`;

                document.body.appendChild(a);
                a.click();
                a.remove();

                Swal.fire({ icon: 'success', title: 'Berhasil', text: 'File berhasil diunggah & disimpan' });
                resetPreview();
                pdfFileInput.value=''; sigFileInput.value=''; dokumenSelect.value=''; periodeSelect.value=''; userSelect.value='';
            }catch(err){
                Swal.fire({ icon: 'error', title: 'Error', text: err.message });
                console.error(err);
            }
        });
        handlePreselection();
    });
    </script>
</x-layout>
