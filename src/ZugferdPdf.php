<?php

namespace Atwx\Zugferd;

use horstoeko\zugferd\ZugferdDocumentBuilder;
use horstoeko\zugferd\ZugferdDocumentPdfMerger;
use InvalidArgumentException;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Injector\Injectable;

/**
 * Embeds ZUGFeRD / Factur-X / XRechnung XML into a PDF, producing a PDF/A-3
 * hybrid e-invoice (via horstoeko/zugferd's ZugferdDocumentPdfMerger).
 *
 * Works on any PDF data, whichever tool produced it (mPDF, headless Chrome,
 * wkhtmltopdf, uploads). With atwx/silverstripe-mpdf installed it is applied
 * automatically when the renderer gets the option `zugferd`.
 */
class ZugferdPdf
{
    use Configurable;
    use Extensible;
    use Injectable;

    /**
     * Added to the PDF's creator tool, e.g. "Intranet".
     */
    private static string $creator_tool = '';

    /**
     * AFRelationship of the XML attachment: Data, Alternative or Source.
     * Empty keeps the library default (Data).
     */
    private static string $relationship_type = '';

    /**
     * Reproducible output (no random IDs / current timestamps), handy for tests.
     */
    private static bool $deterministic = false;

    /**
     * Whether PDF viewers open the attachment pane; null keeps the library default.
     */
    private static ?bool $show_attachment_pane = null;

    /**
     * sprintf templates for the PDF metadata, filled by the library with data
     * from the XML; empty keeps the library defaults.
     */
    private static array $meta_templates = [
        'title' => '',
        'author' => '',
        'subject' => '',
        'keywords' => '',
    ];

    /**
     * @param string|ZugferdDocumentBuilder $xml XML string, XML file path or document builder
     * @param array $options overrides for the config keys above
     * @return string PDF/A-3 data with the embedded XML
     */
    public function embed(string $pdf, string|ZugferdDocumentBuilder $xml, array $options = []): string
    {
        if ($xml instanceof ZugferdDocumentBuilder) {
            $xml = $xml->getContent();
        }
        if (trim($xml) === '') {
            throw new InvalidArgumentException('Empty ZUGFeRD XML');
        }

        $get = fn(string $key) => array_key_exists($key, $options) ? $options[$key] : static::config()->get($key);

        $merger = new ZugferdDocumentPdfMerger($xml, $pdf);

        if ($creator = $get('creator_tool')) {
            $merger->setAdditionalCreatorTool($creator);
        }
        if ($relationship = $get('relationship_type')) {
            $merger->setAttachmentRelationshipType($relationship);
        }
        if ($get('deterministic')) {
            $merger->setDeterministicModeEnabled(true);
        }
        $pane = $get('show_attachment_pane');
        if ($pane !== null) {
            $merger->setAttachmentPaneVisibility((bool) $pane);
        }
        $templates = array_replace(static::config()->get('meta_templates') ?: [], $options['meta_templates'] ?? []);
        foreach (['title' => 'setTitleTemplate', 'author' => 'setAuthorTemplate',
                     'subject' => 'setSubjectTemplate', 'keywords' => 'setKeywordTemplate'] as $key => $method) {
            if (!empty($templates[$key])) {
                $merger->$method($templates[$key]);
            }
        }

        $this->extend('updateMerger', $merger, $options);

        return $merger->generateDocument()->downloadString();
    }
}
