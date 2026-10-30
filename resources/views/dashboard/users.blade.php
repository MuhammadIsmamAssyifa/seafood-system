@extends('layouts.app')

@section('title', 'Manajemen Pengguna & Staf')
@section('page_title', 'Pengguna & Akses Staf')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-base font-bold text-slate-800">Daftar Akun Pengguna</h2>
            <p class="text-xs text-slate-500">Kelola akun Owner, Admin, Kasir POS, dan Staf Dapur KDS</p>
        </div>
        <button onclick="document.getElementById('userModal').classList.remove('hidden')" class="px-4 py-2 bg-slate-900 text-white text-sm font-semibold rounded-lg hover:bg-slate-800 transition">
            + Tambah Pengguna
        </button>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-xs">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-xs font-semibold text-slate-500 uppercase border-b border-slate-200">
                <tr>
                    <th class="px-5 py-3">Nama Pengguna</th>
                    <th class="px-5 py-3">Email & Username</th>
                    <th class="px-5 py-3">Peran (Role)</th>
                    <th class="px-5 py-3 text-center">PIN POS</th>
                    <th class="px-5 py-3 text-center">Status</th>
                    <th class="px-5 py-3">Tanggal Dibuat</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($users as $user)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3.5 font-semibold text-slate-900">{{ $user->name }}</td>
                        <td class="px-5 py-3.5 text-xs text-slate-500">
                            <div>{{ $user->email }}</div>
                            <div class="font-mono text-slate-700">@ {{ $user->username }}</div>
                        </td>
                        <td class="px-5 py-3.5">
                            @foreach($user->roles as $role)
                                <span class="px-2 py-0.5 rounded text-xs font-semibold 
                                    {{ $role->name === 'owner' ? 'bg-purple-100 text-purple-800' : ($role->name === 'admin' ? 'bg-cyan-100 text-cyan-800' : ($role->name === 'kitchen' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800')) }}">
                                    {{ strtoupper($role->name) }}
                                </span>
                            @endforeach
                        </td>
                        <td class="px-5 py-3.5 text-center font-mono text-xs">
                            @if($user->pin_hash)
                                <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 font-semibold">Aktif (4-digit)</span>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">
                                {{ ucfirst($user->status ?? 'active') }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-xs text-slate-500">
                            {{ $user->created_at?->format('d M Y') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-slate-400">Belum ada pengguna.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div id="userModal" class="hidden fixed inset-0 bg-slate-900/50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl border border-slate-200">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Tambah Akun Pengguna Baru</h3>
                <button onclick="document.getElementById('userModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form method="POST" action="{{ route('dashboard.users.store') }}" class="mt-4 space-y-3.5">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" required placeholder="Contoh: Siti Kasir" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Email</label>
                    <input type="email" name="email" required placeholder="kasir@seafood.com" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Username Login</label>
                    <input type="text" name="username" required placeholder="kasir1" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Password Web</label>
                    <input type="password" name="password" required placeholder="Minimal 6 karakter" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">PIN Cepat POS (4 Angka)</label>
                    <input type="password" name="pin" maxlength="4" placeholder="Contoh: 1234" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm font-mono text-center">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Peran Akses (Role)</label>
                    <select name="role" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ strtoupper($role->name) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('userModal').classList.add('hidden')" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-slate-900 text-white text-sm font-semibold rounded-lg hover:bg-slate-800">Simpan Pengguna</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

