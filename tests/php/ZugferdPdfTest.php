<?php

namespace Atwx\Zugferd\Tests;

use Atwx\Mpdf\MpdfRenderer;
use Atwx\Zugferd\ZugferdPdf;
use DateTime;
use FPDF;
use horstoeko\zugferd\ZugferdDocumentBuilder;
use horstoeko\zugferd\ZugferdDocumentPdfReader;
use horstoeko\zugferd\ZugferdProfiles;
use SilverStripe\Dev\SapphireTest;

class ZugferdPdfTest extends SapphireTest
{
    protected $usesDatabase = false;

    private function builder(): ZugferdDocumentBuilder
    {
        return ZugferdDocumentBuilder::createNew(ZugferdProfiles::PROFILE_EN16931)
            ->setDocumentInformation('R-2026-1', '380', new DateTime('2026-11-21'), 'EUR')
            ->setDocumentSeller('Test GmbH')
            ->setDocumentBuyer('Kunde AG');
    }

    private function assertInvoiceNumber(string $pdf, string $expected): void
    {
        $document = ZugferdDocumentPdfReader::readAndGuessFromContent($pdf);
        $document->getDocumentInformation($number, $type, $date, $currency, $taxCurrency, $name, $language, $period);
        $this->assertSame($expected, $number);
        $this->assertSame('380', $type);
    }

    public function testEmbedsIntoAnyPdf(): void
    {
        // A PDF from another tool (here FPDF, which horstoeko/zugferd ships with)
        $fpdf = new FPDF();
        $fpdf->AddPage();
        $fpdf->SetFont('Helvetica', '', 12);
        $fpdf->Cell(0, 10, 'Rechnung');
        $plain = $fpdf->Output('S');

        $pdf = ZugferdPdf::create()->embed($plain, $this->builder());

        $this->assertStringStartsWith('%PDF-', $pdf);
        $this->assertInvoiceNumber($pdf, 'R-2026-1');
    }

    public function testEmbedsXmlString(): void
    {
        $fpdf = new FPDF();
        $fpdf->AddPage();
        $pdf = ZugferdPdf::create()->embed($fpdf->Output('S'), $this->builder()->getContent());

        $this->assertInvoiceNumber($pdf, 'R-2026-1');
    }

    public function testMpdfRendererEmbedsXmlWhenOptionIsGiven(): void
    {
        if (!class_exists(MpdfRenderer::class)) {
            $this->markTestSkipped('atwx/silverstripe-mpdf is not installed');
        }
        MpdfRenderer::config()->set('css', []);
        $renderer = MpdfRenderer::create();

        $this->assertNotSame($renderer->renderHtml('<p>Rechnung</p>'), $pdf = $renderer->renderHtml(
            '<p>Rechnung</p>',
            ['zugferd' => $this->builder()]
        ));
        $this->assertInvoiceNumber($pdf, 'R-2026-1');
    }
}
