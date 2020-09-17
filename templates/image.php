<?php
/**
 * @var \Imagick $imagick
 */
$filename = 'ukens-kamper.png';
header(sprintf('Content-Disposition: inline; filename="%s"', $filename));
header('Content-Type: image/png');
echo $imagick;
