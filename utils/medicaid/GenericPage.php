<?php

declare(strict_types=1);

namespace app\utils\medicaid;

use DOMNameSpaceNode;
use DOMNode;

class GenericPage
{

    public readonly string $action;
    public readonly string $method;
    public readonly array $fields;

    protected DomParser $dom;

    public function __construct(public string $content)
    {
        $this->dom = new DomParser($content);

        $form = $this->dom->findFormFields();

        $this->action = $form['action'];
        $this->method = $form['method'];
        $this->fields = $form['fields'];
    }

    public function firstForm(): DOMNode|DOMNameSpaceNode|null
    {
        return $this->dom->firstForm();
    }

    public function isEligibilityPage(): bool
    {
        return $this->dom->isEligibilityPage();
    }

    public function isRecipientProcedureLookupPage(): bool
    {
        return $this->dom->isRecipientProcedureLookupPage();
    }

}