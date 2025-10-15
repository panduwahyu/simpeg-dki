<x-layout bodyClass="g-sidenav-show bg-gray-200">
    <x-navbars.sidebar activePage="form"></x-navbars.sidebar>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-navbars.navs.auth titlePage="Dokumen Baru"></x-navbars.navs.auth>

        <div class="container-fluid py-4">

            <!-- Form Input Dokumen -->
            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                                <h6 class="text-white text-capitalize ps-3 form-title">Form Pembuatan Dokumen</h6>
                            </div>
                        </div>
                        <div class="card-body px-4 pb-2">

                            {{-- Tombol refresh isi storage --}}
                            <form action="{{ route('storage.refresh') }}" method="POST" onsubmit="return confirm('Yakin ingin menyegarkan isi penyimpanan?')">
                                @csrf
                                <button type="submit" class="btn btn-danger">Refresh Isi Penyimpanan</button>
                            </form>
                            {{--  --}}

                            <form id="dokumenForm" action="{{ route('form.store') }}" method="POST">
                                @csrf

                                <div class="mb-3">
                                    <label for="nama_dokumen" class="form-label">Nama Dokumen</label>
                                    <input type="text" id="nama_dokumen" name="nama_dokumen"
                                           class="form-control"
                                           value="{{ old('nama_dokumen') }}">
                                </div>

                                <div class="mb-3">
                                    <label for="tahun" class="form-label">Tahun</label>
                                    <input type="number" id="tahun" name="tahun" class="form-control"
                                           value="{{ old('tahun', date('Y')) }}">
                                </div>

                                <div class="mb-3">
                                    <label for="periode_tipe" class="form-label">Tipe Periode</label>
                                    <select name="periode_tipe" id="periode_tipe" class="form-control">
                                        <option value="">-- Pilih Tipe --</option>
                                        <option value="bulanan" {{ old('periode_tipe') == 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                                        <option value="triwulanan" {{ old('periode_tipe') == 'triwulanan' ? 'selected' : '' }}>Triwulanan</option>
                                        <option value="tahunan" {{ old('periode_tipe') == 'tahunan' ? 'selected' : '' }}>Tahunan</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="pegawai_type" class="form-label">Pegawai Ditugaskan</label>
                                    <select name="pegawai_type" id="pegawai_type" class="form-control">
                                        <option value="">-- Pilih Opsi --</option>
                                        <option value="all" {{ old('pegawai_type') == 'all' ? 'selected' : '' }}>Seluruh Pegawai</option>
                                        <option value="specific" {{ old('pegawai_type', 'specific') == 'specific' ? 'selected' : '' }}>Pegawai Tertentu</option>
                                    </select>
                                </div>

                                <div class="mb-3" id="pegawai_checkbox_container">
                                    <label class="form-label">Pilih Pegawai</label>

                                    <div class="mb-2">
                                        <input type="text" id="searchPegawai" class="form-control"
                                            placeholder="Cari pegawai berdasarkan nama/email/NIP..."
                                            style="border: 2px solid #28a745; border-radius: 5px; padding: 8px;">
                                    </div>

                                    <div class="border p-2" style="max-height:300px; overflow-y:auto;">
                                        <table class="table table-bordered table-sm mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Pilih</th>
                                                    <th>Nama</th>
                                                    <th>Email</th>
                                                    <th>NIP</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($pegawaiList as $pegawai)
                                                    <tr>
                                                        <td class="text-center">
                                                            <input type="checkbox" name="pegawai_ids[]" value="{{ $pegawai->id }}"
                                                            @if(is_array(old('pegawai_ids')) && in_array($pegawai->id, old('pegawai_ids'))) checked @endif>
                                                        </td>
                                                        <td>{{ $pegawai->name }}</td>
                                                        <td>{{ $pegawai->email }}</td>
                                                        <td>{{ $pegawai->nip ?? '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <button type="submit" id="btnSubmit" class="btn btn-primary">Buat Dokumen</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preview Tabel Dokumen & Periode -->
            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-info shadow-info border-radius-lg pt-4 pb-3">
                                <h6 class="text-white text-capitalize ps-3">Dokumen yang Dimuat</h6>
                            </div>
                        </div>
                        <div class="card-body px-4 pb-2">
                            <div class="mb-3">
                                <input type="text" id="searchDokumen" class="form-control" 
                                    placeholder="Cari dokumen..." 
                                    style="border: 2px solid #3498db; border-radius: 5px; padding: 8px;">
                            </div>

                            <div id="dokumenTableContainer">
                                <table class="table table-bordered table-striped" id="dokumenTable">
                                    <thead>
                                        <tr>
                                            <th>No.</th>
                                            <th>Nama Dokumen</th>
                                            <th>Tipe Periode</th>
                                            <th>Tahun Terbaru</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $no = 1; @endphp
                                        @foreach($jenisDokumen->sortByDesc(fn($jd) => $jd->periode->max('tahun')) as $jd)
                                            @php $latestPeriode = $jd->periode->sortByDesc('tahun')->first(); @endphp
                                            <tr>
                                                <td>{{ $no++ }}</td>
                                                <td class="dokumen-nama">{{ $jd->nama_dokumen }}</td>
                                                <td>{{ $jd->periode_tipe }}</td>
                                                <td>{{ $latestPeriode?->tahun ?? '-' }}</td>
                                                <td>
                                                    <a href="javascript:void(0)" class="btn btn-sm btn-warning btn-edit" data-id="{{ $jd->id }}">Edit</a>
                                                    <form action="{{ route('jenis-dokumen.destroy') }}" method="POST" style="display:inline-block;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <input type="hidden" name="nama_dokumen" value="{{ $jd->nama_dokumen }}">
                                                        <input type="hidden" name="periode_tipe" value="{{ $jd->periode_tipe }}">
                                                        <input type="hidden" name="tahun" value="{{ $jd->periode->max('tahun') }}">
                                                        <button type="submit" class="btn btn-sm btn-danger btn-hapus">Hapus</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>
    <x-plugins></x-plugins>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const pegawaiType = document.getElementById('pegawai_type');
        const checkboxContainer = document.getElementById('pegawai_checkbox_container');
        const dokumenForm = document.getElementById('dokumenForm');
        const btnSubmit = document.getElementById('btnSubmit');
        const periodeTipe = document.getElementById('periode_tipe');
        const namaDokumen = document.getElementById('nama_dokumen');
        const searchPegawai = document.getElementById('searchPegawai');
        const pegawaiRows = document.querySelectorAll('#pegawai_checkbox_container tbody tr');

        searchPegawai.addEventListener('input', function () {
            const keyword = this.value.toLowerCase().trim();

            pegawaiRows.forEach(row => {
                const nama = row.children[1].textContent.toLowerCase();
                const email = row.children[2].textContent.toLowerCase();
                const nip = row.children[3].textContent.toLowerCase();

                if (nama.includes(keyword) || email.includes(keyword) || nip.includes(keyword)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Pencarian otomatis
        const searchInput = document.getElementById('searchDokumen');
        const table = document.getElementById('dokumenTable');
        const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

        searchInput.addEventListener('input', function() {
            const filter = this.value.toLowerCase();
            Array.from(rows).forEach(row => {
                const namaDokumen = row.querySelector('.dokumen-nama').textContent.toLowerCase();
                row.style.display = namaDokumen.includes(filter) ? '' : 'none';
            });
        });

        function togglePegawaiCheckbox() {
            checkboxContainer.style.display = 'block';
            if (pegawaiType.value === 'all') {
                checkboxContainer.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = true);
            } else if (pegawaiType.value === 'specific') {
                checkboxContainer.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
            }
        }
        pegawaiType.addEventListener('change', togglePegawaiCheckbox);
        window.addEventListener('DOMContentLoaded', togglePegawaiCheckbox);

        // Submit dengan SweetAlert + AJAX
        btnSubmit.addEventListener('click', function(e) {
            e.preventDefault();

            if(namaDokumen.value.trim() === '') return Swal.fire({icon:'warning', title:'Peringatan', text:'Nama Dokumen wajib diisi.'});
            if(periodeTipe.value === '') return Swal.fire({icon:'warning', title:'Peringatan', text:'Silakan pilih Tipe Periode terlebih dahulu.'});

            const isChecked = Array.from(checkboxContainer.querySelectorAll('input[type="checkbox"]')).some(cb => cb.checked);
            if(!isChecked) return Swal.fire({icon:'warning', title:'Peringatan', text:'Minimal pilih 1 pegawai.'});

            Swal.fire({
                title: 'Yakin membuat dokumen?',
                text: "Pastikan data sudah benar.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Buat!',
                cancelButtonText: 'Batal'
            }).then(result => {
                if(result.isConfirmed){
                    const formData = new FormData(dokumenForm);
                    fetch(dokumenForm.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if(data.status === 'success'){
                            Swal.fire({icon:'success', title:'Berhasil', text:data.message}).then(()=>location.reload());
                        } else {
                            Swal.fire({icon:'error', title:'Gagal', text:data.message});
                        }
                    })
                    .catch(err=>{
                        Swal.fire({icon:'error', title:'Gagal', text:'Terjadi kesalahan server.'});
                        console.error(err);
                    });
                }
            });
        });

        // Konfirmasi hapus dengan SweetAlert + AJAX
        document.querySelectorAll('.btn-hapus').forEach(button => {
            button.addEventListener('click', function(e){
                e.preventDefault();
                const form = this.closest('form');
                
                Swal.fire({
                    title: 'Yakin ingin hapus?',
                    text: 'Data tidak bisa dikembalikan!',
                    icon: 'warning',
                    showCancelButton:true,
                    confirmButtonText:'Ya, hapus!',
                    cancelButtonText:'Batal'
                }).then(result => {
                    if(result.isConfirmed){
                        const formData = new FormData(form);

                        fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if(data.status === 'success'){
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: data.message
                                }).then(() => {
                                    // Hapus row dari tabel
                                    form.closest('tr').remove();
                                });
                            } else {
                                Swal.fire({icon:'error', title:'Gagal', text:data.message});
                            }
                        })
                        .catch(err=>{
                            Swal.fire({icon:'error', title:'Gagal', text:'Terjadi kesalahan server.'});
                            console.error(err);
                        });
                    }
                });
            });
        });

        // Edit dokumen
        document.querySelectorAll('.btn-edit').forEach(button=>{
            button.addEventListener('click', function(){
                const id = this.getAttribute('data-id');
                Swal.fire({
                    title:'Apakah anda yakin ingin edit data ini?',
                    icon:'question',
                    showCancelButton:true,
                    confirmButtonText:'Ya, edit',
                    cancelButtonText:'Batal'
                }).then(result=>{
                    if(result.isConfirmed){
                        fetch(`/jenis-dokumen/${id}/json`)
                        .then(res=>res.json())
                        .then(data=>{
                            document.querySelector('.form-title').textContent = "Edit Dokumen";
                            dokumenForm.action = `/form/${data.id}`;

                            let hiddenMethod = dokumenForm.querySelector('input[name="_method"]');
                            if(!hiddenMethod){
                                hiddenMethod = document.createElement('input');
                                hiddenMethod.type='hidden';
                                hiddenMethod.name='_method';
                                dokumenForm.appendChild(hiddenMethod);
                            }
                            hiddenMethod.value='PUT';

                            namaDokumen.value = data.nama_dokumen;
                            document.getElementById('tahun').value = data.tahun;
                            periodeTipe.value = data.periode_tipe;
                            document.getElementById('tahun').readOnly = true;
                            periodeTipe.disabled = true;

                            document.querySelectorAll('input[name="pegawai_ids[]"]').forEach(cb=>{
                                cb.checked = data.pegawai_ids.includes(parseInt(cb.value));
                            });

                            btnSubmit.textContent = 'Simpan Perubahan';
                        })
                        .catch(err=>{
                            Swal.fire({icon:'error', title:'Gagal', text:'Tidak dapat memuat data dokumen.'});
                            console.error(err);
                        });
                    }
                });
            });
        });
    </script>
</x-layout>
