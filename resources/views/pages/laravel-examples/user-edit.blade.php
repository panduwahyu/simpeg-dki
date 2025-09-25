<x-layout bodyClass="g-sidenav-show bg-gray-200">
    <x-navbars.sidebar activePage="user-management"></x-navbars.sidebar>
    <div class="main-content position-relative max-height-vh-100 h-100">
        <x-navbars.navs.auth titlePage="Edit User"></x-navbars.navs.auth>
        <div class="container-fluid py-4">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('user-management.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}">
                            @error('name') <p class="text-danger">{{ $message }}</p> @enderror
                        </div>

                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}">
                            @error('email') <p class="text-danger">{{ $message }}</p> @enderror
                        </div>

                        <div class="mb-3">
                            <label>Password (kosongkan jika tidak diubah)</label>
                            <input type="password" name="password" class="form-control">
                            @error('password') <p class="text-danger">{{ $message }}</p> @enderror
                        </div>

                        <div class="mb-3">
                            <label>Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label>Role</label>
                            <select name="role" class="form-control">
                                <option value="Pegawai" {{ old('role', $user->role ?? 'Pegawai') == 'Pegawai' ? 'selected' : '' }}>Pegawai</option>
                                <option value="Supervisor" {{ old('role', $user->role ?? 'Pegawai') == 'Supervisor' ? 'selected' : '' }}>Supervisor</option>
                                <option value="Admin" {{ old('role', $user->role ?? 'Pegawai') == 'Admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                            @error('role') <p class="text-danger">{{ $message }}</p> @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Update User</button>
                        <a href="{{ route('user-management') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <x-plugins></x-plugins>
</x-layout>
