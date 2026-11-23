@extends('layouts.main')
@section('title', 'Pengguna & Role')
@section('subtitle', 'Kelola akun dan hak akses sistem')
@section('actions')<a href="{{ route('user.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah Pengguna</a>@endsection
@section('content')
<div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
    <thead><tr><th>Nama</th><th>Email</th><th>Role</th><th></th></tr></thead>
    <tbody>
    @foreach($data as $u)
        <tr><td>{{ $u->name }}</td><td>{{ $u->email }}</td>
            <td><span class="badge text-bg-primary">{{ $u->role->label ?? '-' }}</span></td>
            <td class="text-end">
                <a href="{{ route('user.edit', $u) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
                <form action="{{ route('user.destroy', $u) }}" method="POST" class="d-inline" data-confirm="Hapus pengguna ini?">
                    @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
            </td></tr>
    @endforeach
    </tbody>
</table></div></div>
<div class="mt-3">{{ $data->links() }}</div>
@endsection