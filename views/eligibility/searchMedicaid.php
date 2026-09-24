<?php

/** @var array $monthlyResults */

?>

<div class="container">

    <h5 class="mt-2 mb-3"><i class="bi bi-calendar3"></i> Monthly Eligibility</h5>

    <div class="accordion" id="eligibilityAccordion">

        <?php foreach ($monthlyResults as $index => $monthly): ?>

            <?php
            $monthlyResult = $monthly['result'];

            $eligibility = $monthlyResult['eligibility'] ?? [];

            $status = $eligibility['status'] ?? 'MANUAL_REVIEW';

            $isHmo = $status === 'CONFIRMED_HMO';

            $collapseId = 'eligibility-' . $index;
            ?>

            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button <?= $index !== 12 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>">
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

                <div id="<?= $collapseId ?>" class="accordion-collapse collapse <?= $index === 11 ? 'show' : '' ?>" data-bs-parent="#eligibilityAccordion">
                    <div class="accordion-body">
                        <?php
                        $benefitPlans = $monthlyResult['benefit_plans'] ?? [];
                        $managedCare = $monthlyResult['managed_care'] ?? [];
                        $tpl = $monthlyResult['tpl'] ?? [];
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

</div>