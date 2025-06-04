<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gambar dari Supplier') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <p class="text-lg font-medium text-gray-900 mb-4">
                        Ada **{{ $unlinkedUploadsCount }}** gambar supplier yang belum dikaitkan.
                    </p>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gambar</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Upload</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengunggah</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Faktur Terkait</th>
                                    <th class="relative px-6 py-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($supplierUploads as $upload)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a href="{{ Storage::url($upload->filepath) }}" target="_blank">
                                                <img src="{{ Storage::url($upload->filepath) }}" alt="{{ $upload->title ?? $upload->filename }}" class="h-16 w-16 object-cover rounded-md">
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $upload->upload_code }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $upload->title ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $upload->supplier->name ?? 'N/A' }} ({{ $upload->supplier->email ?? '-' }})</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $upload->is_linked ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $upload->is_linked ? 'Terkait' : 'Belum Terkait' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($upload->linkedInvoice)
                                                <a href="{{ route('invoices.show', $upload->linkedInvoice->id) }}" class="underline">{{ $upload->linkedInvoice->invoice_number }}</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            @if(!$upload->is_linked)
                                                <form action="{{ route('supplier.images.link', $upload->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Anda yakin ingin mengaitkan gambar ini?');">
                                                    @csrf
                                                    <label for="invoice_id_{{ $upload->id }}" class="sr-only">Pilih Faktur</label>
                                                    <select name="invoice_id" id="invoice_id_{{ $upload->id }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-xs">
                                                        <option value="">-- Pilih Faktur --</option>
                                                        @foreach($invoices as $invoice)
                                                            <option value="{{ $invoice->id }}">{{ $invoice->invoice_number }}</option>
                                                        @endforeach
                                                    </select>
                                                    <x-primary-button class="ml-2 px-3 py-1 bg-indigo-600 hover:bg-indigo-500 text-xs">Kaitkan</x-primary-button>
                                                </form>
                                            @endif
                                            <form action="{{ route('supplier.images.destroy', $upload->id) }}" method="POST" class="inline-block ml-2" onsubmit="return confirm('Anda yakin ingin menghapus gambar ini secara permanen?');">
                                                @csrf
                                                @method('DELETE')
                                                <x-danger-button class="px-3 py-1 text-xs">Hapus</x-danger-button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Belum ada gambar yang diunggah oleh supplier.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $supplierUploads->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>