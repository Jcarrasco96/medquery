<?php

/** @var int $code */
/** @var string $message */
/** @var ?string $trace */

use app\core\helpers\Html;

$img = in_array($code, [403, 404, 405]) ? "$code.svg" : '500.svg';

?>

<div class="container">
    <section class="site-error d-flex align-items-center justify-content-center p-2">
        <div class="d-flex flex-column align-items-center">
            <img class="img-fluid" src="<?= Html::img($img) ?>" style="max-height: 300px;" alt="Error"/>
            <h2 class="text-break">ERROR <?= $code ?></h2>
            <h5 class="text-break"><?= nl2br(htmlspecialchars($message)) ?></h5>
            <code class="border rounded p-2 "><?= nl2br(htmlspecialchars($trace)) ?></code>
        </div>
    </section>
</div>