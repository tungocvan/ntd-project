<?php

namespace Modules\Admission\Imports;

use Modules\Admission\Models\AdmissionApplication;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

use Maatwebsite\Excel\Validators\Failure;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ApplicationsImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    SkipsOnFailure,
    SkipsEmptyRows
{
    /**
     * MAIN IMPORT
     */
    public function model(array $row)
    {
        try {

            // 🔥 LOG RAW (debug khi cần)
            Log::info('IMPORT RAW ROW', $row);

            // =========================
            // 🔥 NORMALIZE DATA
            // =========================

            $gioiTinh = $this->normalizeGender($row['gioi_tinh'] ?? null);
            $status   = $this->normalizeStatus($row['status'] ?? null);

            $ngaySinh = $this->parseDate($row['ngay_sinh'] ?? null);
            $ngayDon  = $this->parseDate($row['ngay_lam_don'] ?? null) ?? now();

            // =========================
            // 🔥 BUILD DATA
            // =========================

            $data = [
                'mhs' => $row['mhs'] ?? null,
                'status' => $status,

                'ho_va_ten_hoc_sinh' => $row['ho_va_ten_hoc_sinh'] ?? null,
                'gioi_tinh' => $gioiTinh,
                'ngay_sinh' => $ngaySinh,

                'dan_toc' => $row['dan_toc'] ?? null,
                'ma_dinh_danh' => $row['ma_dinh_danh'] ?? null,
                'quoc_tich' => $row['quoc_tich'] ?? null,
                'ton_giao' => $row['ton_giao'] ?? null,
                'sdt_enetviet' => $row['sdt_enetviet'] ?? null,

                'noi_sinh' => $row['noi_sinh'] ?? null,
                'noi_dang_ky_khai_sinh' => $row['noi_dang_ky_khai_sinh'] ?? null,
                'que_quan' => $row['que_quan'] ?? null,

                // Địa chỉ
                'ttsn' => $row['ttsn'] ?? null,
                'ttd' => $row['ttd'] ?? null,
                'ttkp' => $row['ttkp'] ?? null,
                'ttpx' => $row['ttpx'] ?? null,
                'ttttp' => $row['ttttp'] ?? null,
                'dia_chi_thuong_tru' => $row['dia_chi_thuong_tru'] ?? null,

                'htsn' => $row['htsn'] ?? null,
                'htd' => $row['htd'] ?? null,
                'htkp' => $row['htkp'] ?? null,
                'htpx' => $row['htpx'] ?? null,
                'htttp' => $row['htttp'] ?? null,
                'noi_o_hien_tai' => $row['noi_o_hien_tai'] ?? null,

                // JSON
                'kha_nang_hoc_sinh' => $this->parseJson($row['kha_nang_hoc_sinh'] ?? null),
                'suc_khoe_can_luu_y' => $this->parseJson($row['suc_khoe_can_luu_y'] ?? null),

                // Phụ huynh
                'ho_ten_cha' => $row['ho_ten_cha'] ?? null,
                'dien_thoai_cha' => $row['dien_thoai_cha'] ?? null,

                'ho_ten_me' => $row['ho_ten_me'] ?? null,
                'dien_thoai_me' => $row['dien_thoai_me'] ?? null,

                'ho_ten_nguoi_giam_ho' => $row['ho_ten_nguoi_giam_ho'] ?? null,
                'dien_thoai_giam_ho' => $row['dien_thoai_giam_ho'] ?? null,

                // Đăng ký
                'loai_lop_dang_ky' => $row['loai_lop_dang_ky'] ?? null,
                'ngay_lam_don' => $ngayDon,
                'nguoi_lam_don' => $row['nguoi_lam_don'] ?? null,
            ];

            // 🔥 LOG FINAL DATA
            Log::info('IMPORT FINAL DATA', $data);

            // =========================
            // 🔥 UPSERT
            // =========================

            return AdmissionApplication::updateOrCreate(
                ['mhs' => $data['mhs']],
                $data
            );
        } catch (\Throwable $e) {
            Log::error('IMPORT ERROR', [
                'row' => $row,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * VALIDATION
     */
    public function rules(): array
    {
        return [
            '*.mhs' => ['required'],
            '*.ho_va_ten_hoc_sinh' => ['required', 'string'],
            '*.gioi_tinh' => ['nullable'],
            '*.status' => ['nullable'],
        ];
    }

    /**
     * VALIDATION FAIL LOG
     */
    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            Log::error('IMPORT VALIDATION FAIL', [
                'row' => $failure->row(),
                'errors' => $failure->errors(),
                'values' => $failure->values(),
            ]);
        }
    }

    /**
     * =========================
     * HELPERS
     * =========================
     */

    protected function normalizeGender($value)
    {
        $v = strtolower(trim($value ?? ''));

        return match ($v) {
            'nam' => 'nam',
            'nữ', 'nu' => 'nu',
            default => null,
        };
    }

    protected function normalizeStatus($value)
    {
        $v = strtolower(trim($value ?? ''));

        return match ($v) {
            'pending' => 'pending',
            'approved' => 'approved',
            'rejected' => 'rejected',
            default => 'pending',
        };
    }

    protected function parseDate($value)
    {
        if (!$value) return null;

        try {
            // Excel numeric date
            if (is_numeric($value)) {
                return ExcelDate::excelToDateTimeObject($value)->format('Y-m-d');
            }

            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function parseJson($value)
    {
        if (!$value) return null;

        if (is_array($value)) return $value;

        if (is_string($value)) {
            return array_map('trim', explode(',', $value));
        }

        return null;
    }
}
