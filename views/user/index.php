<?php

/** @var User[] $users */

use app\core\App;
use app\core\helpers\Url;
use app\models\User;

?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-2 d-print-none">
        <div>
            <h2>Users</h2>
        </div>
        <div>
            <button class="btn btn-primary" data-jax='1' data-href="<?= Url::to('user/create') ?>"><i class="bi bi-plus-lg"></i> Add user</button>
        </div>
    </div>

    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Portal Username</th>
            <th class="d-print-none">Action</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr id="td_<?= $user->id ?>">
                <td class="align-middle"><?= htmlspecialchars($user->name) ?></td>
                <td class="align-middle"><?= htmlspecialchars($user->email) ?></td>
                <td class="align-middle"><?= htmlspecialchars($user->portal_username ?? '') ?></td>
                <td class="align-middle d-print-none" style="width: 125px;">
                    <button class="btn btn-primary btn-sm" data-jax='1' data-href="<?= Url::to("user/$user->id/update") ?>"><i class="bi bi-pen-fill"></i></button>
                    <button class="btn btn-primary btn-sm" data-jax='1' data-href="<?= Url::to("user/$user->id/update") ?>"><i class="bi bi-person-vcard-fill"></i></button>
                    <?php if (App::$session->_id !== $user->id): ?>
                        <button data-href="<?= Url::to("user/$user->id") ?>" class="btn btn-danger btn-sm" data-jax='1' data-jax-method='delete' data-jax-confirm='Are you sure you want to delete this item?'><i class="bi bi-trash"></i></button>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>