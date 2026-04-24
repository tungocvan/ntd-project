<?php

namespace Modules\Admission\Exports;

use Modules\Admission\Models\AdmissionApplication;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ApplicationsExport implements FromCollection, WithHeadings
{
    protected $search;
    protected $status;
    protected $class;

    public function __construct($search = null, $status = null, $class = null)
    {
        $this->search = $search;
        $this->status = $status;
        $this->class = $class;
    }

    public function collection()
    {
        return AdmissionApplication::query()
            ->when($this->search, function ($q) {
                $q->where(function ($qq) {
                    $qq->where('ho_va_ten_hoc_sinh', 'like', '%' . $this->search . '%')
                        ->orWhere('ma_dinh_danh', 'like', '%' . $this->search . '%')
                        ->orWhere('sdt_enetviet', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->class, fn($q) => $q->where('loai_lop_dang_ky', $this->class))
            ->get()
            ->map(function ($item) {
                return [
                    'mhs' => $item->mhs,
                    'status' => $item->status,
                    'ho_va_ten_hoc_sinh' => $item->ho_va_ten_hoc_sinh,
                    'gioi_tinh' => $item->gioi_tinh,
                    'ngay_sinh' => $item->ngay_sinh,
                    'dan_toc' => $item->dan_toc,
                    'ma_dinh_danh' => $item->ma_dinh_danh,
                    'quoc_tich' => $item->quoc_tich,
                    'ton_giao' => $item->ton_giao,
                    'sdt_enetviet' => $item->sdt_enetviet,
                    'noi_sinh' => $item->noi_sinh,
                    'noi_dang_ky_khai_sinh' => $item->noi_dang_ky_khai_sinh,
                    'que_quan' => $item->que_quan,

                    'ttsn' => $item->ttsn,
                    'ttd' => $item->ttd,
                    'ttkp' => $item->ttkp,
                    'ttpx' => $item->ttpx,
                    'ttttp' => $item->ttttp,
                    'dia_chi_thuong_tru' => $item->dia_chi_thuong_tru,

                    'htsn' => $item->htsn,
                    'htd' => $item->htd,
                    'htkp' => $item->htkp,
                    'htpx' => $item->htpx,
                    'htttp' => $item->htttp,
                    'noi_o_hien_tai' => $item->noi_o_hien_tai,

                    'kha_nang_hoc_sinh' => is_array($item->kha_nang_hoc_sinh)
                        ? implode(',', $item->kha_nang_hoc_sinh)
                        : null,

                    'suc_khoe_can_luu_y' => is_array($item->suc_khoe_can_luu_y)
                        ? implode(',', $item->suc_khoe_can_luu_y)
                        : null,

                    'ho_ten_cha' => $item->ho_ten_cha,
                    'dien_thoai_cha' => $item->dien_thoai_cha,
                    'ho_ten_me' => $item->ho_ten_me,
                    'dien_thoai_me' => $item->dien_thoai_me,

                    'ho_ten_nguoi_giam_ho' => $item->ho_ten_nguoi_giam_ho,
                    'dien_thoai_giam_ho' => $item->dien_thoai_giam_ho,

                    'loai_lop_dang_ky' => $item->loai_lop_dang_ky,
                    'ngay_lam_don' => $item->ngay_lam_don,
                    'nguoi_lam_don' => $item->nguoi_lam_don,

                    'created_at' => $item->created_at,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'mhs',
            'status',
            'ho_va_ten_hoc_sinh',
            'gioi_tinh',
            'ngay_sinh',
            'dan_toc',
            'ma_dinh_danh',
            'quoc_tich',
            'ton_giao',
            'sdt_enetviet',
            'noi_sinh',
            'noi_dang_ky_khai_sinh',
            'que_quan',

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

            'kha_nang_hoc_sinh',
            'suc_khoe_can_luu_y',

            'ho_ten_cha',
            'dien_thoai_cha',
            'ho_ten_me',
            'dien_thoai_me',

            'ho_ten_nguoi_giam_ho',
            'dien_thoai_giam_ho',

            'loai_lop_dang_ky',
            'ngay_lam_don',
            'nguoi_lam_don',
            'created_at',
        ];
    }
}
