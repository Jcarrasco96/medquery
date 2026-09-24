<?php

namespace app\utils\medicaid;

use DOMDocument;
use DOMElement;
use DOMNameSpaceNode;
use DOMNode;
use DOMXPath;
use RuntimeException;

class DomParser
{

    private DOMXPath $xpath;

    public function __construct(string $html)
    {
        libxml_use_internal_errors(true);

        $document = new DOMDocument();

        if (!$document->loadHTML($html)) {
            throw new RuntimeException('Unable to parse page.');
        }

        $this->xpath = new DOMXPath($document);
    }

    public function findFormFields(): array
    {
        $form = $this->xpath->query('//form')->item(0);

        if (!$form instanceof DOMElement) {
            throw new RuntimeException('Form not found.');
        }

        $action = $form->getAttribute('action');
        $method = strtoupper($form->getAttribute('method') ?: 'GET');

        $fields = [];

        foreach ($this->xpath->query('.//input', $form) as $input) {
            if (!$input instanceof DOMElement) {
                continue;
            }

            $name = $input->getAttribute('name');

            if ($name === '') {
                continue;
            }

            $fields[$name] = $input->getAttribute('value');
        }

        return [
            'action' => $action,
            'method' => $method,
            'fields' => $fields,
        ];
    }

    public function findInputName(string $suffix): ?string
    {
        $nodes = $this->xpath->query(
            "//input[substring(@name, string-length(@name) - string-length('" . $suffix . "') + 1) = '" . $suffix . "']"
        );

        if ($nodes->length === 0) {
            return null;
        }

        $node = $nodes->item(0);

        if (!$node instanceof DOMElement) {
            throw new RuntimeException('Input not found.');
        }

        return $node->getAttribute('name');
    }

    public function findInputValueBySuffixId(string $suffix): ?string
    {
        $nodes = $this->xpath->query(
            "//input[
                starts-with(@id, 'dnn_RecipSearchPage_SearchPage_ClientInformationPanel_')
                and
                substring(@id, string-length(@id) - string-length('" . $suffix . "') + 1) = '" . $suffix . "']"
        );

        if ($nodes->length === 0) {
            return null;
        }

        $node = $nodes->item(0);

        if (!$node instanceof DOMElement) {
            throw new RuntimeException('Input not found.');
        }

        return $node->getAttribute('value');
    }

    public function findPostBackTarget(string $idSuffix): ?string
    {
        $nodes = $this->xpath->query("//*[self::a and contains(@id, '$idSuffix')]");

        if ($nodes->length === 0) {
            return null;
        }

        $node = $nodes->item(0);

        if (!$node instanceof DOMElement) {
            throw new RuntimeException('Target not found.');
        }

        $href = $node->getAttribute('href');

        if (preg_match("/__doPostBack\('([^']*)'/", $href, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public function findCellsByTableId(string $tableId, array $requiredClasses): array
    {
        $table = $this->xpath->query('//table[@id="' . $tableId . '"]')->item(0);

        if (!$table instanceof DOMElement) {
            return [];
        }

        $rows = [];

        foreach ($this->xpath->query('.//tr', $table) as $tr) {
            if (!$tr instanceof DOMElement) {
                continue;
            }

            $class = $tr->getAttribute('class');

            if (!self::hasRequiredClass($class, $requiredClasses)) {
                continue;
            }

            $cells = [];

            foreach ($this->xpath->query('./th|./td', $tr) as $cell) {
                if (!$cell instanceof DOMElement) {
                    continue;
                }

                $cells[] = trim(preg_replace('/\s+/', ' ', $cell->textContent) ?? '');
            }

            $rows[] = $cells;
        }

        return $rows;
    }

    public function isEligibilityPage(): bool
    {
        foreach ($this->xpath->query('//h2') as $heading) {
            if (!$heading instanceof DOMElement) {
                continue;
            }

            if (trim($heading->textContent) === 'Eligibility') {
                return true;
            }
        }

        return false;
    }

    public function firstForm(): DOMNode|DOMNameSpaceNode|null
    {
        return $this->xpath->query('//form')->item(0);
    }

    public function images(): array
    {
        $images = [];

        foreach ($this->xpath->query('//img') as $image) {
            if (!$image instanceof DOMElement) {
                continue;
            }

            $src = $image->getAttribute('src');

            if ($src !== '') {
                $images[] = $src;
            }
        }

        return $images;
    }

    private static function hasRequiredClass(string $class, array $requiredClasses): bool
    {
        $classes = preg_split('/\s+/', trim($class));

        if ($classes === false) {
            return false;
        }

        return array_intersect($requiredClasses, $classes) !== [];
    }

    public function findNotEligibleInputValueBySuffixId(string $suffix)
    {
        $nodes = $this->xpath->query(
            "//input[
                starts-with(@id, 'dnn_RecipSearchPage_SearchPage_ClientNotEligiblePanel_')
                and
                substring(@id, string-length(@id) - string-length('" . $suffix . "') + 1) = '" . $suffix . "']"
        );

        if ($nodes->length === 0) {
            return null;
        }

        $node = $nodes->item(0);

        if (!$node instanceof DOMElement) {
            throw new RuntimeException('Input not found.');
        }

        return $node->getAttribute('value');
    }

}