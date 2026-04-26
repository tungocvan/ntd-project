<div class="space-y-10">

    {{-- ================= HEADER ================= --}}
    <div>
        <h2 class="text-xl font-bold text-gray-800 uppercase">
            Thông tin học sinh
        </h2>
        <p class="text-sm text-gray-500">
            Nhập thông tin cơ bản của học sinh để bắt đầu hồ sơ
        </p>
    </div>


    {{-- ================= NHÓM 1: CƠ BẢN ================= --}}
    <div class="bg-white border rounded-2xl p-6 space-y-6">

        <h3 class="font-semibold text-gray-700 uppercase text-sm">
            Thông tin định danh
        </h3>

        <div class="grid md:grid-cols-3 gap-5">

            {{-- HỌ TÊN --}}
            <div class="md:col-span-2">
                <label class="text-sm font-medium">
                    Họ và tên học sinh <span class="text-red-500">*</span>
                </label>
                <input type="text" wire:model.lazy="form.HoVaTenHocSinh" x-on:input="$el.value"
                    class="w-full rounded-xl border border-gray-300 px-4 py-3 mt-1 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
            </div>

            {{-- GIỚI TÍNH --}}
            <div>
                <label class="text-sm font-medium">
                    Giới tính
                </label>
                <select wire:model.defer="form.GioiTinh"
                    class="w-full rounded-xl border border-gray-300 px-4 py-3 mt-1 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                    <option value="">-- Chọn --</option>
                    <option value="Nam">Nam</option>
                    <option value="Nữ">Nữ</option>
                </select>
            </div>

            {{-- NGÀY SINH --}}
            <div>
                <label class="text-sm font-medium">Ngày sinh</label>
                <input type="date" wire:model.defer="form.NgaySinh"
                    class="w-full rounded-xl border border-gray-300 px-4 py-3 mt-1 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
            </div>

            {{-- CCCD --}}
            <div>
                <label class="text-sm font-medium">
                    Mã định danh <span class="text-red-500">*</span>
                </label>
                <input type="text" maxlength="12" inputmode="numeric" pattern="[0-9]*"
                    wire:model.defer="form.MaDinhDanh" oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                    class="w-full rounded-xl border border-gray-300 px-4 py-3 mt-1 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
            </div>

            {{-- SĐT --}}
            <div>
                <label class="text-sm font-medium">
                    SĐT (EnetViet)
                </label>
                <input type="text" wire:model.defer="form.SDTEnetViet"
                    class="w-full rounded-xl border border-gray-300 px-4 py-3 mt-1 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
            </div>

        </div>
    </div>


    {{-- ================= NHÓM 2: THÔNG TIN CÁ NHÂN ================= --}}
    <div class="bg-white border rounded-2xl p-6 space-y-6 my-1">

        <h3 class="font-semibold text-gray-700 uppercase text-sm">
            Phần I
        </h3>

        <div class="grid md:grid-cols-3 gap-5">

            {{-- DÂN TỘC --}}
            <div>
                <label class="text-sm font-medium">Dân tộc</label>
                <x-select-search id="dan_toc" wire:model="form.DanToc" placeholder="Chọn dân tộc...">
                    <option value="">-- Chọn --</option>
                    @foreach ($ethnicities as $et)
                        <option value="{{ $et['value'] }}">{{ $et['value'] }}</option>
                    @endforeach
                </x-select-search>
            </div>

            {{-- QUỐC TỊCH --}}
            <div>
                <label class="text-sm font-medium">Quốc tịch</label>
                <input type="text" wire:model.defer="form.QuocTich"
                    class="w-full rounded-xl border border-gray-300 px-4 py-3 mt-1 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
            </div>

            {{-- TÔN GIÁO --}}
            <div>
                <label class="text-sm font-medium">Tôn giáo</label>
                <x-select-search id="ton_giao" wire:model="form.TonGiao" placeholder="Chọn tôn giáo...">
                    <option value="">-- Chọn --</option>
                    @foreach ($religions as $rl)
                        <option value="{{ $rl['value'] }}">{{ $rl['value'] }}</option>
                    @endforeach
                </x-select-search>
            </div>

        </div>
    </div>


    {{-- ================= NHÓM 3: ĐỊA PHƯƠNG ================= --}}
    <div class="bg-white border rounded-2xl p-6 space-y-6">

        <h3 class="font-semibold text-gray-700 uppercase text-sm">
            Phần II
        </h3>

        <div class="space-y-5">

            {{-- NƠI KHAI SINH --}}
            <div>
                <label class="text-sm font-medium">
                    Nơi sinh (Tỉnh/TP)
                </label>
                <x-select-search id="noi_sinh" wire:model="form.NoiSinhTt" placeholder="Chọn nơi sinh...">
                    <option value="">-- Chọn --</option>
                    @foreach ($provinces as $p)
                        <option value="{{ $p['province_name'] }}">{{ $p['province_name'] }}</option>
                    @endforeach
                </x-select-search>
            </div>
            <div>
                <label class="text-sm font-medium">
                    Nơi sinh (Phường/Xã)
                </label>
                <x-select-search id="noi_sinh_px" options-wire="noi_sinh_wards" wire:model.live="form.NoiSinhPx"
                    placeholder="Phường / Xã">
                    <option value="">-- Chọn --</option>

                    @if (!empty($noi_sinh_wards))
                        @foreach ($noi_sinh_wards as $w)
                            <option value="{{ $w['ward_name'] }}">
                                {{ $w['ward_name'] }}
                            </option>
                        @endforeach
                    @endif
                </x-select-search>
            </div> 
             <div>
            <label class="text-sm font-medium">
                    Nơi sinh
                </label>
                <input type="text" wire:model.live="form.NoiSinh"
                    class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
            </div>
            <div>
                <label class="text-sm font-medium">
                    Nơi sinh chi tiết: {{ $form['NoiSinh'].", "  }}{{ $form['NoiSinhPx'].", "  }}{{ $form['NoiSinhTt']  }}
                </label>                
            </div>

            {{-- CHECKBOX COPY --}}
            {{-- <label class="flex items-center gap-2 text-sm cursor-pointer">
                <input type="checkbox" wire:model.live="copyNoiSinhToQueQuan" class="rounded border-gray-300">
                Quê quán giống nơi sinh
            </label> --}}

            {{-- QUÊ QUÁN --}}
            <div>
                <label class="text-sm font-medium">
                    Quê quán Tỉnh/TP
                </label>
                <x-select-search id="que_quan" wire:model="form.QueQuanTt" placeholder="Chọn quê quán...">
                    <option value="">-- Chọn --</option>
                    @foreach ($provinces as $p)
                        <option value="{{ $p['province_name'] }}">{{ $p['province_name'] }}</option>
                    @endforeach
                </x-select-search>
            </div>
            <div>
                <label class="text-sm font-medium">
                    Quê quán Phường/Xã
                </label>
                <x-select-search id="que_quan_px" options-wire="que_quan_wards" wire:model.live="form.QueQuanPx"
                    placeholder="Phường / Xã">
                    <option value="">-- Chọn --</option>

                    @if (!empty($que_quan_wards))
                        @foreach ($que_quan_wards as $w)
                            <option value="{{ $w['ward_name'] }}">
                                {{ $w['ward_name'] }}
                            </option>
                        @endforeach
                    @endif
                </x-select-search>
            </div>            

            
           

            {{-- <div>
                <label class="text-sm font-medium">
                    Quê quán chi tiết: {{ $form['QueQuanPx'].", "  }}{{ $form['QueQuanTt']  }}
                </label>                
            </div> --}}
            
        </div>
    </div>

</div>
