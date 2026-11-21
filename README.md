# atwx/silverstripe-mpdf-zugferd

Add-on for [atwx/silverstripe-mpdf](https://github.com/atwx/silverstripe-mpdf): embeds ZUGFeRD / Factur-X /
XRechnung XML into the rendered PDF and turns it into a PDF/A-3 hybrid e-invoice
(via `horstoeko/zugferd`'s `ZugferdDocumentPdfMerger`).

```php
$builder = ZugferdDocumentBuilder::createNew(ZugferdProfiles::PROFILE_XRECHNUNG_3);
// … fill the builder …

$pdf = Injector::inst()->get(PdfRenderer::class)->renderTemplate('Invoice_pdf', $invoice, [
    'profile' => 'invoice',
    'zugferd' => $builder,           // or an XML string / XML file path; null = plain PDF
    'zugferd_options' => [],         // per-call overrides of the config below
]);
```

Without the renderer, any PDF data works: `ZugferdPdf::create()->embed($pdf, $xml)`.

## Configuration

```yaml
Atwx\MpdfZugferd\ZugferdPdf:
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
