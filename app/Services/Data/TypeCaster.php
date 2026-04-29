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
    public function castInput(?string $type, $value, ?string $column = null)
    {
        if ($value === null || $value === '') {
            return null;
        }

        return match ($type) {

            'date', 'datetime' => $this->parseDate($value, $column),

            'array', 'json' => $this->parseArray($value),

            'boolean' => $this->parseBoolean($value),

            default => $this->cleanPrimitive($value),
        };
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

            $value = trim((string) $value);

            /**
             * 1. Excel numeric (45123)
             */
            if (is_numeric($value) && strlen($value) > 5) {
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