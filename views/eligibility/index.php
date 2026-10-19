<?php

/** @var EligibilityForm $model */

use app\core\services\Renderer;
use app\models\EligibilityForm;

?>
<div class="container">
    <div id="formContent">
        <?= Renderer::renderContent(__DIR__ . '/_form.php', ['model' => $model]) ?>
    </div>
    <div id="eligibilityContent"></div>
</div>
