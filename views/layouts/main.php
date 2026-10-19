<?php

/** @var string $content */
/** @var string $pageTitle */
/** @var array $scripts */

use app\core\App;
use app\core\helpers\Html;
use app\core\services\Renderer;
use app\core\widgets\Alert;
use app\utils\debugger\MyDebugger;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once '_head.php'; ?>
    <title><?= Html::encode($pageTitle) ?> - <?= App::$config['name'] ?></title>
</head>
<body>
<?php include_once '_nav.php'; ?>

<div class="wrapper">
    <main id="content" class="content d-none">
        <?= $content; ?>
    </main>

    <?php include_once '_footer.php'; ?>
</div>

<div class="modal fade" id="modal-app" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modal-app-title">Modal title</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modal-app-container"></div>
        </div>
    </div>
</div>

<div class="preloader"></div>
<button type="button" class="btn btn-secondary btn-to-top"><i class="bi bi-caret-up-fill"></i></button>

<img src="<?= Html::img('loading.gif') ?>" alt="Loading" id="loading-indicator" />

<?= MyDebugger::run() ?>

<?= Renderer::renderScripts($scripts) ?>

<?= Html::js("bootstrap.bundle.min.js") ?>
<?= Html::js("index.js") ?>
<?= Html::js("bootstrap-notify.min.js") ?>
<?= Html::js("notify.js") ?>
<?= Html::js("fix.container.js") ?>

<?= Alert::run() ?>
</body>
</html>
