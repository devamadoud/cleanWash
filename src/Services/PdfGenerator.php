<?php
namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfGenerator
{
    public function generate(string $html): Dompdf
    {
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans')
            ->setDefaultPaperOrientation('portrait')
            ->setIsRemoteEnabled(true)
        ;
        $domPdf = new Dompdf($options);
        $domPdf->loadHtml($html);
        $domPdf->setPaper('A4', 'portrait');
        $domPdf->render();
        return $domPdf;
    }
}