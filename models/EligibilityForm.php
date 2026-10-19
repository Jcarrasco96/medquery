<?php

namespace app\models;

use app\core\Model;
use DateTime;
use DateTimeImmutable;
use stdClass;

final class EligibilityForm extends Model
{

    public string $medicaid_id;
    public string $from_date;
    public ?string $to_date;

    public function load(array|stdClass $data = []): void
    {
        if (is_array($data)) {
            if (isset($data['medicaid_id'])) {
                $this->medicaid_id = trim($data['medicaid_id']);
            }
            if (isset($data['from_date'])) {
                $this->from_date = trim($data['from_date']);
            }
            if (isset($data['to_date'])) {
                $this->to_date = trim($data['to_date']);
            }
            return;
        }

        if ($data instanceof stdClass) {
            $this->medicaid_id = trim($data->medicaid_id);
            $this->from_date = trim($data->from_date);
            $this->to_date = trim($data->to_date);
        }
    }

    public function validate(bool $clearErrors = true): bool
    {
        if ($clearErrors) {
            $this->errors = [];
        }

        if (empty($this->medicaid_id)) {
            $this->addError('medicaid_id', 'Recipient ID is required.');
        }

        if (strlen($this->medicaid_id) != 10) {
            $this->addError('medicaid_id', 'Recipient ID is invalid.');
        }

        $this->validateDate();

        return empty($this->errors);
    }

    private function validateDate(): void
    {
        $today = new DateTimeImmutable('today');
        $minDate = $today->modify('-1 year');

        $fromDate = DateTimeImmutable::createFromFormat('!Y-m-d', $this->from_date);
        $toDate = $this->to_date !== '' ? DateTimeImmutable::createFromFormat('!Y-m-d', $this->to_date) : null;

        if (!$fromDate) {
            $this->addError('from_date', 'From date is required.');
            return;
        }

        if ($toDate && $fromDate->format('Y-m') !== $toDate->format('Y-m')) {
            $this->addError('to_date', 'The from and to dates must be within the same month.');
            return;
        }

        if ($fromDate < $minDate) {
            $this->addError('from_date', 'The from date of service must be in a 12 month window.');
            return;
        }

        if ($fromDate > $today) {
            $this->addError('from_date', 'Future eligibility cannot be requested.');
            return;
        }

        if ($toDate && $toDate > $today) {
            $this->addError('to_date', 'Future eligibility cannot be requested.');
            return;
        }

        if ($toDate && $toDate < $fromDate) {
            $this->addError('to_date', 'From date must be less than or equal to To date.');
        }
    }

    public function formatDate(string $field, string $fromFormat = 'd/m/Y', string $toFormat = 'Y-m-d'): ?string
    {
        if (empty($this->$field)) {
            return null;
        }

        $date = DateTime::createFromFormat($fromFormat, $this->$field);

        if (!$date) {
            return 'null';
        }

        return $date->format($toFormat);
    }

    public ?string $fromDateUS {
        get {
            return $this->formatDate('from_date', 'Y-m-d', 'm/d/Y');
        }
    }

    public ?string $toDateUS {
        get {
            return $this->formatDate('to_date', 'Y-m-d', 'm/d/Y');
        }
    }

}