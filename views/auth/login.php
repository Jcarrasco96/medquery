<?php

/** @var LoginForm $model */

use app\core\App;
use app\core\services\Renderer;
use app\models\LoginForm;

?>
<section class="bg-primary min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-4 col-lg-6 d-flex flex-column align-items-center justify-content-center">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="text-center">
                            <h1 class="display-4"><i class="bi bi-clipboard2-pulse"></i> <?= App::$config['name'] ?></h1>
                            <h5 class="card-title fs-4">Login to your account</h5>
                            <p class="small">Enter your username and password to login</p>
                        </div>
                        <div id="content-form">
                            <?= Renderer::renderContent(__DIR__ . '/_form.php', ['model' => $model]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>