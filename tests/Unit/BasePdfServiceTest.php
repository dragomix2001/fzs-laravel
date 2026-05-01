<?php

namespace Tests\Unit;

use App\Services\BasePdfService;
use Elibyy\TCPDF\TCPDF;
use Illuminate\Support\Facades\View;
use Mockery;
use PDF;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BasePdfServiceTest extends TestCase
{
    #[Test]
    public function base_pdf_service_can_be_instantiated(): void
    {
        $service = new BasePdfService;
        $this->assertInstanceOf(BasePdfService::class, $service);
    }

    #[Test]
    public function protected_methods_render_and_generate_pdf_are_covered(): void
    {
        $service = new class extends BasePdfService
        {
            public ?object $fakePdf = null;

            public function renderPublic(string $view, array $data, string $title): void
            {
                $this->renderPdf($view, $data, $title);
            }

            public function settingsPublic(): array
            {
                return $this->getPdfSettings();
            }

            public function createPublic(): TCPDF
            {
                return $this->createPdf();
            }

            public function generatePublic(string $view, array $data, string $title, string $filename): void
            {
                $this->generatePdf($view, $data, $title, $filename);
            }

            protected function createPdf()
            {
                if ($this->fakePdf !== null) {
                    return $this->fakePdf;
                }

                return parent::createPdf();
            }
        };

        $viewMock = Mockery::mock();
        $viewMock->shouldReceive('with')->once()->andReturnSelf();
        $viewMock->shouldReceive('render')->once()->andReturn('<p>rendered</p>');
        View::shouldReceive('make')->once()->with('pdf.view')->andReturn($viewMock);

        PDF::shouldReceive('SetAutoPageBreak')->once()->with(true, 5);
        PDF::shouldReceive('SetTitle')->once()->with('Title');
        PDF::shouldReceive('AddPage')->once();
        PDF::shouldReceive('SetFont')->once()->with('dejavusans', '', 10);
        PDF::shouldReceive('WriteHtml')->once()->with('<p>rendered</p>', true);
        PDF::shouldReceive('Output')->once()->with('Title.pdf');

        $service->renderPublic('pdf.view', ['a' => 1], 'Title');

        $settings = $service->settingsPublic();
        $this->assertIsArray($settings);
        $this->assertArrayHasKey('page_format', $settings);

        $pdf = $service->createPublic();
        $this->assertInstanceOf(TCPDF::class, $pdf);

        $renderMock = Mockery::mock();
        $renderMock->shouldReceive('render')->once()->andReturn('<p>gen</p>');
        View::shouldReceive('make')->once()->with('pdf.gen', ['x' => 1])->andReturn($renderMock);

        $fakePdf = new class
        {
            public array $calls = [];

            public function SetTitle(string $title): void
            {
                $this->calls[] = ['SetTitle', $title];
            }

            public function AddPage(): void
            {
                $this->calls[] = ['AddPage'];
            }

            public function SetFont(string $font, string $style, int $size): void
            {
                $this->calls[] = ['SetFont', $font, $style, $size];
            }

            public function WriteHtml(string $contents): void
            {
                $this->calls[] = ['WriteHtml', $contents];
            }

            public function Output(string $filename): void
            {
                $this->calls[] = ['Output', $filename];
            }
        };

        $service->fakePdf = $fakePdf;
        $service->generatePublic('pdf.gen', ['x' => 1], 'GenTitle', 'file.pdf');

        $this->assertNotEmpty($fakePdf->calls);
    }
}
