<?php namespace inc;
session_start();
?>
<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title><?php echo LOGIN ?></title>
    <link rel="icon" href="../img/favicon.ico" />
    <meta name="description" content="Tampoon" />
    <link rel="stylesheet" type="text/css" href="../css/login.css"/>
    <script>
    <?php
    echo 'var locale = "' . $_SESSION['locale'] . '";' . PHP_EOL;

    ?>
    </script>
    <script type="text/javascript" src="../js/translations.js"></script>
    <script type="text/javascript" src="../js/script.js"></script>
</head>
<body>