<?php

declare(strict_types=1);

namespace app\utils\medicaid;

use DateTime;
use Exception;

final class EligibilityModel
{

    private const array HMO_TYPES = [
        'Children\'s Medical Services T19 Specialty Plan',
        'Dual-Special Needs Plan',
        'SMMC MMA Capitated',
        'SMMC MMA Child Welfare Capitated',
        'SMMC MMA Specialty Capitated',
    ];

    private const array BENEFITS_PLAN_NOT_ELIGIBLE = [
        'FP: Limited to family planning services',
        'QI1 : Limited to Medicare Part B premium only',
        'QMB : Ltd to Mcare premiums, deductibls and CoIns',
        'SLMB: Limited to Medicare Part B premium only',
    ];

    public array $listBenefitPlan;
    public array $listTPL;
    public array $listManagedCare;

    public ?string $lastName;
    public ?string $firstName;
    public ?string $birthDate;

    public int $age
        {
            get {
                return $this->calculateAge();
            }
        }

    public array $hmo = [];

    public function detectHmo(): void
    {
        foreach ($this->listBenefitPlan as $row) {
            $type = trim($row['0'] ?? '');

            if (count($this->listBenefitPlan) == 1 && in_array($type, self::BENEFITS_PLAN_NOT_ELIGIBLE)) {
                $this->hmo = [
                    'status' => 'CONFIRMED_HMO',
                    'type' => $type,
                    'managed_care' => $type,
                ];
                return;
            }
        }

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

    private function calculateAge(): int
    {
        try {
            return new DateTime($this->birthDate)->diff(new DateTime())->y;
        } catch (Exception) {
            return 0;
        }
    }

}