<?php
try{
	$sdn = 'mysql:host=127.0.0.1;dbname=article';
	$username = 'root';
	$password = '';
	$pdo = new PDO($sdn,$username,$password);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SLIENT);
	$sql = 'SELECT * FROM user12;';
	$stmt = $pdo->query($sql);
	echo $pdo->errorCode();
}catch(PDOException $e){
	echo $e->getMessage();
}
?>