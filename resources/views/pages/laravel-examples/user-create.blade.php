<x-layout bodyClass="g-sidenav-show bg-gray-200">
    <x-navbars.sidebar activePage="user-management"></x-navbars.sidebar>

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <x-navbars.navs.auth titlePage="User Baru"></x-navbars.navs.auth>

        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                                <h6 class="text-white mx-3">Tambah User Baru</h6>
                            </div>
                        </div>

                        <div class="card-body px-4 pb-2">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form action="{{ route('user-management.store') }}" method="POST">
                                @csrf

                                {{-- Nama --}}
                                <div class="mb-3">
                                    <label class="form-label">Nama</label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                                </div>

                                {{-- Email --}}
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                                </div>

                                {{-- NIP BPS --}}
                                <div class="mb-3">
                                    <label class="form-label">NIP BPS</label>
                                    <input type="text" name="nip_bps" class="form-control" value="{{ old('nip_bps') }}">
                                </div>

                                {{-- NIP --}}
                                <div class="mb-3">
                                    <label class="form-label">NIP</label>
                                    <input type="text" name="nip" class="form-control" value="{{ old('nip') }}">
                                </div>

                                {{-- Role --}}
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="role" class="form-control" required>
                                        <option value="Pegawai">Pegawai</option>
                                        <option value="Supervisor">Supervisor</option>
                                        <option value="Admin">Admin</option>
                                    </select>
                                </div>

                                {{-- Wilayah --}}
                                <div class="mb-3">
                                    <label class="form-label">Wilayah</label>
                                    <input type="text" name="wilayah" class="form-control" value="{{ old('wilayah') }}">
                                </div>

                                {{-- Unit Kerja --}}
                                <div class="mb-3">
                                    <label class="form-label">Unit Kerja</label>
                                    <input type="text" name="unit_kerja" class="form-control" value="{{ old('unit_kerja') }}">
                                </div>

                                {{-- Jabatan --}}
                                <div class="mb-3">
                                    <label class="form-label">Jabatan</label>
                                    <input type="text" name="jabatan" class="form-control" value="{{ old('jabatan') }}">
                                </div>

                                {{-- Pangkat --}}
                                <div class="mb-3">
                                    <label class="form-label">Pangkat</label>
                                    <select name="pangkat" id="pangkat" class="form-control">
                                        <option value="">-- Pilih Pangkat --</option>
                                        {{-- PNS Klasik --}}
                                        <option value="Juru Muda">Juru Muda</option>
                                        <option value="Juru Muda Tingkat I">Juru Muda Tingkat I</option>
                                        <option value="Juru">Juru</option>
                                        <option value="Juru Tingkat I">Juru Tingkat I</option>
                                        <option value="Pengatur Muda">Pengatur Muda</option>
                                        <option value="Pengatur Muda Tingkat I">Pengatur Muda Tingkat I</option>
                                        <option value="Pengatur">Pengatur</option>
                                        <option value="Pengatur Tingkat I">Pengatur Tingkat I</option>
                                        <option value="Penata Muda">Penata Muda</option>
                                        <option value="Penata Muda Tingkat I">Penata Muda Tingkat I</option>
                                        <option value="Penata">Penata</option>
                                        <option value="Penata Tingkat I">Penata Tingkat I</option>
                                        <option value="Pembina">Pembina</option>
                                        <option value="Pembina Tingkat I">Pembina Tingkat I</option>
                                        <option value="Pembina Utama Muda">Pembina Utama Muda</option>
                                        <option value="Pembina Utama Madya">Pembina Utama Madya</option>
                                        <option value="Pembina Utama">Pembina Utama</option>
                                        {{-- PPPK / ASN Modern --}}
                                        <option value="Pemula">Pemula</option>
                                        <option value="Terampil">Terampil</option>
                                        <option value="Mahir">Mahir</option>
                                        <option value="Penyelia">Penyelia</option>
                                        <option value="Ahli Pertama">Ahli Pertama</option>
                                        <option value="Ahli Muda">Ahli Muda</option>
                                        <option value="Ahli Madya">Ahli Madya</option>
                                        <option value="Ahli Utama">Ahli Utama</option>
                                        <option value="Koordinator">Koordinator</option>
                                        <option value="Pengawas">Pengawas</option>
                                        <option value="Pejabat Fungsional Utama">Pejabat Fungsional Utama</option>
                                        <option value="Pejabat Pimpinan Tinggi Pratama">Pejabat Pimpinan Tinggi Pratama</option>
                                        <option value="Pejabat Pimpinan Tinggi Madya">Pejabat Pimpinan Tinggi Madya</option>
                                        <option value="Pejabat Pimpinan Tinggi Utama">Pejabat Pimpinan Tinggi Utama</option>
                                    </select>
                                </div>

                                {{-- Golongan --}}
                                <div class="mb-3">
                                    <label class="form-label">Golongan</label>
                                    <select name="golongan" id="golongan" class="form-control">
                                        <option value="">-- Pilih Golongan --</option>
                                        {{-- PNS Klasik --}}
                                        <option value="I/A">I/A</option>
                                        <option value="I/B">I/B</option>
                                        <option value="I/C">I/C</option>
                                        <option value="I/D">I/D</option>
                                        <option value="II/A">II/A</option>
                                        <option value="II/B">II/B</option>
                                        <option value="II/C">II/C</option>
                                        <option value="II/D">II/D</option>
                                        <option value="III/A">III/A</option>
                                        <option value="III/B">III/B</option>
                                        <option value="III/C">III/C</option>
                                        <option value="III/D">III/D</option>
                                        <option value="IV/A">IV/A</option>
                                        <option value="IV/B">IV/B</option>
                                        <option value="IV/C">IV/C</option>
                                        <option value="IV/D">IV/D</option>
                                        <option value="IV/E">IV/E</option>
                                        {{-- PPPK / ASN Modern --}}
                                        <option value="I">I</option>
                                        <option value="II">II</option>
                                        <option value="III">III</option>
                                        <option value="IV">IV</option>
                                        <option value="V">V</option>
                                        <option value="VI">VI</option>
                                        <option value="VII">VII</option>
                                        <option value="VIII">VIII</option>
                                        <option value="IX">IX</option>
                                        <option value="X">X</option>
                                        <option value="XI">XI</option>
                                        <option value="XII">XII</option>
                                        <option value="XIII">XIII</option>
                                        <option value="XIV">XIV</option>
                                        <option value="XV">XV</option>
                                        <option value="XVI">XVI</option>
                                        <option value="XVII">XVII</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn bg-gradient-primary">Simpan</button>
                                <a href="{{ route('user-management') }}" class="btn btn-secondary">Batal</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SCRIPT PANGKAT ↔ GOLONGAN OTOMATIS --}}
        <script>
            const mapping = {
                // PNS Klasik
                "I/A": "Juru Muda",
                "I/B": "Juru Muda Tingkat I",
                "I/C": "Juru",
                "I/D": "Juru Tingkat I",
                "II/A": "Pengatur Muda",
                "II/B": "Pengatur Muda Tingkat I",
                "II/C": "Pengatur",
                "II/D": "Pengatur Tingkat I",
                "III/A": "Penata Muda",
                "III/B": "Penata Muda Tingkat I",
                "III/C": "Penata",
                "III/D": "Penata Tingkat I",
                "IV/A": "Pembina",
                "IV/B": "Pembina Tingkat I",
                "IV/C": "Pembina Utama Muda",
                "IV/D": "Pembina Utama Madya",
                "IV/E": "Pembina Utama",

                // PPPK / ASN Modern
                "I": "Pemula",
                "II": "Terampil",
                "III": "Mahir",
                "IV": "Penyelia",
                "V": "Ahli Pertama",
                "VI": "Ahli Muda",
                "VII": "Ahli Madya",
                "VIII": "Ahli Utama",
                "IX": "Fungsional Tingkat Lanjut I",
                "X": "Fungsional Tingkat Lanjut II",
                "XI": "Fungsional Tingkat Lanjut III",
                "XII": "Koordinator",
                "XIII": "Pengawas",
                "XIV": "Pejabat Fungsional Utama",
                "XV": "Pejabat Pimpinan Tinggi Pratama",
                "XVI": "Pejabat Pimpinan Tinggi Madya",
                "XVII": "Pejabat Pimpinan Tinggi Utama"
            };

            const golonganSelect = document.getElementById('golongan');
            const pangkatSelect = document.getElementById('pangkat');

            golonganSelect.addEventListener('change', function () {
                const selectedGol = this.value;
                pangkatSelect.value = mapping[selectedGol] || "";
            });

            pangkatSelect.addEventListener('change', function () {
                const selectedPangkat = this.value;
                const found = Object.entries(mapping).find(([gol, pangkat]) => pangkat === selectedPangkat);
                golonganSelect.value = found ? found[0] : "";
            });
        </script>
    </main>

    <x-plugins></x-plugins>
</x-layout>
