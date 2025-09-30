<x-layout bodyClass="g-sidenav-show bg-gray-200">
    <x-navbars.sidebar activePage="pdf-sign"></x-navbars.sidebar>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-navbars.navs.auth titlePage="Tanda Tangan PDF"></x-navbars.navs.auth>
        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-lg-10 mx-auto">
                    <div class="card">
                        <div class="card-header pb-0 px-3">
                            <h6 class="mb-0">Upload & Tandatangani PDF (Drag & Resize multiple tanda tangan)</h6>
                        </div>
                        <div class="card-body pt-4 p-3">
                            <form id="signForm" action="{{ route('pdf.sign') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label for="dokumenSelect" class="form-label">Dokumen</label>
                                        <select id="dokumenSelect" name="mandatory_id" class="form-select" required>
                                            <option value="">-- Pilih Dokumen --</option>
                                            @foreach($belumUpload as $dokumen)
                                                <option value="{{ $dokumen->id }}">{{ $dokumen->nama_dokumen }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="periodeSelect" class="form-label">Periode</label>
                                        <select id="periodeSelect" name="periode_id" class="form-select" required>
                                            <option value="">-- Pilih Periode --</option>
                                            @foreach($belumUpload as $periode)
                                                <option value="{{ $periode->id }}">{{ $periode->periode_key }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Pilih file PDF</label>
                                        <input type="file" id="pdfFile" name="pdf" accept="application/pdf" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Pilih tanda tangan (PNG/JPG)</label>
                                        <input type="file" id="sigFile" accept="image/*" class="form-control" multiple>
                                    </div>
                                </div>

                                {{-- signatures hasil JS (metadata) --}}
                                <input type="hidden" name="signatures" id="signaturesInput">

                                <div id="viewerWrap" class="border" style="height:70vh; overflow:auto; position:relative; background:#efefef;">
                                    <div id="pdf-container" style="position:relative; width:fit-content; margin:16px;"></div>
                                </div>

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
            <x-footers.auth></x-footers.auth>
        </div>
    </main>
    <x-plugins></x-plugins>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.13.216/pdf.min.js"></script>

    <style>
        .page-wrap {
            position: relative;
            margin-bottom: 18px;
            display: inline-block;
        }
        .page-canvas {
            display: block;
        }
        .sign-img {
            position: absolute;
            cursor: grab;
            user-select: none;
            touch-action: none;
            z-index: 999;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const pdfFileInput = document.getElementById('pdfFile');
            const sigFileInput = document.getElementById('sigFile');
            const dokumenSelect = document.getElementById('dokumenSelect');
            const pdfContainer = document.getElementById('pdf-container');
            const placeAndSubmitBtn = document.getElementById('placeAndSubmit');
            const signaturesInput = document.getElementById('signaturesInput');
            const signatureControls = document.getElementById('signature-controls');

            if (pdfjsLib && !pdfjsLib.GlobalWorkerOptions.workerSrc) {
                pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.13.216/pdf.worker.min.js';
            }

            let pdfDocument = null;
            let pageViews = [];
            let signatures = [];

            function resetPreview() {
                pdfContainer.innerHTML = '';
                signatureControls.innerHTML = '';
                pageViews = [];
                pdfDocument = null;
                signatures = [];
            }

            // Load PDF preview
            pdfFileInput.addEventListener('change', async (e) => {
                resetPreview();
                const file = e.target.files[0];
                if (!file) return;

                const url = URL.createObjectURL(file);
                const loadingTask = pdfjsLib.getDocument(url);
                pdfDocument = await loadingTask.promise;

                const containerWidth = Math.max(800, pdfContainer.clientWidth - 40);

                for (let i = 1; i <= pdfDocument.numPages; i++) {
                    const page = await pdfDocument.getPage(i);
                    const viewport = page.getViewport({ scale: 1 });
                    const scale = Math.min(1000, containerWidth) / viewport.width;
                    const scaledViewport = page.getViewport({ scale });

                    const pageWrap = document.createElement('div');
                    pageWrap.className = 'page-wrap';
                    pageWrap.style.width = scaledViewport.width + 'px';
                    pageWrap.style.height = scaledViewport.height + 'px';
                    pageWrap.dataset.pageNumber = i;

                    const canvas = document.createElement('canvas');
                    canvas.className = 'page-canvas';
                    canvas.width = scaledViewport.width;
                    canvas.height = scaledViewport.height;
                    pageWrap.appendChild(canvas);
                    pdfContainer.appendChild(pageWrap);

                    const ctx = canvas.getContext('2d');
                    await page.render({ canvasContext: ctx, viewport: scaledViewport }).promise;

                    pageViews.push({ pageNumber: i, elem: pageWrap });
                }
            });

            // Enable drag for signature
            function enableDragFor(el) {
                let isDragging = false, startX=0, startY=0, origLeft=0, origTop=0;

                el.addEventListener('pointerdown', (ev)=>{
                    ev.preventDefault();
                    isDragging = true;
                    el.setPointerCapture(ev.pointerId);

                    const rect = el.getBoundingClientRect();
                    const parentRect = pdfContainer.getBoundingClientRect();
                    startX = ev.clientX;
                    startY = ev.clientY;
                    origLeft = rect.left - parentRect.left;
                    origTop = rect.top - parentRect.top;
                });

                document.addEventListener('pointermove', (ev)=>{
                    if(!isDragging) return;
                    const parentRect = pdfContainer.getBoundingClientRect();
                    let left = origLeft + (ev.clientX - startX);
                    let top = origTop + (ev.clientY - startY);
                    left = Math.max(0, Math.min(left, parentRect.width - el.offsetWidth));
                    top = Math.max(0, Math.min(top, parentRect.height - el.offsetHeight));
                    el.style.left = left + 'px';
                    el.style.top = top + 'px';
                });

                document.addEventListener('pointerup', (ev)=>{
                    if(!isDragging) return;
                    isDragging=false;
                    try { el.releasePointerCapture(ev.pointerId); } catch(e){}
                });
            }

            // Tambahkan signature image
            sigFileInput.addEventListener('change', (e) => {
                const files = Array.from(e.target.files);
                files.forEach(file => {
                    const url = URL.createObjectURL(file);
                    const img = document.createElement('img');
                    img.src = url;
                    img.className = 'sign-img';
                    img.style.width = '180px';
                    img.style.top = '16px';
                    img.style.left = '16px';
                    pdfContainer.appendChild(img);
                    enableDragFor(img);

                    // tombol hapus
                    const btn = document.createElement('button');
                    btn.innerText = '×';
                    btn.style.position='absolute';
                    btn.style.top='0';
                    btn.style.right='0';
                    btn.style.zIndex=10000;
                    btn.style.background='red';
                    btn.style.color='white';
                    btn.style.border='none';
                    btn.style.borderRadius='50%';
                    btn.style.width='20px';
                    btn.style.height='20px';
                    btn.style.cursor='pointer';

                    btn.addEventListener('click', ()=>{
                        pdfContainer.removeChild(img);
                        pdfContainer.removeChild(btn);
                        const sidx = signatures.findIndex(s=>s.imgElem===img);
                        if(sidx>=0){
                            const sliderDiv = document.getElementById(`slider-${sidx}`);
                            if(sliderDiv) sliderDiv.remove();
                            signatures.splice(sidx,1);
                        }
                    });

                    pdfContainer.appendChild(btn);

                    // slider resize
                    const idx = signatures.length;
                    const sliderDiv = document.createElement('div');
                    sliderDiv.id = `slider-${idx}`;
                    sliderDiv.className = 'mt-2';
                    sliderDiv.innerHTML = `<label>Resize tanda tangan ${idx+1}: </label> 
                        <input type="range" min="30" max="600" value="180" class="individual-slider">`;
                    signatureControls.appendChild(sliderDiv);

                    const slider = sliderDiv.querySelector('input');
                    slider.addEventListener('input', ()=>{
                        img.style.width = slider.value + 'px';
                    });

                    // push signature object
                    signatures.push({ file, imgElem: img, page: 1, x:0, y:0, w:0, slider });
                });
            });

            // Submit
            placeAndSubmitBtn.addEventListener('click', async ()=> {
                if (!dokumenSelect.value) {
                    alert('Pilih dokumen terlebih dahulu'); return;
                }
                if (!pdfFileInput.files[0]) {
                    alert('Pilih file PDF'); return;
                }
                if (signatures.length===0) {
                    alert('Pilih minimal 1 tanda tangan'); return;
                }
                if (pageViews.length === 0) {
                    alert('Pastikan PDF telah dipreview terlebih dahulu'); return;
                }

                // Hitung posisi relatif signature
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
                    const relX = sigLeft/pageRect.width;
                    const relY = sigTop/pageRect.height;
                    const relW = rect.width/pageRect.width;

                    s.page = matchedPage.pageNumber;
                    s.x = relX;
                    s.y = relY;
                    s.w = relW;
                });

                // Buat FormData
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('mandatory_id', dokumenSelect.value);
                formData.append('pdf', pdfFileInput.files[0]);

                signatures.forEach((s,i)=>{
                    formData.append(`files[${i}]`, s.file);
                    formData.append(`signatures[${i}][page]`, s.page);
                    formData.append(`signatures[${i}][x]`, s.x);
                    formData.append(`signatures[${i}][y]`, s.y);
                    formData.append(`signatures[${i}][w]`, s.w);
                });

                // Kirim via fetch
                try {
                    const res = await fetch('{{ route("pdf.sign") }}', {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin'
                    });
                    if (!res.ok) {
                        const text = await res.text();
                        throw new Error(text || 'Upload gagal');
                    }
                    const blob = await res.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'signed.pdf';
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                } catch (err) {
                    alert('Error: ' + err.message);
                    console.error(err);
                }
            });
        });
    </script>
</x-layout>
