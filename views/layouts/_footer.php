<?php

use app\core\App;

?>

<div class="container d-print-none">
    <footer class="d-flex flex-wrap justify-content-between align-items-end pt-2 my-2 border-top">
        <div class="col-12 col-md-8 d-flex align-items-center">
            <span class="mb-3 mb-md-0 text-body-secondary">
                <?= App::$config['name'] ?> v0.1 &copy; <?= date('Y') ?> JC IT NETWORK, LLC
                <?php if (isset(App::$config['github_repo'])): ?>
                    <br>
                    This software is licensed under the <a href="<?= App::$config['github_repo'] ?>/blob/master/LICENSE" target="_blank">AGPL-3.0</a>.
                    Source code is available <a href="<?= App::$config['github_repo'] ?>" target="_blank">here</a>.
                <?php endif; ?>
            </span>
        </div>
        <?php if (isset(App::$config['github_repo'])): ?>
            <ul class="col-12 col-md-4 d-flex flex-column justify-content-end align-items-end list-unstyled mb-0">
                <li class="ms-2"><a class="report-bug" href="<?= App::$config['github_repo'] ?>/issues" target="_blank"><i class="bi bi-bug"></i> Report issue</a></li>
            </ul>
        <?php endif; ?>
    </footer>
</div>
