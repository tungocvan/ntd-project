<div class="p-6 space-y-6">

    {{-- HEADER --}}
    <div>
        <h2 class="text-xl font-semibold text-gray-800">
            Quản lý Đơn vị Hành chính
        </h2>
        <p class="text-sm text-gray-500">
            Chỉnh sửa Tỉnh/Phường nhanh chóng
        </p>
    </div>

    {{-- CARD: FILTER + UPDATE TỈNH --}}
    <div class="bg-white rounded-2xl shadow-sm border p-5 space-y-4">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">

            {{-- SELECT TỈNH --}}
            <div>
                <label class="text-sm font-medium text-gray-600 mb-1 block">
                    Chọn Tỉnh / TP
                </label>

                <x-select-search
                    id="province_select"
                    wire:model="selectedProvince"
                    placeholder="Chọn tỉnh..."
                >
                    <option value="">-- Chọn --</option>

                    @foreach ($provinces as $p)
                        <option value="{{ $p['province_name'] }}">
                            {{ $p['province_name'] }}
                        </option>
                    @endforeach
                </x-select-search>
            </div>

            {{-- INPUT TÊN TỈNH --}}
            <div>
                <label class="text-sm font-medium text-gray-600 mb-1 block">
                    Tên Tỉnh
                </label>

                <input type="text"
                       wire:model="editingProvinceName"
                       class="w-full border rounded-xl px-3 py-2 text-sm
                              focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            {{-- BUTTON --}}
            <div>
                <button wire:click="updateProvinceName"
                        class="w-full bg-blue-600 hover:bg-blue-700
                               text-white font-medium px-4 py-2 rounded-xl
                               transition">
                    Cập nhật Tỉnh
                </button>
            </div>

        </div>

    </div>

    {{-- CARD: TABLE --}}
    <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">

        {{-- SEARCH --}}
        <div class="p-4 border-b">
            <input type="text"
                   wire:model.debounce.500ms="search"
                   placeholder="Tìm phường..."
                   class="w-full border rounded-xl px-3 py-2 text-sm
                          focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        {{-- TABLE --}}
        <div class="overflow-auto max-h-[500px]">
            <table class="w-full text-sm">

                <thead class="bg-gray-50 sticky top-0 z-10">
                    <tr class="text-left text-gray-600">
                        <th class="px-4 py-3 font-medium">Tỉnh</th>
                        <th class="px-4 py-3 font-medium">Mã phường</th>
                        <th class="px-4 py-3 font-medium">Tên phường</th>
                        <th class="px-4 py-3 w-16"></th>
                    </tr>
                </thead>

                <tbody class="divide-y">

                    @forelse($rows as $index => $row)
                        <tr class="hover:bg-gray-50 transition">

                            {{-- TỈNH --}}
                            <td class="px-4 py-2 text-gray-700">
                                {{ $row['province_name'] }}
                            </td>

                            {{-- CODE --}}
                            <td class="px-4 py-2 text-gray-400 text-xs">
                                {{ $row['ward_code'] }}
                            </td>

                            {{-- EDIT PHƯỜNG --}}
                            <td class="px-4 py-1">
                                <input
                                    wire:model.lazy="rows.{{ $index }}.ward_name"
                                    wire:blur="updateRow({{ $index }})"
                                    class="w-full border rounded-lg px-2 py-1 text-sm
                                           focus:ring-2 focus:ring-green-500
                                           focus:border-green-500"
                                >
                            </td>

                            <td class="px-4 text-center text-green-500 text-lg">
                                ✔
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-6 text-gray-400">
                                Không có dữ liệu
                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>
        </div>

    </div>

</div>