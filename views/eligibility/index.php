<?php

use app\core\helpers\Url;

?>

<div class="container">

    <div class="card shadow-sm border-0">

        <div class="card-header bg-primary text-white py-3">
            <h4 class="mb-0"><i class="bi bi-shield-check"></i> Medicaid Eligibility</h4>
        </div>

        <div class="card-body p-4">

            <p class="text-muted mb-2">Enter the patient's information to verify eligibility.</p>

            <form method="POST" action="/eligibility/search" class="mb-0">

                <div class="row">

                    <div class="col-md-4 mb-2">
                        <label for="medicaid_id" class="form-label fw-semibold">Medicaid ID</label>
                        <input type="text" class="form-control" id="medicaid_id" name="medicaid_id" maxlength="20" required autocomplete="off">
                    </div>

                    <div class="col-md-4 mb-2">
                        <label for="from_date" class="form-label fw-semibold">From DOS</label>
                        <input type="date" class="form-control" id="from_date" name="from_date" required>
                    </div>

                    <div class="col-md-4 mb-2">
                        <label for="to_date" class="form-label fw-semibold">To DOS</label>
                        <input type="date" class="form-control" id="to_date" name="to_date">
                    </div>

                </div>

                <div class="d-grid mt-2">
                    <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-search"></i> Check Eligibility</button>
                </div>

            </form>

        </div>
    </div>

</div>
