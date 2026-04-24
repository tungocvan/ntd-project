<?php

namespace Modules\Admission\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Modules\Admission\Models\AdmissionApplication;
use Modules\Admission\Services\AdmissionService;
use Symfony\Component\Process\Process;
use Illuminate\Support\Str;

class GenerateAdmissionPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $id;

    public function __construct($id)
    {
        $this->id = $id;
    }



    public function handle(AdmissionService $service)
    {
        $app = AdmissionApplication::find($this->id);
        if (!$app) return;

        // ❗ chỉ chạy khi approved
        if ($app->status !== 'approved') return;

        try {
            // 🔥 data
            $data = $service->getDataForTemplate($this->id);

            // 🔥 filename (safe)
            $name = 'Don_' . Str::slug($data['HoVaTenHocSinh'], '_');

            // 🔥 PATH CHUẨN
            $relativeDir = 'admission/';
            $fullDir = storage_path('app/' . $relativeDir);

            // 🔥 ensure folder
            if (!is_dir($fullDir)) {
                mkdir($fullDir, 0775, true);
            }

            chmod($fullDir, 0775);

            // 🔥 file paths
            $wordRelative = $relativeDir . $name . '.docx';
            $pdfRelative  = $relativeDir . $name . '.pdf';

            $wordFull = $fullDir . $name . '.docx';
            $pdfFull  = $fullDir . $name . '.pdf';

            // 🔥 nếu đã có PDF → chỉ update DB và thoát
            if (file_exists($pdfFull)) {
                $app->updateQuietly([
                    'pdf_path'  => $pdfRelative,
                    'word_path' => $wordRelative,
                ]);
                return;
            }

            // =========================
            // 📝 TẠO WORD
            // =========================
            if (!file_exists($wordFull)) {

                $template = storage_path('app/templates/application.docx');

                if (!file_exists($template)) {
                    throw new \Exception('Template không tồn tại');
                }

                $tp = new \PhpOffice\PhpWord\TemplateProcessor($template);

                foreach ($data as $key => $value) {
                    $tp->setValue($key, $value);
                }

                $tp->saveAs($wordFull);

                // 🔥 permission
                chmod($wordFull, 0664);
            }

            // =========================
            // 📄 CONVERT PDF
            // =========================
            $process = new Process([
                'libreoffice',
                '--headless',
                '--convert-to',
                'pdf',
                '--outdir',
                $fullDir,
                $wordFull
            ]);

            $process->setTimeout(120);
            $process->setEnv(['HOME' => $fullDir]);

            $process->run();

            if (!$process->isSuccessful()) {
                throw new \Exception($process->getErrorOutput());
            }

            // 🔥 check PDF
            if (!file_exists($pdfFull)) {
                throw new \Exception('Không tạo được PDF');
            }

            // 🔥 permission
            chmod($pdfFull, 0664);

            // =========================
            // 💾 UPDATE DB (CHUẨN)
            // =========================
            $app->updateQuietly([
                'pdf_path'  => $pdfRelative,
                'word_path' => $wordRelative,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Generate PDF lỗi', [
                'id' => $this->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
