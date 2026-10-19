<?php

namespace app\models;

use app\core\Model;
use stdClass;

class FullEligibilityForm extends Model
{

    public string $medicaid_id;

    public function load(array|stdClass $data = []): void
    {
        if (is_array($data)) {
            if (isset($data['medicaid_id'])) {
                $this->medicaid_id = trim($data['medicaid_id']);
            }
            return;
        }

        if ($data instanceof stdClass) {
            $this->medicaid_id = trim($data->medicaid_id);
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

        return empty($this->errors);
    }

}