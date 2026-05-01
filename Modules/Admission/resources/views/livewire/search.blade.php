<div>

    <form wire:submit.prevent="login">

        {{-- Mã định danh --}}
        <div>
            <label class="text-sm font-medium text-gray-600">
                Mã định danh <span class="text-rose-500">*</span>
            </label>

            <input type="text" maxlength="12" inputmode="numeric" wire:model.defer="MaDinhDanh"
                oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                class="w-full rounded-xl border border-gray-300 px-4 py-3 mt-1 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
        </div>

        {{-- PASSWORD --}}
        <div class="mb-4 my-2">
            <label class="block text-gray-700 text-sm font-medium mb-2">
                Mật khẩu (ddmmyyyy)
            </label>

            <input wire:model.defer="password" type="password"
                class="w-full rounded-xl border border-gray-300 px-4 py-3 mt-1 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                placeholder="••••••••">
        </div>

        {{-- ERROR --}}
        @if ($message)
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

    @if ($showModal)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 
       flex items-center justify-center 
       px-4 py-6 md:py-10 lg:py-16">

            <div class="relative w-full max-w-5xl ">
                <div class="relative text-white rounded-3xl shadow-2xl overflow-hidden px-12 py-2  max-h-[95vh]"
                    style="background: linear-gradient(135deg, #0f4c5c, #0a3d47);">

                    {{-- CLOSE (RIGHT) --}}
                    <button wire:click="closeModal"
                        class="absolute top-5 right-5 bg-white text-gray-700 w-10 h-10 rounded-full shadow flex items-center justify-center hover:bg-gray-100 transition">
                        ✕
                    </button>

                    {{-- HEADER (CÂN LOGO + TEXT) --}}
                    <div class="flex items-center justify-center gap-4 mb-6">

                        <img src="{{ asset('storage/admission/img/logo-ntd.png') }}" class="w-32 h-32 object-contain">

                        <div class="text-center  text-2xl">
                            <p class=" font-semibold tracking-wide">
                                ỦY BAN NHÂN DÂN PHƯỜNG TÂN THUẬN
                            </p>
                            <p class=" font-semibold tracking-wide">
                                TRƯỜNG TIỂU HỌC NGUYỄN THỊ ĐỊNH
                            </p>
                        </div>
                    </div>

                    {{-- TITLE --}}
                    <div class="text-center mb-6">
                        <h1 class="font-bold text-lg uppercase">
                            HỘI ĐỒNG TUYỂN SINH LỚP 1
                        </h1>

                        <h3 class="font-bold text-lg uppercase">
                            TRƯỜNG TIỂU HỌC NGUYỄN THỊ ĐỊNH
                        </h3>

                        <h1 class="text-3xl font-extrabold mt-3 tracking-wide">
                            ĐÃ TIẾP NHẬN HỒ SƠ
                        </h1>
                    </div>

                    {{-- CONTENT --}}
                    <div class="max-w-2xl mx-auto text-sm md:text-base">

                        <table class="w-[90%]">
                            <tr>
                                <td class="w-44 py-2">Học sinh:</td>
                                <td class="border-b border-white/20"> {{ $app['ho_va_ten_hoc_sinh'] }}</td>
                            </tr>
                            <tr>
                                <td class="py-2">Ngày sinh:</td>
                                <td class="border-b border-white/20">{{ \Carbon\Carbon::parse($app['ngay_sinh'])->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <td class="py-2">Mã định danh:</td>
                                <td class="border-b border-white/20">{{ $app['ma_dinh_danh'] }}</td>
                            </tr>
                            <tr>
                                <td class="py-2">Mã hồ sơ:</td>
                                <td class="border-b border-white/20">{{ $app['mhs'] }}</td>
                            </tr>
                        </table>

                        <div class="text-center mt-8 mb-5 font-semibold tracking-wide">
                            HỌC SINH ĐƯỢC PHÂN VÀO LỚP
                        </div>

                        <table class="w-[90%]">
                            <tr>
                                <td class="w-44 py-2">➤ Lớp:</td>
                                <td class="border-b border-white/20"></td>
                            </tr>
                            <tr>
                                <td class="py-2">➤ GVCN:</td>
                                <td class="border-b border-white/20"></td>
                            </tr>
                            <tr>
                                <td class="py-2">➤ Bảo mẫu:</td>
                                <td class="border-b border-white/20"></td>
                            </tr>
                        </table>
                    </div>

                    {{-- LEFT IMAGE (TĂNG NHẸ) --}}
                    <img src="{{ asset('storage/admission/img/left.png') }}"
                        class="absolute bottom-6 left-0 w-[200px] h-[133px] object-contain opacity-95">
                    <img src="{{ asset('storage/admission/img/right.png') }}"
                        class="absolute bottom-4 right-0 w-[200px] h-[133px] object-contain opacity-95">



                </div>
            </div>
        </div>
    @endif

</div>
