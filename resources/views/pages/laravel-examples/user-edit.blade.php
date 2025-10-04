<x-layout bodyClass="g-sidenav-show bg-gray-200">
    <x-navbars.sidebar activePage="user-management"></x-navbars.sidebar>

    <div class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-navbars.navs.auth titlePage="Edit User"></x-navbars.navs.auth>

        <div class="container-fluid py-4">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('user-management.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" value="{{ $user->name }}" disabled>
                            <input type="hidden" name="name" value="{{ $user->name }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="{{ $user->email }}" disabled>
                            <input type="hidden" name="email" value="{{ $user->email }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="role" class="form-control">
                                <option value="Pegawai" {{ old('role', $user->role) == 'Pegawai' ? 'selected' : '' }}>Pegawai</option>
                                <option value="Supervisor" {{ old('role', $user->role) == 'Supervisor' ? 'selected' : '' }}>Supervisor</option>
                                <option value="Admin" {{ old('role', $user->role) == 'Admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                            @error('role')<p class="text-danger">{{ $message }}</p>@enderror
                        </div>

                        {{-- Tambahan field baru --}}
                        <div class="mb-3">
                            <label class="form-label">NIP</label>
                            <input type="text" name="nip" class="form-control" value="{{ old('nip', $user->nip) }}">
                            @error('nip')<p class="text-danger">{{ $message }}</p>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Unit Kerja</label>
                            <input type="text" name="unit_kerja" class="form-control" value="{{ old('unit_kerja', $user->unit_kerja) }}">
                            @error('unit_kerja')<p class="text-danger">{{ $message }}</p>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Jabatan</label>
                            <input type="text" name="jabatan" class="form-control" value="{{ old('jabatan', $user->jabatan) }}">
                            @error('jabatan')<p class="text-danger">{{ $message }}</p>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Pangkat</label>
                            <select name="pangkat" class="form-control">
                                <option value="">-- Pilih Pangkat --</option>
                                @foreach([
                                  'Juru Muda','Juru Muda Tingkat I','Juru','Juru Tingkat I',
                                  'Pengatur Muda','Pengatur Muda Tingkat I','Pengatur','Pengatur Tingkat I',
                                  'Penata Muda','Penata Muda Tingkat I','Penata','Penata Tingkat I',
                                  'Pembina','Pembina Tingkat I','Pembina Utama Muda','Pembina Utama Madya','Pembina Utama'
                                ] as $pangkat)
                                    <option value="{{ $pangkat }}" {{ old('pangkat', $user->pangkat) == $pangkat ? 'selected' : '' }}>{{ $pangkat }}</option>
                                @endforeach
                            </select>
                            @error('pangkat')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Golongan</label>
                            <select name="golongan" class="form-control">
                                <option value="">-- Pilih Golongan --</option>
                                @foreach(['I/a','I/b','I/c','I/d','II/a','II/b','II/c','II/d','III/a','III/b','III/c','III/d','IV/a','IV/b','IV/c','IV/d','IV/e'] as $gol)
                                    <option value="{{ $gol }}" {{ old('golongan', $user->golongan) == $gol ? 'selected' : '' }}>{{ $gol }}</option>
                                @endforeach
                            </select>
                            @error('golongan')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>

                        {{-- Tombol --}}
                        <button type="submit" class="btn btn-primary">Update User</button>
                        <a href="{{ route('user-management') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <x-plugins></x-plugins>
</x-layout>
