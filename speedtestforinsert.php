<?php
$pt = microtime(true);
$pdo = new PDO('mysql:host=127.0.0.1;dbname=article','root','');
$psql = 'INSERT testspeed VALUES(:id)';
$stmt1 = $pdo -> prepare($psql);
for($i=0; $i<1000; $i++){
	$id=1;
	$stmt1->bindParam(':id',$id,PDO::PARAM_INT);
	$stmt1->execute();
}
$pe = microtime(true);
// 销毁
unset($pdo) //$pdo = null;
$ptime = $pe - $pt;

$mt = microtime(true);
$mysqli = new mysqli('127.0.0.1','root','','article');
$msql = 'INSERT testspeed VALUES(?)';
$stmt2 = $mysqli -> prepare($msql);
for($i=0; $i<1000; $i++){
	$mid =2;
	$stmt2 -> bind_param('i',$mid);
	$stmt2 -> execute();
}
$me = microtime(true);
$mysqli -> close();
$mtime = $me - $mt;

echo $ptime.'PDO';
echo '<br/>';
echo $mtime.'mysqli';
?>