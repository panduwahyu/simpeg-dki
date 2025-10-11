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

                        {{-- Name --}}
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                            @error('name')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>

                        {{-- Email (readonly) --}}
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="{{ $user->email }}" disabled>
                            <input type="hidden" name="email" value="{{ $user->email }}">
                        </div>

                        {{-- NIP BPS --}}
                        <div class="mb-3">
                            <label class="form-label">NIP BPS</label>
                            <input type="text" name="nip_bps" class="form-control" value="{{ old('nip_bps', $user->nip_bps) }}">
                            @error('nip_bps')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>

                        {{-- NIP --}}
                        <div class="mb-3">
                            <label class="form-label">NIP</label>
                            <input type="text" name="nip" class="form-control" value="{{ old('nip', $user->nip) }}">
                            @error('nip')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>

                        {{-- Wilayah --}}
                        <div class="mb-3">
                            <label class="form-label">Wilayah</label>
                            <input type="text" name="wilayah" class="form-control" value="{{ old('wilayah', $user->wilayah) }}">
                            @error('wilayah')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>

                        {{-- Unit Kerja --}}
                        <div class="mb-3">
                            <label class="form-label">Unit Kerja</label>
                            <input type="text" name="unit_kerja" class="form-control" value="{{ old('unit_kerja', $user->unit_kerja) }}">
                            @error('unit_kerja')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>

                        {{-- Jabatan --}}
                        <div class="mb-3">
                            <label class="form-label">Jabatan</label>
                            <input type="text" name="jabatan" class="form-control" value="{{ old('jabatan', $user->jabatan) }}">
                            @error('jabatan')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>

                        {{-- Role / Status --}}
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            @php $roles = ['Pegawai','Supervisor','Admin']; @endphp
                            <select name="role" class="form-control">
                                @foreach($roles as $role)
                                    <option value="{{ $role }}" {{ old('role', $user->role) == $role ? 'selected' : '' }}>
                                        {{ $role }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>

                        {{-- Pangkat --}}
                        <div class="mb-3">
                            <label class="form-label">Pangkat</label>
                            <select name="pangkat" id="pangkat" class="form-control">
                                <option value="">-- Pilih Pangkat --</option>
                                @php
                                    $pangkats = [
                                        'Juru Muda','Juru Muda Tingkat I','Juru','Juru Tingkat I',
                                        'Pengatur Muda','Pengatur Muda Tingkat I','Pengatur','Pengatur Tingkat I',
                                        'Penata Muda','Penata Muda Tingkat I','Penata','Penata Tingkat I',
                                        'Pembina','Pembina Tingkat I','Pembina Utama Muda','Pembina Utama Madya','Pembina Utama',
                                        'Pemula','Terampil','Mahir','Penyelia','Ahli Pertama','Ahli Muda','Ahli Madya','Ahli Utama',
                                        'Fungsional Tingkat Lanjut I','Fungsional Tingkat Lanjut II','Fungsional Tingkat Lanjut III',
                                        'Koordinator','Pengawas','Pejabat Fungsional Utama','Pejabat Pimpinan Tinggi Pratama',
                                        'Pejabat Pimpinan Tinggi Madya','Pejabat Pimpinan Tinggi Utama'
                                    ];
                                @endphp
                                @foreach($pangkats as $p)
                                    <option value="{{ $p }}" {{ old('pangkat', $user->pangkat) == $p ? 'selected' : '' }}>
                                        {{ $p }}
                                    </option>
                                @endforeach
                            </select>
                            @error('pangkat')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>

                        {{-- Golongan --}}
                        <div class="mb-3">
                            <label class="form-label">Golongan</label>
                            <select name="golongan" id="golongan" class="form-control">
                                <option value="">-- Pilih Golongan --</option>
                                @php
                                    $golongans = [
                                        'I/A','I/B','I/C','I/D','II/A','II/B','II/C','II/D',
                                        'III/A','III/B','III/C','III/D','IV/A','IV/B','IV/C','IV/D','IV/E',
                                        'I','II','III','IV','V','VI','VII','VIII',
                                        'IX','X','XI','XII','XIII','XIV','XV','XVI','XVII'
                                    ];
                                @endphp
                                @foreach($golongans as $gol)
                                    <option value="{{ $gol }}" {{ old('golongan', $user->golongan) == $gol ? 'selected' : '' }}>
                                        {{ $gol }}
                                    </option>
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

    {{-- SCRIPT PANGKAT ↔ GOLONGAN OTOMATIS --}}
    <script>
        const mapping = {
            "I/A": "Juru Muda","I/B": "Juru Muda Tingkat I","I/C": "Juru","I/D": "Juru Tingkat I",
            "II/A": "Pengatur Muda","II/B": "Pengatur Muda Tingkat I","II/C": "Pengatur","II/D": "Pengatur Tingkat I",
            "III/A": "Penata Muda","III/B": "Penata Muda Tingkat I","III/C": "Penata","III/D": "Penata Tingkat I",
            "IV/A": "Pembina","IV/B": "Pembina Tingkat I","IV/C": "Pembina Utama Muda","IV/D": "Pembina Utama Madya","IV/E": "Pembina Utama",
            "I": "Pemula","II": "Terampil","III": "Mahir","IV": "Penyelia","V": "Ahli Pertama","VI": "Ahli Muda","VII": "Ahli Madya","VIII": "Ahli Utama",
            "IX": "Fungsional Tingkat Lanjut I","X": "Fungsional Tingkat Lanjut II","XI": "Fungsional Tingkat Lanjut III",
            "XII": "Koordinator","XIII": "Pengawas","XIV": "Pejabat Fungsional Utama","XV": "Pejabat Pimpinan Tinggi Pratama",
            "XVI": "Pejabat Pimpinan Tinggi Madya","XVII": "Pejabat Pimpinan Tinggi Utama"
        };

        const golonganSelect = document.getElementById('golongan');
        const pangkatSelect = document.getElementById('pangkat');

        // Sync saat user memilih salah satu
        golonganSelect.addEventListener('change', () => {
            pangkatSelect.value = mapping[golonganSelect.value] || "";
        });

        pangkatSelect.addEventListener('change', () => {
            const found = Object.entries(mapping).find(([gol, pang]) => pang === pangkatSelect.value);
            golonganSelect.value = found ? found[0] : "";
        });

        // Otomatis set dropdown saat load halaman
        window.addEventListener('DOMContentLoaded', () => {
            const currentGol = "{{ old('golongan', $user->golongan) }}";
            if (currentGol && mapping[currentGol]) {
                pangkatSelect.value = mapping[currentGol];
            } else {
                const currentPangkat = "{{ old('pangkat', $user->pangkat) }}";
                if (currentPangkat) {
                    const found = Object.entries(mapping).find(([gol, pang]) => pang === currentPangkat);
                    golonganSelect.value = found ? found[0] : "";
                }
            }
        });
    </script>

    <x-plugins></x-plugins>
</x-layout>
