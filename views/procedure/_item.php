<?php

/** @var Procedure[] $procedures */
/** @var Pagination $pagination */

use app\core\helpers\Url;
use app\core\services\Pagination;
use app\core\widgets\BootstrapPaginator;
use app\models\Procedure;

$order = $_GET['order'] ?? 'procedure:asc';
$nextOrder = $order === 'procedure:asc' ? 'procedure:desc' : 'procedure:asc';

$query = http_build_query([
    'page' => $pagination->page + 1,
    'limit' => $pagination->pageSize,
    'order' => $nextOrder,
]);

?>
<table class="table table-bordered table-striped">
    <thead>
    <tr>
        <th>
            <a href="?<?= htmlspecialchars($query) ?>" class="text-decoration-none text-reset">Procedure <?= $order === 'procedure:asc' ? '<i class="bi bi-sort-alpha-down"></i>' : '<i class="bi bi-sort-alpha-up"></i>' ?></a>
        </th>
        <th class="d-print-none">Action</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($procedures as $procedure): ?>
        <tr id="td_<?= $procedure->id ?>">
            <td class="align-middle"><?= htmlspecialchars($procedure->procedure) ?></td>
            <td class="align-middle d-print-none" style="width: 86px;">
                <button class="btn btn-primary btn-sm" data-jax='1' data-href="<?= Url::to("procedure/$procedure->id/update") ?>"><i class="bi bi-pen"></i></button>
                <button class="btn btn-danger btn-sm" data-jax='1' data-href="<?= Url::to("procedure/$procedure->id") ?>" data-jax-method='delete' data-jax-confirm='Are you sure you want to delete this item?'><i class="bi bi-trash-fill"></i></button>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?= BootstrapPaginator::render($pagination); ?>