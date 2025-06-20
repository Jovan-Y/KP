    <x-app-layout>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Kelola Akun Pegawai
            </h2>
        </x-slot>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                {{-- Notifikasi --}}
                @if (session('success'))
                    <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-md">{{ session('success') }}</div>
                @endif
                @if ($errors->any() || session('error'))
                    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md" role="alert">
                        <strong class="font-bold">Oops! Terjadi kesalahan:</strong>
                        <ul class="mt-2 list-disc list-inside">
                            @if(session('error')) <li>{{ session('error') }}</li> @endif
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-bold">Daftar Akun Pegawai</h3>
                            <x-primary-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'add-user-modal')">
                                Tambah Akun
                            </x-primary-button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($employees as $employee)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $employee->name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $employee->email }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $employee->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ ucfirst($employee->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                                <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'password-modal-{{ $employee->id }}')" class="text-indigo-600 hover:text-indigo-900">Ganti Password</button>
                                                
                                                <form action="{{ route('users.update_status', $employee->id) }}" method="POST" class="inline" onsubmit="return confirm('Anda yakin ingin mengubah status akun ini?');">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="{{ $employee->status === 'active' ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900' }}">
                                                        {{ $employee->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>

                                        {{-- Modal Ganti Password untuk setiap user --}}
                                        <x-modal name="password-modal-{{ $employee->id }}" :show="$errors->userDeletion->isNotEmpty()" focusable>
                                            <form method="post" action="{{ route('users.update_password', $employee->id) }}" class="p-6">
                                                @csrf
                                                @method('put')
                                                <h2 class="text-lg font-medium text-gray-900">Ganti Password untuk {{ $employee->name }}</h2>
                                                <div class="mt-6">
                                                    <x-input-label for="password_{{ $employee->id }}" value="Password Baru" />
                                                    <x-text-input id="password_{{ $employee->id }}" name="password" type="password" class="mt-1 block w-full" required />
                                                </div>
                                                <div class="mt-4">
                                                    <x-input-label for="password_confirmation_{{ $employee->id }}" value="Konfirmasi Password Baru" />
                                                    <x-text-input id="password_confirmation_{{ $employee->id }}" name="password_confirmation" type="password" class="mt-1 block w-full" required />
                                                </div>
                                                <div class="mt-6 flex justify-end">
                                                    <x-secondary-button x-on:click="$dispatch('close')">Batal</x-secondary-button>
                                                    <x-primary-button class="ms-3">Simpan Password</x-primary-button>
                                                </div>
                                            </form>
                                        </x-modal>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-gray-500">Belum ada akun pegawai.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $employees->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Tambah Akun Pegawai --}}
        <x-modal name="add-user-modal" :show="$errors->userDeletion->isNotEmpty()" focusable>
            <form method="post" action="{{ route('users.store') }}" class="p-6">
                @csrf
                <h2 class="text-lg font-medium text-gray-900">Tambah Akun Pegawai Baru</h2>
                <div class="mt-6">
                    <x-input-label for="name" value="Nama Lengkap" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus />
                </div>
                <div class="mt-4">
                    <x-input-label for="email" value="Email" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required />
                </div>
                <div class="mt-4">
                    <x-input-label for="password" value="Password" />
                    <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" required />
                </div>
                <div class="mt-4">
                    <x-input-label for="password_confirmation" value="Konfirmasi Password" />
                    <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" required />
                </div>
                <div class="mt-6 flex justify-end">
                    <x-secondary-button x-on:click="$dispatch('close')">Batal</x-secondary-button>
                    <x-primary-button class="ms-3">Tambah Akun</x-primary-button>
                </div>
            </form>
        </x-modal>

    </x-app-layout>
    