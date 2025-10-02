<x-layout bodyClass="g-sidenav-show bg-gray-200">
    <x-navbars.sidebar activePage="form"></x-navbars.sidebar>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <!-- Navbar -->
        <x-navbars.navs.auth titlePage="Form Periode"></x-navbars.navs.auth>
        <!-- End Navbar -->

        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                                <h6 class="text-white text-capitalize ps-3">Form Pembuatan Periode</h6>
                            </div>
                        </div>
                        <div class="card-body px-4 pb-2">

                            @if(session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif

                            <form action="{{ route('form.store') }}" method="POST">
                                @csrf

                                <div class="mb-3">
                                    <label for="jenis_dokumen_id" class="form-label">Jenis Dokumen</label>
                                    <select name="jenis_dokumen_id" id="jenis_dokumen_id" class="form-control" required>
                                        <option value="">-- Pilih Jenis Dokumen --</option>
                                        @foreach($jenisDokumen as $jd)
                                            <option value="{{ $jd->id }}">{{ $jd->nama_dokumen }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="tahun" class="form-label">Tahun</label>
                                    <input type="number" id="tahun" name="tahun" class="form-control"
                                           value="{{ date('Y') }}" required>
                                </div>

                                <div class="mb-3">
                                    <label for="periode_tipe" class="form-label">Tipe Periode</label>
                                    <select name="periode_tipe" id="periode_tipe" class="form-control" required>
                                        <option value="">-- Pilih Tipe --</option>
                                        <option value="bulanan">Bulanan</option>
                                        <option value="triwulanan">Triwulanan</option>
                                        <option value="tahunan">Tahunan</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary">Buat Periode</button>
                            </form>

                        </div>
                    </div>
                </div>
            </div>

            <x-footers.auth></x-footers.auth>
        </div>
    </main>
    <x-plugins></x-plugins>
</x-layout>
