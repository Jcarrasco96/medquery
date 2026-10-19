<?php

declare(strict_types=1);

namespace app\models;

use app\core\exceptions\BadRequestHttpException;
use app\core\exceptions\ServerErrorHttpException;
use app\core\Model;
use app\core\validators\LengthValidator;
use app\core\validators\RequiredValidator;
use app\utils\medicaid\HttpClient;
use app\utils\medicaid\PageAnalyzer;
use DateTime;

final class ProcedureLookup extends Model
{

    public string $medicaid_id;
    public string $procedure;
    public string $fromDate;
    public string $toDate;

    /** @var ProcedureLookupResult[]  */
    public array $listProcedure = [];

    /**
     * @throws BadRequestHttpException
     */
    public static function fromArray(array $data): self
    {
        $obj = new ProcedureLookup();

        $fields = ['medicaid_id', 'procedure', 'from_date', 'to_date'];

        foreach ($fields as $field) {
            if (empty($data[$field])) {
                throw new BadRequestHttpException('Field "' . $field . '" is required');
            }
        }

        $obj->medicaid_id = $data['medicaid_id'];
        $obj->procedure = $data['procedure'];

        $obj->fromDate = DateTime::createFromFormat('Y-m-d', $data['from_date'])->format('m/d/Y');
        $obj->toDate = DateTime::createFromFormat('Y-m-d', $data['to_date'])->format('m/d/Y');

        return $obj;
    }

    public function rules(): array
    {
        return [
            'medicaid_id' => [
                new RequiredValidator(),
                new LengthValidator(10),
            ],
        ];
    }

    public function formatDate(string $field, string $format = 'Y-m-d'): ?string
    {
        if (empty($this->$field)) {
            return null;
        }

        return date($format, strtotime($this->$field));
    }

    /**
     * @throws ServerErrorHttpException
     * @throws BadRequestHttpException
     */
    public function parseResult(): void
    {
        $client = new HttpClient('runtime/cookies/session.cookie');

        $page = PageAnalyzer::procedureLookupPage($client);

        $this->listProcedure = $page->postSearch($client, $this->medicaid_id, $this->procedure, $this->fromDate, $this->toDate)->parse();
    }

}