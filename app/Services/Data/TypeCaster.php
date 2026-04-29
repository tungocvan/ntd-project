<?php

namespace App\Services\Data;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class TypeCaster
{
    /**
     * =========================
     * INPUT (IMPORT / API)
     * =========================
     */
    public function castInput($type, $value)
    {
        if (!$type) {
            return $value;
        }

        // 🔥 FIX: tách type chính
        $baseType = explode(':', $type)[0];

        switch ($baseType) {

            case 'date':                
                // Excel serial
                if (is_numeric($value)) {
                    $number = (int) $value;
                      
                    if ($number > 1000 && $number < 60000) {
                        $temp = ExcelDate::excelToDateTimeObject($number);
                        //dd($number, $temp->format('d/m/Y'));
                        return $temp->format('Y-m-d');
                       
                    }
                }

                // string formats
                try {
                    return \Carbon\Carbon::createFromFormat('Y-m-d', $value)->format('Y-m-d');
                } catch (\Exception $e) {}

                try {
                    return \Carbon\Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
                } catch (\Exception $e) {}

                try {
                    return \Carbon\Carbon::createFromFormat('d/m/y', $value)->format('Y-m-d');
                } catch (\Exception $e) {}

                return null;

            default:
                return $value;
        }
    }

    /**
     * =========================
     * OUTPUT (EXPORT / API)
     * =========================
     */
    public function castOutput(?string $type, $value)
    {
        if ($value === null) {
            return '';
        }
            
        return match ($type) {

            'date', 'datetime' => $this->formatDate($value),

            'array', 'json' => implode(', ', (array) $value),

            'boolean' => $value ? 'Yes' : 'No',

            default => $value,
        };
    }

    /**
     * =========================
     * 📅 SMART DATE
     * =========================
     */
    protected function parseDate($value, ?string $column = null)
    {
        
        try {

         //$value = trim((string) $value);

            /**
             * 1. Excel numeric (45123)
             */
            if (is_numeric($value)) {
                // dd($value);
                return ExcelDate::excelToDateTimeObject($value)
                    ->format('Y-m-d');
            }

            /**
             * 2. Year only (1998)
             */
            if (preg_match('/^\d{4}$/', $value)) {
                return $value . '-01-01';
            }

            /**
             * 3. Known formats (STRICT - no guessing)
             */
            $formats = [
                'd/m/Y',
                'd-m-Y',
                'Y-m-d',
            ];

            foreach ($formats as $format) {
                try {
                    return Carbon::createFromFormat($format, $value)
                        ->format('Y-m-d');
                } catch (\Exception $e) {
                    continue;
                }
            }

            /**
             * FAIL
             */
            throw new \Exception();
        } catch (\Exception $e) {

            $field = $column ?? 'date';

            throw new \Exception("$field: Invalid date format");
        }
    }

    /**
     * =========================
     * 📦 ARRAY / JSON
     * =========================
     */
    protected function parseArray($value)
    {
        if (is_array($value)) {
            return $value;
        }

        $value = trim((string) $value);

        // JSON string
        json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return json_decode($value, true);
        }

        // CSV string
        return array_values(
            array_filter(
                array_map('trim', explode(',', $value))
            )
        );
    }

    /**
     * =========================
     * 🔘 BOOLEAN
     * =========================
     */
    protected function parseBoolean($value)
    {
        $v = strtolower(trim((string) $value));

        return match ($v) {
            '1', 'true', 'yes', 'y', 'x' => true,
            '0', 'false', 'no', 'n', '' => false,
            default => throw new \Exception("Invalid boolean"),
        };
    }

    /**
     * =========================
     * 📤 FORMAT DATE OUTPUT
     * =========================
     */
    protected function formatDate($value)
    {
        try {
            return Carbon::parse($value)->format('d/m/Y');
        } catch (\Exception $e) {
            return $value;
        }
    }

    /**
     * =========================
     * 🧹 CLEAN STRING / PRIMITIVE
     * =========================
     */
    protected function cleanPrimitive($value)
    {
        if (!is_string($value)) {
            return $value;
        }

        return trim(
            preg_replace('/[\x00-\x1F\x7F]/u', '', $value)
        );
    }
}
