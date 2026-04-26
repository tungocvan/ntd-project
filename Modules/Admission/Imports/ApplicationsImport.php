<?php

namespace Modules\Admission\Imports;

use Modules\Admission\Models\AdmissionApplication;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

use Maatwebsite\Excel\Concerns\{
    ToCollection,
    WithHeadingRow,
    SkipsEmptyRows
};

use Illuminate\Support\Collection;

class ApplicationsImport implements
    ToCollection,
    WithHeadingRow,
    SkipsEmptyRows
{
    protected array $exceptFields = [
        'id',
        'noi_sinh_chi_tiet',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {

            foreach ($rows as $index => $row) {

                try {

                    $row = $row->toArray();
                    Log::info('IMPORT ROW', ['row' => $row]);

                    $data = [];

                    foreach ($row as $key => $value) {

                        $column = $this->normalizeColumn($key);

                        if (in_array($column, $this->exceptFields)) {
                            continue;
                        }

                        // ❗ bỏ qua null → không overwrite
                        // if ($value === null || $value === '') {
                        //     continue;
                        // }

                        $data[$column] = $this->transformValue($column, $value);
                    }

                    // =========================
                    // 🔥 KEY CHECK (BẮT BUỘC)
                    // =========================

                    $key = $data['ma_dinh_danh']
                        ?? $data['mhs']
                        ?? null;
                    

                    if (!$key) {
                        Log::warning('SKIP - NO KEY', [
                            'row_index' => $index,
                            'row' => $row
                        ]);
                        continue;
                    }

               

                    // =========================
                    // 🔥 FIND EXISTING
                    // =========================

                    $record = AdmissionApplication::where('ma_dinh_danh', $key)
                        ->orWhere('mhs', $key)
                        ->first();

                    if ($record) {

                        // ✅ CHỈ UPDATE FIELD CÓ TRONG FILE
                        foreach ($data as $field => $value) {
                            $record->$field = $value;
                        }

                        $record->save();

                        Log::info('UPDATED', [
                            'id' => $record->id,
                            'key' => $key
                        ]);

                    } else {

                        AdmissionApplication::create($data);

                        Log::info('CREATED', [
                            'key' => $key
                        ]);
                    }

                } catch (\Throwable $e) {

                    Log::error('IMPORT ERROR', [
                        'row_index' => $index,
                        'error' => $e->getMessage(),
                        'row' => $row
                    ]);

                    // ❗ KHÔNG throw → không làm chết toàn bộ file
                    continue;
                }
            }
        });
    }

    /**
     * =========================
     * TRANSFORM
     * =========================
     */

    protected function transformValue($column, $value)
    {
        // if ($column === 'gioi_tinh') {
        //     return $this->normalizeGender($value);
        // }

        if ($column === 'status') {
            return $this->normalizeStatus($value);
        }

        if ($this->isDateField($column)) {
            return $this->parseDate($value);
        }

        if ($this->isArrayField($column)) {
            return $this->parseJson($value);
        }

        return $value;
    }

    protected function normalizeColumn($key)
    {
        return Str::of($key)
            ->lower()
            ->replace(' ', '_')
            ->toString();
    }

    protected function isDateField($column): bool
    {
        return str_contains($column, 'ngay') ||
               str_contains($column, 'date');
    }

    protected function isArrayField($column): bool
    {
        return in_array($column, [
            'kha_nang_hoc_sinh',
            'suc_khoe_can_luu_y',
        ]);
    }

    protected function normalizeGender($value)
    {
        $v = strtolower(trim($value));

        return match ($v) {
            'nam' => 'nam',
            'nữ', 'nu' => 'nu',
            default => null,
        };
    }

    protected function normalizeStatus($value)
    {
        $v = strtolower(trim($value));

        return match ($v) {
            'pending' => 'pending',
            'approved' => 'approved',
            'rejected' => 'rejected',
            default => 'pending',
        };
    }

    protected function parseDate($value)
    {
        try {
            if (is_numeric($value)) {
                return ExcelDate::excelToDateTimeObject($value)->format('Y-m-d');
            }

            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function parseJson($value)
    {
        if (is_array($value)) return $value;

        if (is_string($value)) {
            return array_map('trim', explode(',', $value));
        }

        return null;
    }
}