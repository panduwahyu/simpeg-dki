<script>
    const mandatoryMapping = @json($mapping);
</script>


{{-- resources/views/pdf/sign.blade.php --}}
<x-layout bodyClass="g-sidenav-show bg-gray-200">
    <x-navbars.sidebar activePage="pdf-sign"></x-navbars.sidebar>

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-navbars.navs.auth titlePage="Tanda Tangan PDF"></x-navbars.navs.auth>

        <div class="container-fluid py-4">
            <div class="row">  
                <div class="col-lg-11 mx-auto">
                    <div class="card">
                        <div class="card-header pb-0 px-3">
                            <h6 class="mb-0">Upload & Tandatangani PDF (Drag tanda tangan langsung di viewer)</h6>
                        </div>

                        <div class="card-body pt-4 p-3">
                            <form id="signForm" action="{{ route('pdf.sign') }}" method="POST" enctype="multipart/form-data">
                                @csrf

                                <div class="row g-3 mb-3">
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
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Pilih file PDF</label>
                                        <input type="file" id="pdfFile" name="pdf" accept="application/pdf" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Pilih tanda tangan (PNG)</label>
                                        <input type="file" id="sigFile" name="signature" accept="image/*" class="form-control" required>
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Lebar tanda tangan (px)</label>
                                        <input type="range" id="sigWidthRange" min="30" max="600" value="180" class="form-range">
                                        <div class="small text-muted">Gunakan slider untuk ubah ukuran saat preview</div>
                                    </div>

                                    <div class="col-md-8 d-flex align-items-end justify-content-end">
                                        <button type="button" id="clearPreview" class="btn btn-outline-secondary me-2">Reset Preview</button>
                                        <button type="button" id="placeAndSubmit" class="btn btn-dark">Simpan & Download</button>
                                    </div>
                                </div>

                                {{-- Hidden inputs akan diisi oleh JS sebelum submit --}}
                                <input type="hidden" name="page" id="inputPage" value="1">
                                <input type="hidden" name="x_percent" id="inputX" value="0">
                                <input type="hidden" name="y_percent" id="inputY" value="0">
                                <input type="hidden" name="width_percent" id="inputW" value="0">
                                <input type="hidden" name="mandatory_id" id="mandatoryId">

                                {{-- PDF viewer --}}
                                <div id="viewerWrap" class="border" style="height:70vh; overflow:auto; position:relative; background:#efefef;">
                                    <div id="pdf-container" style="position:relative; width:fit-content; margin:16px;"></div>
                                </div>

                                <p class="mt-3 text-muted small">
                                    Cara pakai: pilih PDF & PNG → tanda tangan muncul di atas PDF → geser (drag) ke posisi yang diinginkan → atur ukuran dengan slider → klik "Simpan & Download".
                                </p>
                            </form>

                            {{-- pesan error/done --}}
                            @if ($errors->any())
                                <div class="alert alert-danger mt-3">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $err)
                                            <li>{{ $err }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if (session('success'))
                                <div class="alert alert-success mt-3">
                                    {{ session('success') }}
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

    {{-- PDF.js CDN --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.13.216/pdf.min.js"></script>

    <style>
        .page-wrap { position:relative; margin-bottom:18px; box-shadow:0 2px 6px rgba(0,0,0,0.08); display:inline-block; }
        .page-canvas { display:block; }
        .sign-img { position:absolute; cursor:grab; user-select:none; touch-action:none; }
    </style>

    <script>
        
    document.addEventListener('DOMContentLoaded', function () {
        const pdfFileInput = document.getElementById('pdfFile');
        const sigFileInput = document.getElementById('sigFile');
        const pdfContainer = document.getElementById('pdf-container');
        const viewerWrap = document.getElementById('viewerWrap');
        const sigWidthRange = document.getElementById('sigWidthRange');
        const placeAndSubmitBtn = document.getElementById('placeAndSubmit');
        const clearPreviewBtn = document.getElementById('clearPreview');

        const inputPage = document.getElementById('inputPage');
        const inputX = document.getElementById('inputX');
        const inputY = document.getElementById('inputY');
        const inputW = document.getElementById('inputW');

        if (pdfjsLib) {
            if (!pdfjsLib.GlobalWorkerOptions.workerSrc) {
                pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.13.216/pdf.worker.min.js';
            }
        }

        let currentSignatureImg = null;
        let pdfDocument = null;
        let pageViews = [];

        function resetPreview() {
            pdfContainer.innerHTML = '';
            pageViews = [];
            pdfDocument = null;
            if (currentSignatureImg) {
                currentSignatureImg.remove();
                currentSignatureImg = null;
            }
            inputPage.value = 1;
            inputX.value = 0; inputY.value = 0; inputW.value = 0;
        }

        const dokumenSelect = document.getElementById('dokumenSelect');
        const periodeSelect = document.getElementById('periodeSelect');
        const mandatoryInput = document.getElementById('mandatoryId');

        function updateMandatoryId() {
            const dokumenId = dokumenSelect.value;
            const periodeId = periodeSelect.value;

            const found = mandatoryMapping.find(item =>
                item.jenis_dokumen_id == dokumenId && item.periode_id == periodeId
            );

            mandatoryInput.value = found ? found.mandatory_id : '';
        }

        dokumenSelect.addEventListener('change', updateMandatoryId);
        periodeSelect.addEventListener('change', updateMandatoryId);


        clearPreviewBtn.addEventListener('click', () => {
            resetPreview();
            pdfFileInput.value = '';
            sigFileInput.value = '';
        });

        pdfFileInput.addEventListener('change', async (e) => {
            resetPreview();
            const file = e.target.files[0];
            if (!file) return;
            const url = URL.createObjectURL(file);

            const loadingTask = pdfjsLib.getDocument(url);
            pdfDocument = await loadingTask.promise;

            const containerWidth = Math.max(800, viewerWrap.clientWidth - 40);
            for (let i = 1; i <= pdfDocument.numPages; i++) {
                const page = await pdfDocument.getPage(i);
                const viewport = page.getViewport({ scale: 1 });
                const desiredWidth = Math.min(1000, containerWidth);
                const scale = desiredWidth / viewport.width;
                const scaledViewport = page.getViewport({ scale });

                const pageWrap = document.createElement('div');
                pageWrap.className = 'page-wrap';
                pageWrap.style.width = scaledViewport.width + 'px';
                pageWrap.style.height = scaledViewport.height + 'px';
                pageWrap.dataset.pageNumber = i;
                pageWrap.dataset.renderedWidth = scaledViewport.width;
                pageWrap.dataset.renderedHeight = scaledViewport.height;

                const canvas = document.createElement('canvas');
                canvas.className = 'page-canvas';
                canvas.width = scaledViewport.width;
                canvas.height = scaledViewport.height;
                canvas.style.width = scaledViewport.width + 'px';
                canvas.style.height = scaledViewport.height + 'px';

                pageWrap.appendChild(canvas);
                pdfContainer.appendChild(pageWrap);

                const ctx = canvas.getContext('2d');
                await page.render({ canvasContext: ctx, viewport: scaledViewport }).promise;

                pageViews.push({ pageNumber: i, elem: pageWrap });
            }
            viewerWrap.scrollTop = 0;
        });

        sigFileInput.addEventListener('change', (e) => {
            if (!e.target.files[0]) return;
            const file = e.target.files[0];
            const url = URL.createObjectURL(file);

            if (currentSignatureImg) currentSignatureImg.remove();

            const img = document.createElement('img');
            img.className = 'sign-img';
            img.src = url;
            img.style.width = sigWidthRange.value + 'px';
            img.style.left = '16px';
            img.style.top = '16px';
            img.style.zIndex = 9999;
            img.draggable = false;

            pdfContainer.appendChild(img);
            currentSignatureImg = img;

            enableDragFor(img);
        });

        sigWidthRange.addEventListener('input', () => {
            if (currentSignatureImg) {
                currentSignatureImg.style.width = sigWidthRange.value + 'px';
            }
        });

        function enableDragFor(el) {
            let isDragging = false;
            let startX = 0, startY = 0;
            let origLeft = 0, origTop = 0;

            el.style.position = 'absolute';
            el.style.cursor = 'grab';

            el.addEventListener('pointerdown', (ev) => {
                ev.preventDefault();
                isDragging = true;
                el.setPointerCapture(ev.pointerId);
                el.style.cursor = 'grabbing';
                const rect = el.getBoundingClientRect();
                startX = ev.clientX;
                startY = ev.clientY;
                const parentRect = pdfContainer.getBoundingClientRect();
                origLeft = rect.left - parentRect.left;
                origTop = rect.top - parentRect.top;
            });

            document.addEventListener('pointermove', (ev) => {
                if (!isDragging) return;
                const parentRect = pdfContainer.getBoundingClientRect();
                let left = origLeft + (ev.clientX - startX);
                let top = origTop + (ev.clientY - startY);

                left = Math.max(0, Math.min(left, parentRect.width - el.offsetWidth));
                top = Math.max(0, Math.min(top, parentRect.height - el.offsetHeight));

                el.style.left = left + 'px';
                el.style.top = top + 'px';
            });

            document.addEventListener('pointerup', (ev) => {
                if (!isDragging) return;
                isDragging = false;
                try { el.releasePointerCapture(ev.pointerId); } catch(e){}
                el.style.cursor = 'grab';
                updateHiddenInputs();
            });
        }

        function updateHiddenInputs() {
            if (!currentSignatureImg || pageViews.length === 0) return;

            const sigRect = currentSignatureImg.getBoundingClientRect();
            const sigCenterX = sigRect.left + sigRect.width / 2;
            const sigCenterY = sigRect.top + sigRect.height / 2;

            let matchedPage = null;
            for (const pv of pageViews) {
                const pRect = pv.elem.getBoundingClientRect();
                if (sigCenterX >= pRect.left && sigCenterX <= pRect.right &&
                    sigCenterY >= pRect.top && sigCenterY <= pRect.bottom) {
                    matchedPage = pv;
                    break;
                }
            }

            if (!matchedPage) matchedPage = pageViews[0];

            const pageRect = matchedPage.elem.getBoundingClientRect();

            const sigLeft = sigRect.left - pageRect.left;
            const sigTop = sigRect.top - pageRect.top;
            const relX = sigLeft / pageRect.width;
            const relY = sigTop / pageRect.height;
            const relW = currentSignatureImg.offsetWidth / pageRect.width;

            inputPage.value = matchedPage.pageNumber;
            inputX.value = relX.toFixed(6);
            inputY.value = relY.toFixed(6);
            inputW.value = relW.toFixed(6);
        }

        placeAndSubmitBtn.addEventListener('click', () => {
            if (!pdfFileInput.files[0]) { alert('Silakan pilih file PDF terlebih dahulu'); return; }
            if (!sigFileInput.files[0]) { alert('Silakan pilih file tanda tangan terlebih dahulu'); return; }
            updateHiddenInputs();
            if (!inputPage.value) { alert('Gagal menentukan halaman'); return; }
            document.getElementById('signForm').submit();
        });

        window.addEventListener('resize', () => { updateHiddenInputs(); });
        viewerWrap.addEventListener('scroll', () => { updateHiddenInputs(); });
    });
    </script>
</x-layout>
