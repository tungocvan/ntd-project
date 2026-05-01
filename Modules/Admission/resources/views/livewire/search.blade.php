<div>

    <form wire:submit.prevent="login">

        {{-- Mã định danh --}}
        <div>
            <label class="text-sm font-medium text-gray-600">
                Mã định danh <span class="text-rose-500">*</span>
            </label>

            <input type="text"
                maxlength="12"
                inputmode="numeric"
                wire:model.defer="MaDinhDanh"
                oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                class="w-full rounded-xl border border-gray-300 px-4 py-3 mt-1 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
        </div>

        {{-- PASSWORD --}}
        <div class="mb-4 my-2">
            <label class="block text-gray-700 text-sm font-medium mb-2">
                Mật khẩu (ddmmyyyy)
            </label>

            <input wire:model.defer="password"
                type="password"
                class="w-full rounded-xl border border-gray-300 px-4 py-3 mt-1 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                placeholder="••••••••">
        </div>

        {{-- ERROR --}}
        @if($message)
            <div class="text-red-500 text-sm mb-2">
                {{ $message }}
            </div>
        @endif

        {{-- BUTTON --}}
        <button type="submit"
            class="w-full bg-slate-900 text-white font-semibold py-3 rounded-xl hover:bg-slate-700 transition shadow-sm">

            <span wire:loading.remove>Tra cứu thông tin</span>
            <span wire:loading>Đang xử lý...</span>

        </button>

    </form>

    {{-- MODAL --}}
    @if($showModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">

            <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md">

                <h2 class="text-xl font-semibold text-green-600 mb-3">
                    🎉 Thông báo
                </h2>

                <p class="text-gray-700">
                    Nhà trường đã tiếp nhận hồ sơ của học sinh.
                </p>

                <button wire:click="closeModal"
                    class="mt-5 w-full bg-slate-900 text-white py-2 rounded-lg hover:bg-slate-700">
                    Đóng
                </button>

            </div>

        </div>
    @endif

</div>