<?php

namespace App\Services;

use Symfony\Component\Process\Process;

class DocumentConverterService
{
    /**
     * Convert Word/Excel -> PDF
     */
    public function toPdf(string $inputPath, string $outputDir): string
    {
        $this->validateInput($inputPath);
        $this->ensureDirectory($outputDir);

        $process = $this->buildProcess($inputPath, $outputDir);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new \Exception(
                'Convert PDF lỗi: ' . $process->getErrorOutput()
            );
        }

        $pdfPath = $this->getOutputPdfPath($inputPath, $outputDir);

        if (!file_exists($pdfPath)) {
            throw new \Exception('Không tạo được file PDF');
        }

        chmod($pdfPath, 0664);

        return $pdfPath;
    }

    // =========================
    // 🔒 INTERNAL METHODS
    // =========================

    protected function validateInput(string $inputPath): void
    {
        if (!file_exists($inputPath)) {
            throw new \Exception("File không tồn tại: {$inputPath}");
        }

        $ext = strtolower(pathinfo($inputPath, PATHINFO_EXTENSION));

        if (!in_array($ext, ['doc', 'docx', 'xls', 'xlsx', 'ods'])) {
            throw new \Exception("Format không hỗ trợ: {$ext}");
        }
    }

    protected function ensureDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        chmod($dir, 0775);
    }

    protected function buildProcess(string $inputPath, string $outputDir): Process
    {
        $process = new Process([
            'libreoffice',
            '--headless',
            '--convert-to',
            'pdf',
            '--outdir',
            $outputDir,
            $inputPath
        ]);

        $process->setTimeout(120);

        // 🔥 tránh lỗi permission khi chạy queue
        $process->setEnv([
            'HOME' => $outputDir
        ]);

        return $process;
    }

    protected function getOutputPdfPath(string $inputPath, string $outputDir): string
    {
        $filename = pathinfo($inputPath, PATHINFO_FILENAME);

        return rtrim($outputDir, '/') . '/' . $filename . '.pdf';
    }
}