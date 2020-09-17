<?php

use Cake\Chronos\Chronos;

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
<div style="width: 200px; margin: 0 auto; text-align: center;">
    <form action="" method="GET">
        <div>
            <label for="dateFrom">Dato fra:</label>
        </div>
        <div>
            <input type="date" name="dateFrom" id="dateFrom" value="<?= Chronos::now()->startOfWeek()->toDateString(); ?>">
        </div>
        <div>
            <label for="dateTo">Dato til:</label>
        </div>
        <div>
            <input type="date" name="dateTo" id="dateTo" value="<?= Chronos::now()->endOfWeek()->toDateString(); ?>">
        </div>
        <div>
            <input type="submit" value="Hente">
        </div>
    </form>
</div>
</body>
</html>