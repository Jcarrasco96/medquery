<?php

declare(strict_types=1);

namespace app\utils\medicaid\pages;

use app\core\exceptions\BadRequestHttpException;
use app\utils\medicaid\DomParser;
use app\utils\medicaid\EligibilityModel;
use app\utils\medicaid\GenericPage;
use app\utils\medicaid\HttpClient;
use app\utils\medicaid\PageAnalyzer;
use Masterminds\HTML5;

final class EligibilityPage extends GenericPage
{

    private const array ROW_CLASSES = [
        'iC_DataListItem',
        'iC_DataListAlternateItem',
    ];

    public readonly string $action;
    public readonly string $method;
    public readonly array $fields;

    private function loadFields(string $medicaidId, string $fromDate, ?string $toDate = null): array
    {
        $medicaidField = $this->dom->findInputName('$MedicaidID$mb_MedicaidID');
        $fromDateField = $this->dom->findInputName('$FromDOS$mb_FromDOS');
        $toDateField = $this->dom->findInputName('$ToDOS$mb_ToDOS');

        $eventTarget = $this->dom->findPostBackTarget('SearchButton');

        $fields['__EVENTTARGET'] = $eventTarget;
        $fields[$medicaidField] = $medicaidId;
        $fields[$fromDateField] = $fromDate;

        if ($toDate) {
            $fields[$toDateField] = $toDate;
        }

        return $fields;
    }

    public function postSearch(HttpClient $client, string $medicaid, string $fromDate, ?string $toDate = null): EligibilityPage
    {
        $fields = $this->loadFields($medicaid, $fromDate, $toDate);

        $url = $client->url();

        $this->content = $client->post(PageAnalyzer::absoluteUrl($this->action, $url), $fields, $url);

        return $this;
    }

    /**
     * @throws BadRequestHttpException
     */
    public function parse(): EligibilityModel
    {
        if (str_contains($this->content, "Recipient ID is not valid.")) {
            throw new BadRequestHttpException('Recipient ID is not valid.');
        }

        if (str_contains($this->content, "The from and to dates of service must be in a 12 month window")) {
            throw new BadRequestHttpException('The from and to dates of service must be in a 12 month window.');
        }

        if (str_contains($this->content, "The from and to dates of service must be within the same month")) {
            throw new BadRequestHttpException('The from and to dates of service must be within the same month.');
        }

        if (str_contains($this->content, "From DOS must be less than or equal to To DOS.")) {
            throw new BadRequestHttpException('From DOS must be less than or equal to To DOS.');
        }

        if (str_contains($this->content, "Future eligibility cannot be requested")) {
            throw new BadRequestHttpException('Future eligibility cannot be requested.');
        }

        $this->dom = new DomParser($this->content);

        $model = new EligibilityModel();

        if (str_contains($this->content, "Recipient is not eligible for the dates of service requested.")) {
            $model->listBenefitPlan = [];
            $model->listTPL = [];
            $model->listManagedCare = [];

            $model->lastName = $this->dom->findNotEligibleInputValueBySuffixId('_LastName_mb_LastName');
            $model->firstName = $this->dom->findNotEligibleInputValueBySuffixId('_FirstName_mb_FirstName');
            $model->birthDate = $this->dom->findNotEligibleInputValueBySuffixId('_BirthDate_mb_BirthDate');

            $model->hmo = [
                'status' => 'NO_MEDICAID',
                'type' => null,
                'managed_care' => $model->listManagedCare,
            ];

            return $model;
        }

        $model->listBenefitPlan = $this->dom->findCellsByTableId('dnn_RecipSearchPage_SearchPage_DatalistBenefitPlan', self::ROW_CLASSES);
        $model->listTPL = $this->dom->findCellsByTableId('dnn_RecipSearchPage_SearchPage_DataListTPL', self::ROW_CLASSES);
        $model->listManagedCare = $this->dom->findCellsByTableId('dnn_RecipSearchPage_SearchPage_DataListManagedCare', self::ROW_CLASSES);

        $model->lastName = $this->dom->findInputValueBySuffixId('_LastName_mb_LastName');
        $model->firstName = $this->dom->findInputValueBySuffixId('_FirstName_mb_FirstName');
        $model->birthDate = $this->dom->findInputValueBySuffixId('_BirthDate_mb_BirthDate');

        $model->detectHmo();

        return $model;
    }

    public function eligibilityTable(): array
    {
        $html5 = new HTML5();

        $dom = $html5->loadHTML($this->content);

        $nodes = $dom->getElementsByTagName('*');
        $remove = [];
        $idsToRemove = [
            'dnn_LeftPane',
            'dnn_RightPane',
            'dnn_RecipSearchPage_SearchPage_PanelSpacer',
            'dnn_RecipSearchPage_SearchPage_CriteriaPanel_Help',
            'dnn_RecipSearchPage_SearchPage_CriteriaPanel_Chevron',
            'dnn_RecipSearchPage_SearchPage_ClientInformationPanel_Help',
            'dnn_RecipSearchPage_SearchPage_ClientInformationPanel_Chevron',
        ];
        foreach ($nodes as $node) {
            if ($node->getAttribute('class') === 'iC_SkinNoPrint') {
                $remove[] = $node;
            }

            $id = $node->getAttribute('id');

            if (in_array($id, $idsToRemove)) {
                $remove[] = $node;
            }

            if (str_ends_with($node->getAttribute('id'), '_SearchButton') || str_ends_with($node->getAttribute('id'), '_ClearButton')) {
                $remove[] = $node;
            }

            $attributes = [];
            foreach ($node->attributes as $attribute) {
                if (preg_match('/^on/i', $attribute->name)) {
                    $attributes[] = $attribute->name;
                }
            }

            foreach ($attributes as $attribute) {
                $node->removeAttribute($attribute);
            }

            if (strtolower($node->tagName) === 'input' || strtolower($node->tagName) === 'select') {
                $node->setAttribute('disabled', 'disabled');
            }
            if (strtolower($node->tagName) === 'a') {
                $node->setAttribute('href', '#');
            }
        }
        foreach ($remove as $node) {
            $node->parentNode?->removeChild($node);
        }

        $title = '';
        foreach ($dom->getElementsByTagName('title') as $title) {
            $title = $title->nodeValue;
            break;
        }

        $target = null;
        foreach ($dom->getElementsByTagName('table') as $div) {
            if (str_contains($div->getAttribute('id'), 'ContentArea')) {
                $target = $div;
                break;
            }
        }

        if ($target !== null) {
            $html2 = $html5->saveHTML($target);

            return [$title, $html2];
        }

        return ['', ''];
    }

}