# atwx/silverstripe-zugferd

Embeds ZUGFeRD / Factur-X / XRechnung XML into a PDF and turns it into a PDF/A-3
hybrid e-invoice (via `horstoeko/zugferd`'s `ZugferdDocumentPdfMerger`). Works with
PDFs from any source – mPDF, headless Chrome, wkhtmltopdf, uploads.

Building the XML stays in your project (fill a `ZugferdDocumentBuilder` from your
invoice model); this module does the embedding and its configuration.

```php
use Atwx\Zugferd\ZugferdPdf;

$pdf = ZugferdPdf::create()->embed($pdf, $builder);   // builder, XML string or XML file path
```

## With atwx/silverstripe-mpdf

When [atwx/silverstripe-mpdf](https://github.com/atwx/silverstripe-mpdf) is installed,
its renderer embeds the e-invoice by itself:

```php
$builder = ZugferdDocumentBuilder::createNew(ZugferdProfiles::PROFILE_XRECHNUNG_3);
// … fill the builder …

$pdf = Injector::inst()->get(PdfRenderer::class)->renderTemplate('Invoice_pdf', $invoice, [
    'profile' => 'invoice',
    'zugferd' => $builder,           // or an XML string / XML file path; null = plain PDF
    'zugferd_options' => [],         // per-call overrides of the config below
]);
```

## Configuration

```yaml
Atwx\Zugferd\ZugferdPdf:
  creator_tool: 'My App'          # added to the PDF creator
  relationship_type: ''           # Data (default) | Alternative | Source
  deterministic: false            # reproducible output, e.g. for tests
  show_attachment_pane: null      # open the attachment pane in viewers
  meta_templates:                 # sprintf templates filled from the XML
    title: ''
    author: ''
    subject: ''
    keywords: ''
```

Extension hook on `ZugferdPdf`: `updateMerger($merger, $options)`.

## Tests

```bash
SS_PHPUNIT_FLUSH=1 php phpunit.phar --configuration phpunit.xml.dist
```
