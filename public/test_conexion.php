<?php

$enlace = mysql_connect('192.168.1.50', 'kevin', '123456');
if  (!$enlace) {
    die('No pudo conectarse: ' . mysql_error());
} else echo 'si hay Conexión';
