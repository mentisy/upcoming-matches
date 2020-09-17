<?php
/**
 * @var \Whoops\Run $whoops
 */
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="images/icon-64.png" type="image/x-icon" rel="icon"/>
    <link href="images/icon-64.png" type="image/x-icon" rel="shortcut icon"/>
    <title>Ukens Kamper - Aksla IL</title>
    <link rel="stylesheet" href="css/milligram.min.css" />
</head>
<body>
<div style="margin: 0 auto; text-align: center;">
    <h1>Oops! Noe gikk galt.</h1>
    <h2>Kode: <?= $whoops->sendHttpCode(); ?></h2>
    <h4><a href="./">Gå tilbake</a></h4>
</div>
</body>