<?php

/** @var string $content */
/** @var string $title */

use app\core\helpers\Html;

// https://portal.flmmis.com/FLPortal/Portals/_default/Skins/flportalskin/portal.css
// https://portal.flmmis.com/FLPortal/Portals/_default/default.css
// https://portal.flmmis.com/FLPortal/Portals/_default/Skins/flportalskin/skin.css
// https://portal.flmmis.com/FLPortal/Portals/_default/Skins/flportalskin/PortalSkin.css

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400" rel="stylesheet" type="text/css">

    <?= Html::css("flmmis/default.css") ?>
    <?= Html::css("flmmis/skin.css") ?>
    <?= Html::css("flmmis/PortalSkin.css") ?>

    <title><?= Html::encode($title) ?></title>

    <style>
        h2 {
            margin-top: 0;
        }

        .iC_SkinContentPane {
            padding-left: 15px;
            padding-right: 15px;
        }

        @media print {
            .iC_SkinContentPane {
                padding-left: 0;
                padding-right: 0;
            }
        }
    </style>

    <script>
        print();
    </script>
</head>
<body>
<?= $content; ?>
</body>
</html>
