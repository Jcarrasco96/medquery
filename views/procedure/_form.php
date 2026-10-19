<?php

/** @var Procedure $model */

use app\core\App;
use app\core\helpers\Url;
use app\models\Procedure;

$btnIcon = $model->scenario == Procedure::SCENARIO_CREATE ? 'bi-plus-lg' : 'bi-pen';
$btnTitle = $model->scenario == Procedure::SCENARIO_CREATE ? 'Create' : 'Update';

$url = match ($model->scenario) {
    Procedure::SCENARIO_CREATE => Url::to("procedure/create"),
    Procedure::SCENARIO_UPDATE => Url::to("procedure/$model->id/update"),
    default => '',
};

?>
<form id="form-add_procedure" class="g-3" action="<?= $url ?>" method="post" novalidate>
    <?= App::csrfToken() ?>
    <div class="mb-2">
        <label for="inputProcedure" class="form-label mb-1">Procedure <span class="text-danger">*</span></label>
        <input type="text" class="form-control <?= $model->getError('procedure') !== null ? 'is-invalid' : 'is-valid' ?>" id="inputProcedure" name="procedure" required value="<?= $model->procedure ?? '' ?>">
        <div id="invalid-procedure" class="invalid-feedback"><?= $model->getError('procedure') ?></div>
    </div>
    <button type="submit" data-jax="1" class="btn btn-primary"><i class="bi <?= $btnIcon ?>"></i> <?= $btnTitle ?></button>
</form>
