<?php
/**
 * barcode_helper.php
 * Fungsi bareng buat generate 1 gambar barcode (template + QR + text).
 * Dipake sama cetak_barcode.php / cetak_barcode_eyewash.php (single preview)
 * DAN proses_unduh_barcode_word.php (multi, disusun ke Word).
 *
 * Taruh file ini satu folder sama cetak_barcode.php (mis. folder /proses atau /barcode)
 */

if (!function_exists('generateBarcodeImage')) {

    /**
     * @param string $type  'lampu' | 'eyewash' | 'p3k'
     * @param array  $data  row dari master_lampu / master_eyewash / master_p3k (harus ada 'id','code','lokasi')
     * @return string       path file PNG hasil generate (temp file, hapus sendiri setelah dipakai)
     */
    function generateBarcodeImage($type, $data)
    {
        include_once __DIR__ . '/../phpqrcode/qrlib.php';

        $templates = [
            'lampu'   => __DIR__ . '/../foto/template_barcode.png',
            'eyewash' => __DIR__ . '/../foto/template_barcode_eyewash.png',
            'p3k'     => __DIR__ . '/../foto/template_barcode_p3k.png',
        ];
        $templatePath = $templates[$type] ?? $templates['lampu'];

        if (!file_exists($templatePath)) {
            throw new Exception("Template barcode tidak ditemukan: $templatePath");
        }

        $tempDir = __DIR__ . '/../proses/temp_qr/';
        if (!file_exists($tempDir)) mkdir($tempDir, 0755, true);

        $uniq = uniqid('', true);
        $qrContent = $data['code'];
        $qrFile = $tempDir . 'qr_' . $type . '_' . $data['id'] . '_' . $uniq . '.png';

        QRcode::png($qrContent, $qrFile, QR_ECLEVEL_Q, 20, 1);

        $sourceImg = imagecreatefrompng($templatePath);
        $qrImg = imagecreatefrompng($qrFile);

        $newQrWidth = 520;
        $newQrHeight = 520;
        $qrX = 1060;
        $qrY = 460;

        imagecopyresampled($sourceImg, $qrImg, $qrX, $qrY, 0, 0, $newQrWidth, $newQrHeight, imagesx($qrImg), imagesy($qrImg));

        $textColor = imagecolorallocate($sourceImg, 0, 0, 0);
        $fontPath = __DIR__ . '/../fonts/Poppins-Bold.ttf';

        if (file_exists($fontPath)) {
            imagettftext($sourceImg, 35, 0, 460, 558, $textColor, $fontPath, $data['code']);

            $textLokasi = $data['lokasi'];
            $fontSize = 25;
            $startX = 460;
            $startY = 700;
            $lineHeight = 50;
            $maxWidth = 300;

            $words = explode(' ', $textLokasi);
            $currentLine = '';

            foreach ($words as $word) {
                $testLine = $currentLine . ($currentLine === '' ? '' : ' ') . $word;
                $box = imagettfbbox($fontSize, 0, $fontPath, $testLine);
                $textWidth = abs($box[2] - $box[0]);

                if ($textWidth > $maxWidth && $currentLine !== '') {
                    imagettftext($sourceImg, $fontSize, 0, $startX, $startY, $textColor, $fontPath, $currentLine);
                    $currentLine = $word;
                    $startY += $lineHeight;
                } else {
                    $currentLine = $testLine;
                }
            }
            if ($currentLine !== '') {
                imagettftext($sourceImg, $fontSize, 0, $startX, $startY, $textColor, $fontPath, $currentLine);
            }
        } else {
            imagestring($sourceImg, 5, 560, 550, $data['code'], $textColor);
            imagestring($sourceImg, 5, 560, 730, $data['lokasi'], $textColor);
        }

        $outFile = $tempDir . 'label_' . $type . '_' . $data['id'] . '_' . $uniq . '.png';
        imagepng($sourceImg, $outFile, 0);

        imagedestroy($sourceImg);
        imagedestroy($qrImg);
        if (file_exists($qrFile)) unlink($qrFile);

        return $outFile;
    }
}
