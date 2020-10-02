<?php
/**
 * @var \Whoops\Run $whoops
 */
?>
<div style="margin: 0 auto; text-align: center;">
    <h1>Oops! Noe gikk galt.</h1>
    <h2>Kode: <?= $whoops->sendHttpCode(); ?></h2>
    <h4><a href="./">Gå tilbake</a></h4>
</div>
