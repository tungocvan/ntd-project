<?php

namespace Modules\Admission\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Modules\Admission\Models\AdmissionApplication;
use Modules\Admission\Services\AdmissionService;
use App\Services\DocumentConverterService;
//use Symfony\Component\Process\Process;
use Illuminate\Support\Str;

class GenerateAdmissionPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $id;

    public function __construct($id)
    {
        $this->id = $id;
    }



    public function handle(
    AdmissionService $service,
    DocumentConverterService $converter
) {
    $app = AdmissionApplication::find($this->id);

    if (!$app) {
        return;
    }

    // ❗ chỉ xử lý khi đã duyệt
    if ($app->status !== 'approved') {
        return;
    }

    try {
        // =========================
        // 🔥 DATA
        // =========================
        $data = $service->getDataForTemplate($this->id);

        $name = 'Don_' . \Str::slug($data['HoVaTenHocSinh'] ?? 'unknown', '_');

        $relativeDir = 'admission/';
        $fullDir = storage_path('app/' . $relativeDir);

        // =========================
        // 📁 ENSURE FOLDER
        // =========================
        if (!is_dir($fullDir)) {
            mkdir($fullDir, 0775, true);
        }

        chmod($fullDir, 0775);

        // =========================
        // 📄 PATH
        // =========================
        $wordRelative = $relativeDir . $name . '.docx';
        $pdfRelative  = $relativeDir . $name . '.pdf';

        $wordFull = $fullDir . $name . '.docx';
        $pdfFull  = $fullDir . $name . '.pdf';

        // =========================
        // 🚀 Nếu PDF đã tồn tại → skip
        // =========================
        if (file_exists($pdfFull)) {
            $app->updateQuietly([
                'pdf_path'  => $pdfRelative,
                'word_path' => $wordRelative,
            ]);
            return;
        }

        // =========================
        // 📝 GENERATE WORD
        // =========================
        if (!file_exists($wordFull)) {

            $template = storage_path('app/templates/application.docx');

            if (!file_exists($template)) {
                throw new \Exception('Template không tồn tại');
            }

            $tp = new \PhpOffice\PhpWord\TemplateProcessor($template);

            foreach ($data as $key => $value) {
                $tp->setValue($key, $value ?? '');
            }

            $tp->saveAs($wordFull);

            chmod($wordFull, 0664);
        }

        // =========================
        // 📄 CONVERT PDF (SERVICE)
        // =========================
       $pdfFull = $converter->toPdf($wordFull, $fullDir);

        // =========================
        // 🔍 VERIFY PDF
        // =========================
        if (!file_exists($pdfFull)) {
            throw new \Exception('Convert xong nhưng không thấy file PDF');
        }

        // =========================
        // 💾 UPDATE DB
        // =========================
        $app->updateQuietly([
            'pdf_path'  => $pdfRelative,
            'word_path' => $wordRelative,
        ]);

    } catch (\Throwable $e) {

        \Log::error('Generate Admission PDF lỗi', [
            'id'    => $this->id,
            'error' => $e->getMessage(),
        ]);
    }
}
}
