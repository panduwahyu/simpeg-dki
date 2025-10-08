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
                            {{-- tampilkan error validasi --}}
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

                                <div class="mb-3">
                                    <label class="form-label">Nama</label>
                                    <input type="text" name="name" class="form-control"
                                           value="{{ old('name') }}" required>
                                    @error('name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control"
                                           value="{{ old('email') }}" required>
                                    @error('email')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="role" class="form-control" required>
                                        <option value="Pegawai" {{ old('role') == 'Pegawai' ? 'selected' : '' }}>Pegawai</option>
                                        <option value="Supervisor" {{ old('role') == 'Supervisor' ? 'selected' : '' }}>Supervisor</option>
                                        <option value="Admin" {{ old('role') == 'Admin' ? 'selected' : '' }}>Admin</option>
                                    </select>
                                    @error('role')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Tambahan field baru --}}
                                <div class="mb-3">
                                    <label class="form-label">NIP</label>
                                    <input type="text" name="nip" class="form-control" value="{{ old('nip') }}">
                                    @error('nip')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Unit Kerja</label>
                                    <input type="text" name="unit_kerja" class="form-control" value="{{ old('unit_kerja') }}">
                                    @error('unit_kerja')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Jabatan</label>
                                    <input type="text" name="jabatan" class="form-control" value="{{ old('jabatan') }}">
                                    @error('jabatan')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- dropdown pangkat --}}
                                <div class="mb-3">
                                    <label class="form-label">Pangkat</label>
                                    <select name="pangkat" class="form-control">
                                        <option value="">-- Pilih Pangkat --</option>
                                        <option value="Juru Muda" {{ old('pangkat') == 'Juru Muda' ? 'selected' : '' }}>Juru Muda</option>
                                        <option value="Juru Muda Tingkat I" {{ old('pangkat') == 'Juru Muda Tingkat I' ? 'selected' : '' }}>Juru Muda Tingkat I</option>
                                        <option value="Juru" {{ old('pangkat') == 'Juru' ? 'selected' : '' }}>Juru</option>
                                        <option value="Juru Tingkat I" {{ old('pangkat') == 'Juru Tingkat I' ? 'selected' : '' }}>Juru Tingkat I</option>
                                        <option value="Pengatur Muda" {{ old('pangkat') == 'Pengatur Muda' ? 'selected' : '' }}>Pengatur Muda</option>
                                        <option value="Pengatur Muda Tingkat I" {{ old('pangkat') == 'Pengatur Muda Tingkat I' ? 'selected' : '' }}>Pengatur Muda Tingkat I</option>
                                        <option value="Pengatur" {{ old('pangkat') == 'Pengatur' ? 'selected' : '' }}>Pengatur</option>
                                        <option value="Pengatur Tingkat I" {{ old('pangkat') == 'Pengatur Tingkat I' ? 'selected' : '' }}>Pengatur Tingkat I</option>
                                        <option value="Penata Muda" {{ old('pangkat') == 'Penata Muda' ? 'selected' : '' }}>Penata Muda</option>
                                        <option value="Penata Muda Tingkat I" {{ old('pangkat') == 'Penata Muda Tingkat I' ? 'selected' : '' }}>Penata Muda Tingkat I</option>
                                        <option value="Penata" {{ old('pangkat') == 'Penata' ? 'selected' : '' }}>Penata</option>
                                        <option value="Penata Tingkat I" {{ old('pangkat') == 'Penata Tingkat I' ? 'selected' : '' }}>Penata Tingkat I</option>
                                        <option value="Pembina" {{ old('pangkat') == 'Pembina' ? 'selected' : '' }}>Pembina</option>
                                        <option value="Pembina Tingkat I" {{ old('pangkat') == 'Pembina Tingkat I' ? 'selected' : '' }}>Pembina Tingkat I</option>
                                        <option value="Pembina Utama Muda" {{ old('pangkat') == 'Pembina Utama Muda' ? 'selected' : '' }}>Pembina Utama Muda</option>
                                        <option value="Pembina Utama Madya" {{ old('pangkat') == 'Pembina Utama Madya' ? 'selected' : '' }}>Pembina Utama Madya</option>
                                        <option value="Pembina Utama" {{ old('pangkat') == 'Pembina Utama' ? 'selected' : '' }}>Pembina Utama</option>
                                    </select>
                                    @error('pangkat') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                {{-- dropdown golongan --}}
                                <div class="mb-3">
                                    <label class="form-label">Golongan</label>
                                    <select name="golongan" class="form-control">
                                        <option value="">-- Pilih Golongan --</option>
                                        <option value="I/a" {{ old('golongan') == 'I/a' ? 'selected' : '' }}>I/a</option>
                                        <option value="I/b" {{ old('golongan') == 'I/b' ? 'selected' : '' }}>I/b</option>
                                        <option value="I/c" {{ old('golongan') == 'I/c' ? 'selected' : '' }}>I/c</option>
                                        <option value="I/d" {{ old('golongan') == 'I/d' ? 'selected' : '' }}>I/d</option>
                                        <option value="II/a" {{ old('golongan') == 'II/a' ? 'selected' : '' }}>II/a</option>
                                        <option value="II/b" {{ old('golongan') == 'II/b' ? 'selected' : '' }}>II/b</option>
                                        <option value="II/c" {{ old('golongan') == 'II/c' ? 'selected' : '' }}>II/c</option>
                                        <option value="II/d" {{ old('golongan') == 'II/d' ? 'selected' : '' }}>II/d</option>
                                        <option value="III/a" {{ old('golongan') == 'III/a' ? 'selected' : '' }}>III/a</option>
                                        <option value="III/b" {{ old('golongan') == 'III/b' ? 'selected' : '' }}>III/b</option>
                                        <option value="III/c" {{ old('golongan') == 'III/c' ? 'selected' : '' }}>III/c</option>
                                        <option value="III/d" {{ old('golongan') == 'III/d' ? 'selected' : '' }}>III/d</option>
                                        <option value="IV/a" {{ old('golongan') == 'IV/a' ? 'selected' : '' }}>IV/a</option>
                                        <option value="IV/b" {{ old('golongan') == 'IV/b' ? 'selected' : '' }}>IV/b</option>
                                        <option value="IV/c" {{ old('golongan') == 'IV/c' ? 'selected' : '' }}>IV/c</option>
                                        <option value="IV/d" {{ old('golongan') == 'IV/d' ? 'selected' : '' }}>IV/d</option>
                                        <option value="IV/e" {{ old('golongan') == 'IV/e' ? 'selected' : '' }}>IV/e</option>
                                    </select>
                                    @error('golongan') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                {{-- End tambahan field --}}

                                <button type="submit" class="btn bg-gradient-primary">Simpan</button>
                                <a href="{{ route('user-management') }}" class="btn btn-secondary">Batal</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <x-plugins></x-plugins>
</x-layout>
