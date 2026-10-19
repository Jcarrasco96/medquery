<?php

use app\core\App;
use app\core\helpers\Url;

?>
<nav id="w0-navbar" class="navbar navbar-expand-lg fixed-top bg-dark d-print-none" data-bs-theme="dark">
    <div class="container px-3">
        <a class="navbar-brand" href="<?= Url::to('site/index') ?>"><i class="bi bi-clipboard2-pulse"></i> <?= App::$config['name'] ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarText">
            <ul class="navbar-nav">
                <?php if (App::$session->isAuthenticated): ?>
                    <li class="nav-item"><a class="nav-link <?= Url::isActiveUrl('/eligibility') ? "active" : '' ?>" href="<?= Url::to('eligibility') ?>"><i class="bi bi-clipboard-check"></i> Eligibility</a></li>
                    <li class="nav-item"><a class="nav-link <?= Url::isActiveUrl('/eligibility/full-year') ? "active" : '' ?>" href="<?= Url::to('eligibility/full-year') ?>"><i class="bi bi-calendar-check"></i> Full-year Eligibility</a></li>
                    <li class="nav-item"><a class="nav-link <?= Url::isActiveUrl('/procedure-lookup') ? "active" : '' ?>" href="<?= Url::to('procedure-lookup') ?>"><i class="bi bi-search"></i> Procedure Lookup</a></li>
                <?php endif; ?>
            </ul>

            <ul class="navbar-nav ms-auto">
                <?php if (App::$session->isAuthenticated): ?>
                    <?php if (App::$session->isAdmin): ?>
                        <li class="nav-item"><a class="nav-link <?= Url::isActiveUrl('/procedure') ? "active" : '' ?>" href="<?= Url::to('/procedure') ?>"><i class="bi bi-journal-medical"></i> Procedures</a></li>
                        <li class="nav-item"><a class="nav-link <?= Url::isActiveUrl('/user') ? "active" : '' ?>" href="<?= Url::to('/user') ?>"><i class="bi bi-person"></i> Users</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= Url::to('adminer-6.1.0-mysql-en.php') ?>" target="_blank"><i class="bi bi-database"></i> Adminer</a></li>
                    <?php endif; ?>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><?= strtoupper(htmlspecialchars(App::$user->name)) ?></a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-darks">
                            <li><a class="dropdown-item" href="<?= Url::to('account') ?>">Account settings</a></li>
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