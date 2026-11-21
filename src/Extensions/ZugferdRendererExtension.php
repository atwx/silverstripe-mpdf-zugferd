<?php

namespace Atwx\Zugferd\Extensions;

use Atwx\Zugferd\ZugferdPdf;
use SilverStripe\Core\Extension;

/**
 * Hooks into Atwx\Mpdf\MpdfRenderer: when the option `zugferd` holds XML (or a
 * ZugferdDocumentBuilder), the rendered PDF gets it embedded as e-invoice.
 * Further merger settings can be passed as `zugferd_options`.
 */
class ZugferdRendererExtension extends Extension
{
    protected function updatePdf(string &$pdf, array $options): void
    {
        if (empty($options['zugferd'])) {
            return;
        }
        $pdf = ZugferdPdf::create()->embed($pdf, $options['zugferd'], $options['zugferd_options'] ?? []);
    }
}
