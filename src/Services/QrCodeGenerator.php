<?php
namespace App\Services;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

class QrCodeGenerator
{

    public function generateQrCode($url)
    {
        $writer = new PngWriter();
        $qrCode = new QrCode($url);
        $qrCode
            ->setEncoding( new Encoding('UTF-8') )
            ->setErrorCorrectionLevel(ErrorCorrectionLevel::High)
            ->setSize(300)
            ->setMargin(10)
            ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->setForegroundColor(new Color(0, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255))
        ;
        
        $result = $writer->write($qrCode)->getDataUri();

        return $result;
    }
}