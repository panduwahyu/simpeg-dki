@foreach ($users as $user)
<tr>
    <td>
        <div class="d-flex px-2 py-1">
            <div class="d-flex flex-column justify-content-center">
                <p class="mb-0 text-sm">{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</p>
            </div>
        </div>
    </td>
    <td>
        @php
            $photo = $user->photo 
                ? (Str::startsWith($user->photo, ['http://','https://']) 
                    ? $user->photo 
                    : asset('storage/' . $user->photo)) 
                : asset('assets/img/bruce-mars.jpg');
        @endphp
        <img src="{{ $photo }}" class="avatar avatar-sm me-3 border-radius-lg" alt="{{ $user->name }}">
    </td>
    <td>
        <div class="d-flex flex-column justify-content-center">
            <h6 class="mb-0 text-sm">{{ $user->name }}</h6>
        </div>
    </td>
    <td class="align-middle text-center text-sm">
        <p class="text-xs text-secondary mb-0">{{ $user->email }}</p>
    </td>
    <td class="align-middle text-center">
        <span class="text-secondary text-xs font-weight-bold">{{ $user->role }}</span>
    </td>
    <td class="align-middle text-center">
        <span class="text-secondary text-xs font-weight-bold">{{ optional($user->created_at)->format('d/m/Y') }}</span>
    </td>
    <td class="align-middle">
        <a href="{{ route('user-management.edit', $user->id) }}" class="btn btn-success btn-link">
            <i class="material-icons">edit</i>
        </a>
        <button type="button" class="btn btn-danger btn-link" onclick="deleteUser({{ $user->id }})">
            <i class="material-icons">close</i>
        </button>
        <form id="delete-form-{{ $user->id }}" action="{{ route('user-management.destroy', $user->id) }}" method="POST" style="display:none;">
            @csrf
            @method('DELETE')
        </form>
    </td>
</tr>
@endforeach