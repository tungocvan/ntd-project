<?php

namespace Modules\Admission\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AdmissionApplication extends Model
{
    protected $table = 'admission_applications';

    protected $fillable = [
        'mhs',
        'status',
        // Bước 1: Thông tin học sinh
        'ho_va_ten_hoc_sinh',
        'gioi_tinh',
        'ngay_sinh',
        'dan_toc',
        'ma_dinh_danh',
        'quoc_tich',
        'ton_giao',
        'sdt_enetviet',
        'noi_sinh',
        'noi_sinh_px',
        'noi_sinh_tt',
        'noi_dang_ky_khai_sinh',
        'noi_sinh_chi_tiet',
        'que_quan',
        'que_quan_px',
        'que_quan_tt',
        // Bước 2: Địa chỉ
        'ttsn',
        'ttd',
        'ttkp',
        'ttpx',
        'ttttp',
        'dia_chi_thuong_tru',
        'htsn',
        'htd',
        'htkp',
        'htpx',
        'htttp',
        'noi_o_hien_tai',
        // Bước 3: Thông tin bổ sung
        'o_chung_voi',
        'quan_he_nguoi_nuoi_duong',
        'con_thu',
        'ts_anh_chi_em',
        'hoan_thanh_lop_la',
        'truong_mam_non',
        'kha_nang_hoc_sinh',
        'suc_khoe_can_luu_y',
        // Bước 4: Phụ huynh
        'ho_ten_cha',
        'nam_sinh_cha',
        'tdvh_cha',
        'tdcm_cha',
        'nghe_nghiep_cha',
        'chuc_vu_cha',
        'dien_thoai_cha',
        'cccd_cha',
        'ho_ten_me',
        'nam_sinh_me',
        'tdvh_me',
        'tdcm_me',
        'nghe_nghiep_me',
        'chuc_vu_me',
        'dien_thoai_me',
        'cccd_me',
        'ho_ten_nguoi_giam_ho',
        'quan_he_giam_ho',
        'dien_thoai_giam_ho',
        'cccd_giam_ho',
        // Bước 5: Đăng ký & Cam kết
        'anh_chi_ruot_trong_truong',
        'thanh_phan_gia_dinh',
        'loai_lop_dang_ky',
        'ck_goc_hoc_tap',
        'ck_sach_vo',
        'ck_hop_ph',
        'ck_tham_gia_hd',
        'ck_gan_gui',
        'ngay_lam_don',
        'nguoi_lam_don',
        'pdf_path',
        'word_path'
    ];
    protected $casts = [
        'kha_nang_hoc_sinh' => 'array',
        'suc_khoe_can_luu_y' => 'array',
    ];

    protected static function booted()
    {

        static::updating(function ($model) {
            if (
                $model->getOriginal('status') === 'approved' &&
                $model->isDirty() &&
                !$model->isDirty('status') // ❗ thêm dòng này
            ) {
                $model->status = 'pending';
            }
        });

        static::updated(function ($model) {

            // Khi chuyển sang approved
            if ($model->wasChanged('status') && $model->status === 'approved') {
                 \Log::info('updated DEBUG START');
                \Modules\Admission\Jobs\GenerateAdmissionPdfJob::dispatch($model->id);
            }
        });

        // ❗ XÓA FILE KHI DELETE


        static::deleting(function ($model) {

            foreach (['pdf_path', 'word_path'] as $field) {

                $path = $model->$field;

                // \Log::info('DELETE DEBUG START', [
                //     'field' => $field,
                //     'path' => $path,
                // ]);

                if (!$path) continue;

                // 🔥 FIX: convert to full path
                $fullPath = storage_path('app/' . $path);

                $exists = file_exists($fullPath);

                // \Log::info('FILE EXISTS CHECK', [
                //     'fullPath' => $fullPath,
                //     'exists' => $exists,
                // ]);

                if (!$exists) continue;

                $deleted = @unlink($fullPath);

                // \Log::info('UNLINK RESULT', [
                //     'deleted' => $deleted,
                //     'error' => error_get_last(),
                // ]);
            }
        });
    }
}
