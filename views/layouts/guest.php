<?php

/** @var string $content */
/** @var string $pageTitle */
/** @var array $scripts */

use app\core\App;
use app\core\helpers\Html;
use app\core\helpers\Url;
use app\core\services\Renderer;
use app\core\widgets\Alert;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once '_head.php'; ?>
    <title><?= Html::encode($pageTitle) ?> - <?= App::$config['name'] ?></title>

    <style>
        .bg-guest {
            background: url(<?= Url::to('assets/img/bg_01.jpg') ?>) no-repeat center;
            background-size: cover;
        }
    </style>
</head>
<body class="">

<main id="content" class="content d-none">
    <?= $content; ?>
</main>

<div class="preloader"></div>
<button type="button" class="btn btn-secondary btn-to-top"><i class="bi bi-caret-up-fill"></i></button>

<?= Renderer::renderScripts($scripts ?? []) ?>

<?= Html::js("bootstrap.bundle.js") ?>
<?= Html::js("index.js") ?>
<?= Html::js("bootstrap-notify.js") ?>
<?= Html::js("notify.js") ?>

<?= Alert::run() ?>

</body>
</html>
