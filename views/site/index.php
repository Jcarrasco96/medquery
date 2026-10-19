<?php

use app\core\App;
use app\core\helpers\Url;

?>
<div class="container">
    <div class="mb-3">
        <div class="p-5 bg-body-tertiary rounded-3 text-center">
            <h1 class="text-body-emphasis"><?= App::$config['name'] ?></h1>

            <p class="lead">Simplifying Medicaid Data Access.</p>

            <p class="lead">MedQuery is a lightweight web application designed to simplify access to Medicaid patient information, starting with Florida Medicaid.</p>

            <div class="d-inline-flex gap-2">
                <a class="btn btn-primary btn-lg" href="<?= Url::to('eligibility') ?>"><i class="bi bi-clipboard-check"></i> Eligibility</a>
                <a class="btn btn-primary btn-lg" href="<?= Url::to('eligibility/full-year') ?>"><i class="bi bi-calendar-check"></i> Full-year Eligibility</a>
                <a class="btn btn-primary btn-lg" href="<?= Url::to('procedure-lookup') ?>"><i class="bi bi-search"></i> Procedure Lookup</a>
            </div>
        </div>
    </div>
</div>
