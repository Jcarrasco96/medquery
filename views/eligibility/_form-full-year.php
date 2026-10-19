<?php

/** @var FullEligibilityForm $model */

use app\core\App;
use app\models\FullEligibilityForm;

?>

<div class="card mb-2">
    <div class="card-header py-3">
        <h4 class="mb-0">Full-year Medicaid eligibility</h4>
    </div>
    <div class="card-body">
        <p class="text-muted mb-2">Enter the patient's information to verify eligibility for a full year.</p>

        <form id="full-year-eligibility-form" method="POST" action="/eligibility/search/full-year" class="mb-0">
            <?= App::csrfToken() ?>
            <div class="mb-2">
                <label for="medicaid_id" class="form-label fw-semibold">Medicaid ID</label>
                <input type="text" class="form-control <?= $model->getError('medicaid_id') !== null ? 'is-invalid' : 'is-valid' ?>" id="medicaid_id" name="medicaid_id" maxlength="10" required value="<?= $model->medicaid_id ?? '' ?>">
                <div id="invalid-procedure" class="invalid-feedback"><?= $model->getError('medicaid_id') ?></div>
            </div>
            <button type="submit" data-jax="1" data-form-id="full-year-eligibility-form" class="btn btn-primary"><i class="bi bi-search"></i> Check Eligibility</button>
        </form>
    </div>
</div>
