<?php

/** @var EligibilityResultParser $eligibility */

/** @var string $medicaid */
/** @var string $fromDate */
/** @var string $toDate */

use app\utils\medicaid\EligibilityResultParser;

?>

<div class="container">

    <div class="card mb-2">

        <div class="card-header bg-primary text-white py-3">
            <h4 class="mb-0"><i class="bi bi-shield-check"></i> Medicaid Eligibility</h4>
        </div>

        <div class="card-body p-4">

            <p class="text-muted mb-2">Enter the patient's information to verify eligibility.</p>

            <form method="POST" action="/eligibility/search" class="mb-0">
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label for="medicaid_id" class="form-label fw-semibold">Medicaid ID</label>
                        <input type="text" class="form-control" id="medicaid_id" name="medicaid_id" maxlength="20" required autocomplete="off" value="<?= $medicaid ?>">
                    </div>

                    <div class="col-md-4 mb-2">
                        <label for="from_date" class="form-label fw-semibold">From DOS</label>
                        <input type="date" class="form-control" id="from_date" name="from_date" required value="<?= $fromDate ?>">
                    </div>

                    <div class="col-md-4 mb-2">
                        <label for="to_date" class="form-label fw-semibold">To DOS</label>
                        <input type="date" class="form-control" id="to_date" name="to_date" value="<?= $toDate ?>">
                    </div>
                </div>

                <div class="d-grid mt-2">
                    <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-search"></i> Check Eligibility</button>
                </div>
            </form>

        </div>
    </div>

    <?php if ($eligibility->hmo['status'] == 'CONFIRMED_HMO'): ?>
        <div class="alert alert-warning mb-2">
            <div class="d-flex align-items-center">
                <div class="fs-1 me-3"><i class="bi bi-exclamation-triangle-fill"></i></div>
                <div><h4 class="alert-heading mb-1">HMO Confirmed</h4>
                    <div>Managed Care Type: <strong><?= htmlspecialchars($eligibility->hmo['type']) ?></strong></div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($eligibility->hmo['status'] == 'NO_MEDICAID'): ?>
        <div class="alert alert-danger mb-2">
            <div class="d-flex align-items-center">
                <div class="fs-1 me-3"><i class="bi bi-person-check-fill"></i></div>
                <div><h4 class="alert-heading mb-1">No medicaid</h4>
                    <div>Recipient is not eligible for the dates of service requested.</div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($eligibility->hmo['status'] == 'MANUAL_REVIEW'): ?>
        <div class="alert alert-info mb-2">
            <div class="d-flex align-items-center">
                <div class="fs-1 me-3"><i class="bi bi-person-check-fill"></i></div>
                <div><h4 class="alert-heading mb-1">Manual Review Required</h4>
                    <div>No configured HMO type was detected. Review the information below.</div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="card mb-2">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="bi bi-person me-2"></i> Client Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8 mb-3 mb-md-0">
                    <div class="text-muted small">Full Name</div>
                    <div class="fw-semibold"><?= htmlspecialchars(($eligibility->firstName ?? '')) ?> <?= htmlspecialchars(($eligibility->lastName ?? '')) ?></div>
                </div>
                <div class="col-md-2">
                    <div class="text-muted small">Birth Date</div>
                    <div class="fw-semibold"> <?= htmlspecialchars(($eligibility->birthDate ?? '')) ?></div>
                </div>
                <div class="col-md-2">
                    <div class="text-muted small">Age</div>
                    <div class="fw-semibold"> <?= htmlspecialchars(($eligibility->age ?? '')) ?> years</div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($eligibility->hmo['status'] != 'NO_MEDICAID'): ?>

    <div class="card mb-2">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="bi bi-card-checklist me-2"></i> Benefit Plans</h5>
        </div>
        <div class="card-body p-0">
            <?php if ($eligibility->listBenefitPlan === []): ?>
                <div class="p-4 text-muted">No benefit plan information found.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>Benefit</th>
                            <th>From</th>
                            <th>To</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($eligibility->listBenefitPlan as $row): ?>
                            <tr>
                                <?php foreach ($row as $value): ?>
                                    <td><?= htmlspecialchars(trim($value)) ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mb-2">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="bi bi-hospital me-2"></i> TPL / Health Plans</h5>
        </div>
        <div class="card-body p-0">
            <?php if ($eligibility->listTPL === []): ?>
                <div class="p-4 text-muted">No TPL information found.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>Plan</th>
                            <th>Information</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Coverage</th>
                            <th>From</th>
                            <th>To</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($eligibility->listTPL as $row): ?>
                            <tr>
                                <?php foreach ($row as $value): ?>
                                    <td><?= htmlspecialchars(trim($value)) ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mb-2">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-building me-2"></i> Managed Care</h5>
                <?php if ($eligibility->hmo['status'] == 'CONFIRMED_HMO'): ?>
                    <span class="badge text-bg-warning">HMO Confirmed</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if ($eligibility->listManagedCare === []): ?>
                <div class="p-4 text-muted"> No managed care information found.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>Organization</th>
                            <th>Phone</th>
                            <th>Type</th>
                            <th>From</th>
                            <th>To</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($eligibility->listManagedCare as $row): ?>
                            <tr>
                                <?php foreach ($row as $value): ?>
                                    <td><?= htmlspecialchars(trim($value)) ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php endif; ?>
</div>
