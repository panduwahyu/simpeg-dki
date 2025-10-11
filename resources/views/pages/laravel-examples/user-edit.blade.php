<x-layout bodyClass="g-sidenav-show bg-gray-200">
    <x-navbars.sidebar activePage="user-management"></x-navbars.sidebar>

    <div class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-navbars.navs.auth titlePage="Edit User"></x-navbars.navs.auth>

        <div class="container-fluid py-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="{{ route('user-management.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Name (readonly) --}}
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" value="{{ $user->name }}" disabled>
                            <input type="hidden" name="name" value="{{ $user->name }}">
                        </div>

                        {{-- Email (readonly) --}}
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="{{ $user->email }}" disabled>
                            <input type="hidden" name="email" value="{{ $user->email }}">
                        </div>

                        {{-- Role --}}
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="role" class="form-control">
                                @php $roles = ['Pegawai','Supervisor','Admin']; @endphp
                                @foreach($roles as $role)
                                    <option value="{{ $role }}" {{ old('role', $user->role) == $role ? 'selected' : '' }}>
                                        {{ $role }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role')<p class="text-danger">{{ $message }}</p>@enderror
                        </div>

                        {{-- NIP --}}
                        <div class="mb-3">
                            <label class="form-label">NIP</label>
                            <input type="text" name="nip" class="form-control" value="{{ old('nip', $user->nip) }}">
                            @error('nip')<p class="text-danger">{{ $message }}</p>@enderror
                        </div>

                        {{-- Unit Kerja --}}
                        <div class="mb-3">
                            <label class="form-label">Unit Kerja</label>
                            <input type="text" name="unit_kerja" class="form-control" value="{{ old('unit_kerja', $user->unit_kerja) }}">
                            @error('unit_kerja')<p class="text-danger">{{ $message }}</p>@enderror
                        </div>

                        {{-- Jabatan --}}
                        <div class="mb-3">
                            <label class="form-label">Jabatan</label>
                            <input type="text" name="jabatan" class="form-control" value="{{ old('jabatan', $user->jabatan) }}">
                            @error('jabatan')<p class="text-danger">{{ $message }}</p>@enderror
                        </div>

                        {{-- Pangkat --}}
                        <div class="mb-3">
                            <label class="form-label">Pangkat</label>
                            <select name="pangkat" class="form-control">
                                <option value="">-- Pilih Pangkat --</option>
                                @php
                                    $pangkats = [
                                        'Juru Muda','Juru Muda Tingkat I','Juru','Juru Tingkat I',
                                        'Pengatur Muda','Pengatur Muda Tingkat I','Pengatur','Pengatur Tingkat I',
                                        'Penata Muda','Penata Muda Tingkat I','Penata','Penata Tingkat I',
                                        'Pembina','Pembina Tingkat I','Pembina Utama Muda','Pembina Utama Madya','Pembina Utama'
                                    ];
                                @endphp
                                @foreach($pangkats as $p)
                                    <option value="{{ $p }}" {{ old('pangkat', $user->pangkat) == $p ? 'selected' : '' }}>{{ $p }}</option>
                                @endforeach
                            </select>
                            @error('pangkat')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>

                        {{-- Golongan --}}
                        <div class="mb-3">
                            <label class="form-label">Golongan</label>
                            <select name="golongan" class="form-control">
                                <option value="">-- Pilih Golongan --</option>
                                @php
                                    $golongans = [
                                        'I/A','I/B','I/C','I/D',
                                        'II/A','II/B','II/C','II/D',
                                        'III/A','III/B','III/C','III/D',
                                        'IV/A','IV/B','IV/C','IV/D','IV/E',
                                        'I','II','III','IV','V','VI','VII','VIII',
                                        'IX','X','XI','XII','XIII','XIV','XV','XVI','XVII'
                                    ];
                                @endphp
                                @foreach($golongans as $gol)
                                    <option value="{{ $gol }}" {{ old('golongan', $user->golongan) == $gol ? 'selected' : '' }}>{{ $gol }}</option>
                                @endforeach
                            </select>
                            @error('golongan')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>

                        {{-- Tombol aksi --}}
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Update User</button>
                            <a href="{{ route('user-management') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <x-plugins></x-plugins>
</x-layout>
