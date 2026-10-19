<?php

/** @var ProcedureLookup $model */
/** @var Procedure[] $procedures */

use app\core\App;
use app\models\Procedure;
use app\models\ProcedureLookup;

?>

<div class="container">

    <div class="card">
        <div class="card-header py-3">
            <h4 class="mb-0">Procedure Lookup</h4>
        </div>
        <div class="card-body">
            <p class="text-muted mb-2">Enter the patient's information to verify procedures.</p>

            <form method="POST" class="mb-0">

                <?= App::csrfToken() ?>

                <div class="row">
                    <div class="col-md-3 mb-2">
                        <label for="medicaid_id" class="form-label fw-semibold">Medicaid ID</label>
                        <input type="text" class="form-control <?= $model->getError('medicaid_id') !== null ? 'is-invalid' : 'is-valid' ?>" id="medicaid_id" name="medicaid_id" maxlength="20" required value="<?= $model->medicaid_id ?? '' ?>">
                        <div class="invalid-feedback"><?= $model->getError('medicaid_id') ?></div>
                    </div>

                    <div class="col-md-3 mb-2">
                        <label for="procedure" class="form-label fw-semibold">Procedure</label>
                        <select id="procedure" class="form-select <?= $model->getError('procedure') !== null ? 'is-invalid' : 'is-valid' ?>" name="procedure" required>
                            <?php foreach ($procedures as $procedure): ?>
                                <option <?= ($model->procedure ?? '') == $procedure->procedure ? 'selected' : '' ?> value="<?= $procedure->procedure ?>"><?= $procedure->procedure ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3 mb-2">
                        <label for="from_date" class="form-label fw-semibold">From DOS</label>
                        <input type="date" class="form-control <?= $model->getError('from_date') !== null ? 'is-invalid' : 'is-valid' ?>" id="from_date" name="from_date" required value="<?= $model->formatDate('fromDate') ?>">
                    </div>

                    <div class="col-md-3 mb-2">
                        <label for="to_date" class="form-label fw-semibold">To DOS</label>
                        <input type="date" class="form-control <?= $model->getError('to_date') !== null ? 'is-invalid' : 'is-valid' ?>" id="to_date" name="to_date" value="<?= $model->formatDate('toDate') ?>">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Search</button>
            </form>
        </div>
    </div>

    <?php if (!empty($model->listProcedure)): ?>
    <div class="table-responsive mt-2">
        <table class="table table-hover table-bordered table-sm align-middle">
            <thead class="table-light">
            <tr>
                <th>#</th>
                <th class="text-center">From Date</th>
                <th class="text-center">To Date</th>
                <th class="text-center">Procedure</th>
                <th class="text-center">M1</th>
                <th class="text-center">M2</th>
                <th class="text-center">M3</th>
                <th class="text-center">M4</th>
                <th class="text-center">Units</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($model->listProcedure as $procedure): ?>
                <tr>
                    <td><?= htmlspecialchars($procedure->index) ?></td>
                    <td class="text-center"><?= htmlspecialchars($procedure->from_date) ?></td>
                    <td class="text-center"><?= htmlspecialchars($procedure->to_date) ?></td>
                    <td class="text-center">
                        <span class="badge text-bg-secondary"><?= htmlspecialchars($procedure->procedure) ?></span>
                    </td>
                    <td class="text-center"><?= htmlspecialchars($procedure->m1) ?></td>
                    <td class="text-center"><?= htmlspecialchars($procedure->m2) ?></td>
                    <td class="text-center"><?= htmlspecialchars($procedure->m3) ?></td>
                    <td class="text-center"><?= htmlspecialchars($procedure->m4) ?></td>
                    <td class="text-center"><?= htmlspecialchars($procedure->units) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

</div>
