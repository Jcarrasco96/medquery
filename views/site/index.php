<?php

use app\core\helpers\Url;

?>

<div class="container">

    <div class="d-flex justify-content-between align-items-center mb-2 d-print-none">
        <div>
            <h2>My sites</h2>
        </div>
        <div>
            <button class="btn btn-primary" id="btn-add_website" data-url="<?= Url::to('website/create') ?>"><i class="bi bi-plus-lg"></i> Add website</button>
        </div>
    </div>

</div>
