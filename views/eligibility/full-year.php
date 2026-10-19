<?php

/** @var FullEligibilityForm $model */

use app\core\services\Renderer;
use app\models\FullEligibilityForm;

//$last = count($monthlyResults) - 1;

// accordion-button $index !== $last ? 'collapsed' : ''
// accordion-collapse collapse $index === $last ? 'show' : ''

?>

<div class="container">
    <div id="formContent">
        <?= Renderer::renderContent(__DIR__ . '/_form-full-year.php', ['model' => $model]) ?>
    </div>
    <div id="eligibilityContent"></div>
</div>