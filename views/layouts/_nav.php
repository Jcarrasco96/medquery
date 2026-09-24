<?php

use app\core\App;
use app\core\helpers\Url;

$roles = App::$session->roles();

$rolesSelect = [];
$roleSelected = App::$session->getSelectedRole() ?? 'NO ROLE';

if ($roles) {
    $rolesSelect[] = '<hr class="dropdown-divider">';
    $rolesSelect[] = '<h6 class="dropdown-header">Select role</h6>';

    foreach ($roles as $role) {
        if ($roleSelected == $role) {
            continue;
        }

        $rolesSelect[] = '<a id="selectedRole" class="dropdown-item" href="' . Url::to("site/select-role/$role") .'">' . $result = ucwords(str_replace('-', ' ', $role)) . '</a>';
    }
}

if (count($rolesSelect) == 2) {
    $rolesSelect = [];
}

$path = $_SERVER['REQUEST_URI'] ?? '/';
$position = strpos($path, '?');

if ($position !== false) {
    $path = substr($path, 0, $position);
}

?>

<nav id="w0-navbar" class="navbar navbar-expand-md navbar-dark fixed-top bg-primary d-print-none">
    <div class="container px-3">
        <a class="navbar-brand" href="<?= Url::to('site/index') ?>"><?= App::$config['name'] ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarText">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link <?= $path == '/eligibility' ? "active" : '' ?>" href="<?= Url::to('eligibility') ?>"><i class="bi bi-clipboard-check"></i> Eligibility</a></li>
            </ul>

            <ul class="navbar-nav ms-auto">
                <?php if (App::$session->isAuthenticated()): ?>


                    <li class="nav-item"><a class="nav-link" href="<?= Url::to('adminer-6.1.0-mysql-en.php') ?>" target="_blank"><i class="bi bi-database"></i> Adminer</a></li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><?= strtoupper(App::$user->name) ?></a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-darks">
                            <li><a class="dropdown-item" href="<?= Url::to('account/my/index') ?>">Account settings</a></li>
                            <?php foreach ($rolesSelect as $item): ?>
                                <li><?= $item ?></li>
                            <?php endforeach; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= Url::to('auth/logout') ?>" data-method="post">Logout</a></li>
                        </ul>
                    </li>

                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= Url::to('auth/login') ?>">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>