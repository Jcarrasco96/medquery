<?php

/** @var User $model */

use app\core\App;
use app\core\helpers\Url;
use app\models\User;

?>
<form id="form-add_user" class="g-3" action="<?= Url::to('user/create') ?>" method="post" novalidate>
    <?= App::csrfToken() ?>
    <div class="mb-2">
        <label for="inputName" class="form-label mb-1">Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control <?= $model->getError('name') !== null ? 'is-invalid' : 'is-valid' ?>" id="inputName" name="name" required value="<?= $model->name ?? '' ?>">
        <div id="invalid-name" class="invalid-feedback"><?= $model->getError('name') ?></div>
    </div>
    <div class="mb-2">
        <label for="inputEmail" class="form-label mb-1">Email <span class="text-danger">*</span></label>
        <input type="email" class="form-control <?= $model->getError('email') !== null ? 'is-invalid' : 'is-valid' ?>" id="inputEmail" name="email" required value="<?= $model->email ?? '' ?>">
        <div id="invalid-email" class="invalid-feedback"><?= $model->getError('email') ?></div>
    </div>
    <div class="mb-2">
        <label for="inputPassword" class="form-label mb-1">Password <span class="text-danger">*</span></label>
        <input type="password" class="form-control <?= $model->getError('password') !== null ? 'is-invalid' : 'is-valid' ?>" id="inputPassword" name="password" required value="<?= $model->password ?? '' ?>">
        <div id="invalid-password" class="invalid-feedback"><?= $model->getError('password') ?></div>
    </div>
    <button type="submit" data-jax="1" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Submit</button>
</form>
