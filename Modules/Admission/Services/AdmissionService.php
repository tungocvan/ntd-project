<?php

namespace Modules\Admission\Services;

use Modules\Admission\Models\AdmissionApplication;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AdmissionService
{
    /**
     * TẠO MỚI HỒ SƠ
     */
    public function createRegistration(array $formData)
    {
        // Tạo mã hồ sơ tự động
        $mhs = 'MHS' . date('Y') . str_pad(AdmissionApplication::count() + 1, 4, '0', STR_PAD_LEFT);

        // Chuẩn hóa dữ liệu trước khi lưu
        $data = $this->prepareData($formData);
        $data['mhs'] = $mhs;
        $data['status'] = $formData['Status'] ?? 'pending';

        return AdmissionApplication::create($data);
    }

    /**
     * CẬP NHẬT HỒ SƠ (Dành cho Admin)
     */
    public function updateRegistration($id, array $formData)
    {
        $application = AdmissionApplication::findOrFail($id);

        // Chuẩn hóa dữ liệu
        $data = $this->prepareData($formData);

        // Admin có quyền cập nhật cả trạng thái
        if (isset($formData['Status'])) {
            $data['status'] = $formData['Status'];
        }

        $application->update($data);
        return $application;
    }

    /**
     * HÀM CHUẨN HÓA DỮ LIỆU TRUNG TÂM (Tránh lỗi SQL 1366 và lỗi định dạng)
     */
    private function prepareData(array $formData)
    {
        return [
            // 1. Thông tin học sinh
            'ho_va_ten_hoc_sinh' => $formData['HoVaTenHocSinh'] ?? null,
            'gioi_tinh'          => $formData['GioiTinh'] ?? null,
            'ngay_sinh'          => !empty($formData['NgaySinh']) ? Carbon::parse($formData['NgaySinh'])->format('Y-m-d') : null,
            'dan_toc'            => $formData['DanToc'] ?? null,
            'ma_dinh_danh'       => $formData['MaDinhDanh'] ?? null,
            'quoc_tich'          => $formData['QuocTich'] ?? null,
            'ton_giao'           => $formData['TonGiao'] ?? null,
            'sdt_enetviet'       => $formData['SDTEnetViet'] ?? null,
            'noi_sinh'           => $formData['NoiSinh'] ?? null,
            'noi_dang_ky_khai_sinh' => $formData['NoiDangKyKhaiSinh'] ?? null,
            'que_quan'           => $formData['QueQuan'] ?? null,

            // 2. Địa chỉ
            'ttsn'               => $formData['TTSN'] ?? null,
            'ttd'                => $formData['TTD'] ?? null,
            'ttkp'               => $formData['TTKP'] ?? null,
            'ttpx'               => $formData['TTPX'] ?? null,
            'ttttp'              => $formData['TTTTP'] ?? null,
            'htsn'               => $formData['HTSN'] ?? null,
            'htd'                => $formData['HTD'] ?? null,
            'htkp'               => $formData['HTKP'] ?? null,
            'htpx'               => $formData['HTPX'] ?? null,
            'htttp'              => $formData['HTTTP'] ?? null,
            'noi_o_hien_tai'     => $formData['NoiOHienTai'] ?? null,

            // 3. Thông tin bổ sung (Ép kiểu Integer để tránh lỗi 1366)
            'o_chung_voi'        => $formData['OChungVoi'] ?? null,
            'quan_he_nguoi_nuoi_duong' => $formData['QuanHeNguoiNuoiDuong'] ?? null,
            'con_thu'            => (isset($formData['ConThu']) && $formData['ConThu'] !== '') ? (int)$formData['ConThu'] : null,
            'ts_anh_chi_em'      => (isset($formData['TSAnhChiEm']) && $formData['TSAnhChiEm'] !== '') ? (int)$formData['TSAnhChiEm'] : null,
            'hoan_thanh_lop_la'  => $formData['HoanThanhLopLa'] ?? null,
            'truong_mam_non'     => $formData['TruongMamNon'] ?? null,
            'kha_nang_hoc_sinh'  => $formData['KhaNangHocSinh'] ?? null,
            'suc_khoe_can_luu_y' => $formData['SucKhoeCanLuuY'] ?? null,

            // 4. Cha - Mẹ - Giám hộ
            'ho_ten_cha'         => $formData['HoTenCha'] ?? null,
            'nam_sinh_cha'       => (isset($formData['NamSinhCha']) && $formData['NamSinhCha'] !== '') ? (int)$formData['NamSinhCha'] : null,
            'tdvh_cha'           => $formData['TdvhCha'] ?? null,
            'tdcm_cha'           => $formData['TdcmCha'] ?? null,
            'nghe_nghiep_cha'    => $formData['NgheNghiepCha'] ?? null,
            'chuc_vu_cha'        => $formData['ChucVuCha'] ?? null,
            'dien_thoai_cha'     => $formData['DienThoaiCha'] ?? null,
            'cccd_cha'           => $formData['CCCDCha'] ?? null,

            'ho_ten_me'          => $formData['HoTenMe'] ?? null,
            'nam_sinh_me'        => (isset($formData['NamSinhMe']) && $formData['NamSinhMe'] !== '') ? (int)$formData['NamSinhMe'] : null,
            'tdvh_me'            => $formData['TdvhMe'] ?? null,
            'tdcm_me'            => $formData['TdcmMe'] ?? null,
            'nghe_nghiep_me'     => $formData['NgheNghiepMe'] ?? null,
            'chuc_vu_me'         => $formData['ChucVuMe'] ?? null,
            'dien_thoai_me'      => $formData['DienThoaiMe'] ?? null,
            'cccd_me'            => $formData['CCCDMe'] ?? null,

            'ho_ten_nguoi_giam_ho' => $formData['HoTenNguoiGiamHo'] ?? null,
            'quan_he_giam_ho'      => $formData['QuanHeGiamHo'] ?? null,
            'dien_thoai_giam_ho'   => $formData['DienThoaiGiamHo'] ?? null,
            'cccd_giam_ho'         => $formData['CCCDGiamHo'] ?? null,

            // 5. Đăng ký & Checkbox cam kết
            'anh_chi_ruot_trong_truong' => $formData['AnhChiRuotTrongTruong'] ?? null,
            'thanh_phan_gia_dinh'       => $formData['ThanhPhanGiaDinh'] ?? null,
            'loai_lop_dang_ky'          => $formData['LoaiLopDangKy'] ?? null,
            'ck_goc_hoc_tap'            => filter_var($formData['CK_GocHocTap'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
            'ck_sach_vo'                => filter_var($formData['CK_SachVo'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
            'ck_hop_ph'                 => filter_var($formData['CK_HopPH'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
            'ck_tham_gia_hd'            => filter_var($formData['CK_ThamGiaHD'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
            'ck_gan_gui'                => filter_var($formData['CK_GanGui'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
            'ngay_lam_don'              => !empty($formData['NgayLamDon']) ? Carbon::parse($formData['NgayLamDon'])->format('Y-m-d') : date('Y-m-d'),
            'nguoi_lam_don'             => $formData['NguoiLamDon'] ?? null,
        ];
    }

    /**
     * DỮ LIỆU ĐỔ VÀO WORD (Sử dụng key của file Word mẫu)
     */
    public function getDataForTemplate($id)
    {
        $app = AdmissionApplication::findOrFail($id);
        return [
            'MaHoSo'            => $app->mhs,
            'HoVaTenHocSinh'    => Str::upper($app->ho_va_ten_hoc_sinh),
            'GioiTinh'          => $app->gioi_tinh,
            'NgaySinh'          => $app->ngay_sinh ? Carbon::parse($app->ngay_sinh)->format('d/m/Y') : '',
            'DanToc'            => $app->dan_toc,
            'MaDinhDanh'        => $app->ma_dinh_danh,
            'QuocTich'          => $app->quoc_tich,
            'TonGiao'           => $app->ton_giao,
            'SDTEnetViet'       => $app->sdt_enetviet,
            'NoiSinh'           => $app->noi_sinh,
            'NoiDangKyKhaiSinh' => $app->noi_dang_ky_khai_sinh,
            'QueQuan'           => $app->que_quan,
            'TTSN'              => $app->ttsn ?? '',
            'TTD'               => $app->ttd ?? '',
            'TTKP'              => $app->ttkp ?? '',
            'TTPX'              => $app->ttpx ?? '',
            'TTTTP'             => $app->ttttp ?? '',
            'HTSN'              => $app->htsn ?? '',
            'HTD'               => $app->htd ?? '',
            'HTKP'              => $app->htkp ?? '',
            'HTPX'              => $app->htpx ?? '',
            'HTTTP'             => $app->htttp ?? '',
            'OChungVoi'         => $app->o_chung_voi ?? 'Cha mẹ',
            'ConThu'            => $app->con_thu ?? '……',
            'TSAnhChiEm'        => $app->ts_anh_chi_em ?? '……',
            'HoanThanhLopLa'    => $app->hoan_thanh_lop_la ?? '…………………………',
            'TruongMamNon'      => $app->truong_mam_non ?? '…………………………',
            'KhaNangHocSinh'    => $app->kha_nang_hoc_sinh ?? '',
            'SucKhoeCanLuuY'    => $app->suc_khoe_can_luu_y ?? '……………………………………………',
            'HoTenCha'          => $app->ho_ten_cha ?? '',
            'NamSinhCha'        => $app->nam_sinh_cha ?? '',
            'TdvhCha'           => $app->tdvh_cha ?? '',
            'TdcmCha'           => $app->tdcm_cha ?? '',
            'NgheNghiepCha'     => $app->nghe_nghiep_cha ?? '',
            'ChuvuCha'          => $app->chuc_vu_cha ?? '',
            'DienThoaiCha'      => $app->dien_thoai_cha ?? '',
            'CCCDCha'           => $app->cccd_cha ?? '',
            'HoTenMe'           => $app->ho_ten_me ?? '',
            'NamSinhMe'         => $app->nam_sinh_me ?? '',
            'TdvhMe'            => $app->tdvh_me ?? '',
            'TdcmMe'            => $app->tdcm_me ?? '',
            'NgheNghiepMe'      => $app->nghe_nghiep_me ?? '',
            'ChuvuMe'           => $app->chuc_vu_me ?? '',
            'DienThoaiMe'       => $app->dien_thoai_me ?? '',
            'CCCDMe'            => $app->cccd_me ?? '',
            'HoTenNguoiGiamHo'  => $app->ho_ten_nguoi_giam_ho ?? '',
            'DienThoaiGiamHo'   => $app->dien_thoai_giam_ho ?? '',
            'CCCDGiamHo'        => $app->cccd_giam_ho ?? '',
            'LoaiLopDangKy'     => $app->loai_lop_dang_ky ?? '',
            'Ngay'              => Carbon::parse($app->created_at)->format('d'),
            'Thang'             => Carbon::parse($app->created_at)->format('m'),
            'Nam'               => Carbon::parse($app->created_at)->format('Y'),
            'NguoiLamDon'       => $app->nguoi_lam_don ?? '',
        ];
    }

    /**
     * Lấy danh sách cho Admin (CRUD)
     */
    public function getPaginatedList(array $filters = [], $perPage = 15)
    {
        $query = AdmissionApplication::query();
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('ho_va_ten_hoc_sinh', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('ma_dinh_danh', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('mhs', 'like', '%' . $filters['search'] . '%');
            });
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        return $query->latest()->paginate($perPage);
    }

    public function deleteRegistration($id)
    {
        return AdmissionApplication::findOrFail($id)->delete();
    }
}
