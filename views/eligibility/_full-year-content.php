<?php

/** @var array $monthlyResults */
/** @var EligibilityModel $eligibility */
/** @var string $medicaid */

use app\core\helpers\Url;
use app\utils\medicaid\EligibilityModel;

?>

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

<div class="accordion" id="eligibilityAccordion">

    <?php foreach ($monthlyResults as $index => $monthly): ?>

        <?php
        $eligibility = $monthly['eligibility'] ?? [];

        $status = $eligibility['status'] ?? 'MANUAL_REVIEW';

        $isHmo = $status === 'CONFIRMED_HMO';

        $collapseId = 'eligibility-' . $index;
        ?>

        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>">
                        <span class="w-100 d-flex justify-content-between align-items-center pe-3">
                            <div>
                                <strong><?= htmlspecialchars($monthly['month']) ?></strong>
                                <small class="text-muted ms-2"><?= htmlspecialchars($monthly['from']) ?> - <?= htmlspecialchars($monthly['to']) ?></small>
                            </div>

                            <?php if ($isHmo): ?>
                                <span class="badge text-bg-warning">HMO</span>
                            <?php else: ?>
                                <span class="badge text-bg-info">Manual Review</span>
                            <?php endif; ?>
                        </span>
                </button>
            </h2>

            <div id="<?= $collapseId ?>" class="accordion-collapse collapse" data-bs-parent="#eligibilityAccordion">
                <div class="accordion-body">
                    <?php
                    $benefitPlans = $monthly['benefit_plans'] ?? [];
                    $managedCare = $monthly['managed_care'] ?? [];
                    $tpl = $monthly['tpl'] ?? [];
                    ?>

                    <?php if ($isHmo): ?>
                        <div class="alert alert-warning mb-0">
                            <strong>HMO Confirmed</strong>
                            <?php if (!empty($eligibility['type'])): ?>
                                <br>
                                Type: <?= htmlspecialchars($eligibility['type']) ?>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <strong>Manual Review Required</strong>
                            <br>
                            No configured HMO type was detected for this period.
                        </div>
                    <?php endif; ?>

                    <button class="btn btn-primary my-2" data-href="<?= Url::to("eligibility/print", [
                        'medicaid_id' => $medicaid,
                        'from_date' => $monthly['from_date'],
                        'to_date' => $monthly['to_date'],
                    ]) ?>" data-jax="1"><i class="bi bi-printer-fill"></i> Print</button>

                    <?php if ($benefitPlans !== []): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                <tr>
                                    <th colspan="4"><i class="bi bi-card-checklist"></i> Benefit Plans</th>
                                </tr>
                                <tr>
                                    <th>Benefit</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($benefitPlans as $row): ?>
                                    <tr>
                                        <?php foreach ($row as $value): ?>
                                            <td><?= htmlspecialchars(trim((string)$value)) ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <?php if ($tpl !== []): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                <tr>
                                    <th colspan="8"><i class="bi bi-hospital"></i> TPL / Health Plans</th>
                                </tr>
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
                                <?php foreach ($tpl as $row): ?>
                                    <tr>
                                        <?php foreach ($row as $value): ?>
                                            <td><?= htmlspecialchars(trim((string)$value)) ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <?php if ($managedCare !== []): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                <tr>
                                    <th colspan="6"><i class="bi bi-building"></i> Managed Care</th>
                                </tr>
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
                                <?php foreach ($managedCare as $row): ?>
                                    <tr>
                                        <?php foreach ($row as $value): ?>
                                            <td><?= htmlspecialchars(trim((string)$value)) ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
