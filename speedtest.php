<?php
$pstarttime = microtime(true);
for($i=0; $i<100; $i++){
	$pdo = new PDO('mysql:host=127.0.0.1;dbname=article','root','');
}
$pendtime = microtime(true);
$sec1 = $pendtime - $pstarttime;

$mstarttime = microtime(true);
for($i=0; $i<100; $i++){
	$msl = new mysqli('127.0.0.1','root','','article');
}
$mendtime = microtime(true);
$sec2 = $mendtime - $mstarttime;

echo 'pdo:'.$sec1;
echo '<br/>';
echo 'mysqli:'.$sec2;
?>