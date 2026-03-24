<?php

namespace App\Services;

use Elibyy\TCPDF\TCPDF;
use PDF;
use View;

class BasePdfService
{
    protected function renderPdf(string $view, array $data, string $title): void
    {
        $view = View::make($view)->with($data);
        $contents = $view->render();

        PDF::SetAutoPageBreak(true, 5);
        PDF::SetTitle($title);
        PDF::AddPage();
        PDF::SetFont('dejavusans', '', 10);
        PDF::WriteHtml($contents, true);
        PDF::Output($title.'.pdf');
    }

    protected function getPdfSettings(): array
    {
        return \Config::get('tcpdf');
    }

    protected function createPdf()
    {
        $settings = $this->getPdfSettings();

        return new TCPDF([
            $settings['page_orientation'],
            $settings['page_units'],
            $settings['page_format'],
            true,
            'UTF-8',
            false,
        ], 'tcpdf');
    }
}
