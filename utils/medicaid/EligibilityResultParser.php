<?php

declare(strict_types=1);

namespace app\utils\medicaid;

use DateTime;

final class EligibilityResultParser
{

    private const array HMO_TYPES = [
        'Dual-Special Needs Plan',
        'SMMC MMA Capitated',
        'SMMC MMA Child Welfare Capitated',
        'SMMC MMA Specialty Capitated',
    ];

    private const array ROW_CLASSES = [
        'iC_DataListItem',
        'iC_DataListAlternateItem',
    ];

    public readonly array $listBenefitPlan;
    public readonly array $listTPL;
    public readonly array $listManagedCare;

    public readonly ?string $lastName;
    public readonly ?string $firstName;
    public readonly ?string $birthDate;

    public readonly int $age;

    public array $hmo = [];

    private readonly DomParser $dom;

    public function __construct(string $html)
    {
        $this->dom = new DomParser($html);

        if (str_contains($html, "Recipient is not eligible for the dates of service requested.")) {
            $this->listBenefitPlan = [];
            $this->listTPL = [];
            $this->listManagedCare = [];

            $this->lastName = $this->dom->findNotEligibleInputValueBySuffixId('_LastName_mb_LastName');
            $this->firstName = $this->dom->findNotEligibleInputValueBySuffixId('_FirstName_mb_FirstName');
            $this->birthDate = $this->dom->findNotEligibleInputValueBySuffixId('_BirthDate_mb_BirthDate');

            $this->age = $this->calculateAge($this->birthDate);

            $this->hmo = [
                'status' => 'NO_MEDICAID',
                'type' => null,
                'managed_care' => $this->listManagedCare,
            ];

            return;
        }

        $this->listBenefitPlan = $this->dom->findCellsByTableId('dnn_RecipSearchPage_SearchPage_DatalistBenefitPlan', self::ROW_CLASSES);
        $this->listTPL = $this->dom->findCellsByTableId('dnn_RecipSearchPage_SearchPage_DataListTPL', self::ROW_CLASSES);
        $this->listManagedCare = $this->dom->findCellsByTableId('dnn_RecipSearchPage_SearchPage_DataListManagedCare', self::ROW_CLASSES);

        $this->lastName = $this->dom->findInputValueBySuffixId('_LastName_mb_LastName');
        $this->firstName = $this->dom->findInputValueBySuffixId('_FirstName_mb_FirstName');
        $this->birthDate = $this->dom->findInputValueBySuffixId('_BirthDate_mb_BirthDate');

        $this->age = $this->calculateAge($this->birthDate);

        $this->detectHmo();
    }

    private function detectHmo(): void
    {
//        foreach ($this->listBenefitPlan as $row) {
//            $type = trim($row['0'] ?? '');
//
//            if ($type == 'FP: Limited to family planning services' && count($this->listBenefitPlan) == 1) {
//                $this->hmo = [
//                    'status' => 'CONFIRMED_HMO',
//                    'type' => $type,
//                    'managed_care' => $type,
//                ];
//                return;
//            }
//        }

        foreach ($this->listManagedCare as $row) {
            $type = trim($row[2] ?? '');

            if (in_array($type, self::HMO_TYPES, true)) {
                $this->hmo = [
                    'status' => 'CONFIRMED_HMO',
                    'type' => $type,
                    'managed_care' => trim($row[0] ?? ''),
                ];
                return;
            }
        }

        $this->hmo = [
            'status' => 'MANUAL_REVIEW',
            'type' => null,
            'managed_care' => $this->listManagedCare,
        ];
    }

    /**
     * @throws \DateMalformedStringException
     */
    private function calculateAge(string $birthDate, ?string $referenceDate = null): int
    {
        $birth = new DateTime($birthDate);
        $reference = $referenceDate ? new DateTime($referenceDate) : new DateTime();

        return $birth->diff($reference)->y;
    }

}