<?php

/** @var Procedure[] $procedures */
/** @var Pagination $pagination */

use app\core\helpers\Url;
use app\core\services\Pagination;
use app\core\services\Renderer;
use app\core\widgets\BootstrapPaginator;
use app\models\Procedure;

?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-2 d-print-none">
        <div>
            <h2>Procedures</h2>
        </div>
        <div>
            <button class="btn btn-primary" data-jax='1' data-href="<?= Url::to('procedure/create') ?>"><i class="bi bi-plus-lg"></i> Add procedure</button>
        </div>
    </div>

    <div id="table-procedures">
        <?= Renderer::renderContent(__DIR__ . '/_item.php', [
            'procedures' => $procedures,
            'pagination' => $pagination,
        ]) ?>
    </div>
</div>