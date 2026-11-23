<?php
namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private function rules(?User $user = null): array
    {
        return [
            'name'    => 'required|string|min:3|max:100',
            'email'   => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($user?->id)],
            'role_id' => 'required|exists:roles,id',
            'password'=> [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function index()
    {
        return view('user.index', ['data' => User::with('role')->orderBy('name')->paginate(10)]);
    }

    public function create() { return view('user.create', ['roles' => Role::pluck('label', 'id')]); }

    public function store(Request $request)
    {
        $request->merge(['email' => strtolower(trim($request->email))]);   // samakan huruf kecil

        $v = $request->validate([
            // ...kolom lain tetap...
            'email' => ['required', 'email', 'max:100',
                        'regex:/^[A-Za-z0-9._%+-]+@gmail\.com$/i',
                        'unique:users,email'],
        ], [
            'email.regex'  => 'Email harus menggunakan domain @gmail.com.',
            'email.unique' => 'Email ini sudah dipakai akun lain.',
        ]);
        User::create($v);                       // password otomatis di-hash (cast 'hashed')
        return redirect()->route('user.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        return view('user.edit', ['user' => $user, 'roles' => Role::pluck('label', 'id')]);
    }

    public function update(Request $request, User $user)
    {
        $request->merge(['email' => strtolower(trim($request->email))]);

        $v = $request->validate([
            // ...kolom lain tetap...
            'email' => ['required', 'email', 'max:100',
                        'regex:/^[A-Za-z0-9._%+-]+@gmail\.com$/i',
                        Rule::unique('users', 'email')->ignore($user->id)],
        ], [
            'email.regex'  => 'Email harus menggunakan domain @gmail.com.',
            'email.unique' => 'Email ini sudah dipakai akun lain.',
        ]);
        if (empty($v['password'])) unset($v['password']);   // kosong = password tidak diubah
        $user->update($v);
        return redirect()->route('user.index')->with('success', 'Data pengguna diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun yang sedang digunakan.');
        }
        $user->delete();
        return back()->with('success', 'Pengguna dihapus.');
    }
}