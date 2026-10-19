<?php

/** @var LoginForm $model */

use app\core\App;
use app\core\helpers\Url;
use app\models\LoginForm;

?>
<form id="form-login" action="<?= Url::to('auth/login') ?>" class="row g-3" method="post" novalidate>
    <?= App::csrfToken() ?>

    <div class="col-12">
        <div class="form-floating">
            <input type="email" name="email" value="<?= $model->email ?? '' ?>" class="form-control <?= $model->getError('email') !== null ? 'is-invalid' : 'is-valid' ?>" id="floatingInput" placeholder="name@example.com" required>
            <label for="floatingInput">Email address</label>
            <div class="invalid-feedback"><?= $model->getError('email') ?? 'Please enter your email.' ?></div>
        </div>
    </div>

    <div class="col-12">
        <div class="form-floating mb-0">
            <input type="password" name="password" class="form-control <?= $model->getError('password') !== null ? 'is-invalid' : 'is-valid' ?>" id="floatingInput2" placeholder="" required>
            <label for="floatingInput2">Password</label>
            <div class="invalid-feedback"><?= $model->getError('password') ?? 'Please enter your password.' ?></div>
        </div>
    </div>

    <?php if ($model->getError('general') !== null): ?>
        <div class="col">
            <div class="alert alert-danger mb-0" role="alert">
                <?= $model->getError('general') ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="col-12">
        <button data-jax="1" data-form-id="form-login" class="btn btn-primary btn-lg w-100" type="submit"><i class="bi bi-box-arrow-in-right"></i> Login</button>
    </div>

    <div class="col-12 d-none">
        <p class="small mb-0">Don't have account? <a href="<?= Url::to('auth/register') ?>">Create an account</a></p>
    </div>
</form>
