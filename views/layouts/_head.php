<?php

/** @var array $styles */
/** @var array $headScripts */

use app\core\helpers\Html;
use app\core\helpers\Url;
use app\core\services\Renderer;

?>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="">
<meta name="keywords" content="">
<meta name="robots" content="noindex, nofollow">
<meta name="googlebot" content="noindex">

<link rel="apple-touch-icon" sizes="180x180" href="<?= Url::to('assets/img/apple-touch-icon.png') ?>">
<link rel="icon" type="image/png" sizes="32x32" href="<?= Url::to('assets/img/favicon-32x32.png') ?>">
<link rel="icon" type="image/png" sizes="16x16" href="<?= Url::to('assets/img/favicon-16x16.png') ?>">
<link rel="manifest" href="<?= Url::to('assets/img/site.webmanifest') ?>">

<?= Html::icon("img/favicon.ico") ?>

<?= Renderer::renderStyles($styles ?? []) ?>

<?= Html::css("bootstrap.css") ?>
<?= Html::css("bootstrap-icons/bootstrap-icons.css") ?>
<?= Html::css("animate.css") ?>
<?= Html::css("preloader.css") ?>
<?= Html::css("style.css") ?>

<?= Renderer::renderScripts($headScripts ?? []) ?>

<?= Html::js("jquery-3.7.1.js") ?>
