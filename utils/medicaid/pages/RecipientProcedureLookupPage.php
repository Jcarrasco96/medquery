<?php

declare(strict_types=1);

namespace app\utils\medicaid\pages;

use app\core\exceptions\BadRequestHttpException;
use app\models\ProcedureLookupResult;
use app\utils\medicaid\DomParser;
use app\utils\medicaid\GenericPage;
use app\utils\medicaid\HttpClient;
use app\utils\medicaid\PageAnalyzer;

final class RecipientProcedureLookupPage extends GenericPage
{

    private const array ROW_CLASSES = [
        'iC_DataListItem',
        'iC_DataListAlternateItem',
    ];

    /**
     * @throws BadRequestHttpException
     */
    private function loadFields(string $medicaid_id, string $procedure, string $fromDate, string $toDate)
    {
        $fields = $this->fields;

        $claimTypeField = $this->dom->findInputName('$ClaimType$mb_ClaimType');
        $medicaidField = $this->dom->findInputName('$ds_MedicaidID$mb_ds_MedicaidID');
        $procedureField = $this->dom->findInputName('$Proc$ds_Proc$mb_ds_Proc');
        $fromDateField = $this->dom->findInputName('$FDOS$mb_FDOS');
        $toDateField = $this->dom->findInputName('$TDOS$mb_TDOS');

        $eventTarget = $this->dom->findPostBackTarget('_SearchButton');

        $fields['__EVENTTARGET'] = $eventTarget;
        $fields[$claimTypeField] = 'P';
        $fields[$medicaidField] = $medicaid_id;
        $fields[$procedureField] = $procedure;
        $fields[$fromDateField] = $fromDate;
        $fields[$toDateField] = $toDate;

        if (empty($fields['__EVENTTARGET'])) {
            throw new BadRequestHttpException('Field "__EVENTTARGET" not found.');
        }

        return $fields;
    }

    /**
     * @throws BadRequestHttpException
     */
    public function postSearch(HttpClient $client, string $medicaid, string $procedure, string $fromDate, ?string $toDate = null): RecipientProcedureLookupPage
    {
        $fields = $this->loadFields($medicaid, $procedure, $fromDate, $toDate);

        $url = $client->url();

        $this->content = $client->post(PageAnalyzer::absoluteUrl($this->action, $url), $fields, $url);

        return $this;
    }

    /**
     * @return ProcedureLookupResult[]
     */
    public function parse(): array
    {
        $this->dom = new DomParser($this->content);

        $auxProcedures = $this->dom->findCellsByTableId('dnn_ClaimSearchRecipProcedurePage_SearchPage_Datalist', self::ROW_CLASSES);

        return $this->normalizeProcedures($auxProcedures);
    }

    /**
     * @param array $listProcedure
     * @return ProcedureLookupResult[]
     */
    private function normalizeProcedures(array $listProcedure): array
    {
        return array_map(static function (array $row): ProcedureLookupResult {
            return new ProcedureLookupResult(
                intval($row[0]),
                $row[1] ?? null,
                $row[2] ?? null,
                $row[3] ?? null,
                $row[4] ?? null,
                $row[5] ?? null,
                $row[6] ?? null,
                $row[7] ?? null,
                intval($row[8]),
            );
        }, $listProcedure);
    }

}