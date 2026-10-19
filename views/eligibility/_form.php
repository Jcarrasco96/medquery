<?php

/** @var EligibilityForm $model */

use app\core\App;
use app\models\EligibilityForm;

?>
<div class="card">
    <div class="card-header py-3">
        <h4 class="mb-0">Medicaid Eligibility</h4>
    </div>
    <div class="card-body">
        <p class="text-muted mb-2">Enter the patient's information to verify eligibility.</p>

        <form id="eligibility-form" method="POST" action="/eligibility/search" class="mb-0">
            <?= App::csrfToken() ?>
            <div class="row">
                <div class="col-md-6 mb-2">
                    <label for="medicaid_id" class="form-label fw-semibold">Medicaid ID</label>
                    <input type="text" class="form-control <?= $model->getError('medicaid_id') !== null ? 'is-invalid' : 'is-valid' ?>" id="medicaid_id" name="medicaid_id" maxlength="10" required value="<?= $model->medicaid_id ?? '' ?>">
                    <div id="invalid-procedure" class="invalid-feedback"><?= $model->getError('medicaid_id') ?></div>
                </div>
                <div class="col-md-3 mb-2">
                    <label for="from_date" class="form-label fw-semibold">From DOS</label>
                    <input type="date" class="form-control <?= $model->getError('from_date') !== null ? 'is-invalid' : 'is-valid' ?>" id="from_date" name="from_date" required value="<?= $model->from_date ?? '' ?>">
                    <div id="invalid-procedure" class="invalid-feedback"><?= $model->getError('from_date') ?></div>
                </div>
                <div class="col-md-3 mb-2">
                    <label for="to_date" class="form-label fw-semibold">To DOS</label>
                    <input type="date" class="form-control <?= $model->getError('to_date') !== null ? 'is-invalid' : 'is-valid' ?>" id="to_date" name="to_date" value="<?= $model->to_date ?? '' ?>">
                    <div id="invalid-procedure" class="invalid-feedback"><?= $model->getError('to_date') ?></div>
                </div>
            </div>
            <button type="submit" data-jax="1" data-form-id="eligibility-form" class="btn btn-primary"><i class="bi bi-search"></i> Check Eligibility</button>
        </form>
    </div>
</div>
