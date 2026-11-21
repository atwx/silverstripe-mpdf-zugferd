<?php

namespace Atwx\MpdfZugferd\Tests;

use Atwx\Mpdf\MpdfRenderer;
use DateTime;
use horstoeko\zugferd\ZugferdDocumentBuilder;
use horstoeko\zugferd\ZugferdDocumentPdfReader;
use horstoeko\zugferd\ZugferdProfiles;
use SilverStripe\Dev\SapphireTest;

class ZugferdPdfTest extends SapphireTest
{
    protected $usesDatabase = false;

    public function testRendererEmbedsXmlWhenOptionIsGiven(): void
    {
        $builder = ZugferdDocumentBuilder::createNew(ZugferdProfiles::PROFILE_EN16931);
        $builder->setDocumentInformation('R-2026-1', '380', new DateTime('2026-11-21'), 'EUR')
            ->setDocumentSeller('Test GmbH')
            ->setDocumentBuyer('Kunde AG');

        MpdfRenderer::config()->set('css', []);
        $renderer = MpdfRenderer::create();
        $plain = $renderer->renderHtml('<p>Rechnung</p>');
        $pdf = $renderer->renderHtml('<p>Rechnung</p>', ['zugferd' => $builder]);

        $this->assertStringStartsWith('%PDF-', $pdf);
        $this->assertNotSame($plain, $pdf);

        $document = ZugferdDocumentPdfReader::readAndGuessFromContent($pdf);
        $document->getDocumentInformation($number, $type, $date, $invoiceCurrency, $taxCurrency, $name, $language, $period);
        $this->assertSame('R-2026-1', $number);
        $this->assertSame('380', $type);
    }
}
