<?php

declare(strict_types=1);

namespace app\utils\medicaid;

use app\core\exceptions\BadRequestHttpException;

final class GenericForm
{

    public string $action;
    public string $method;
    public array $fields;

    private readonly DomParser $dom;

    public function __construct(string $html)
    {
        $this->dom = new DomParser($html);

        $form = $this->dom->findFormFields();

        $this->action = $form['action'];
        $this->method = $form['method'];
        $this->fields = $form['fields'];
    }

    public function eligibilityWithSearchCriteria(string $medicaidId, string $fromDate, ?string $toDate = null): array
    {
        $fields = $this->fields;

        $medicaidField = $this->dom->findInputName('$MedicaidID$mb_MedicaidID');
        $fromDateField = $this->dom->findInputName('$FromDOS$mb_FromDOS');
        $toDateField = $this->dom->findInputName('$ToDOS$mb_ToDOS');

//        if ($medicaidField == null || $fromDateField == null || $toDateField == null) {
//            throw new BadRequestHttpException('Field missing');
//        }

        $eventTarget = $this->dom->findPostBackTarget('SearchButton');

        $fields['__EVENTTARGET'] = $eventTarget;
        $fields[$medicaidField] = $medicaidId;
        $fields[$fromDateField] = $fromDate;

        if ($toDate) {
            $fields[$toDateField] = $toDate;
        }

        return $fields;
    }

    public function login(string $username, string $password): array
    {
        $fields = $this->fields;

        $fields['UserName'] = $username;
        $fields['Password'] = $password;

        return $fields;
    }

}