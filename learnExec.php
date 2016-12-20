<?php
try{
	// 链接数据库
	$pdo = new PDO('mysql:host=127.0.0.1;dbname=article','root','');
	// 新建表
// 	$sql = <<<LES
// 		CREATE TABLE IF NOT EXISTS user2(
// 			id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
// 			username VARCHAR(30) NOT NULL UNIQUE KEY,
// 			password CHAR(50) NOT NULL,
// 			email VARCHAR(20) NOT NULL
// 		);
// LES;
	// $res = $pdo->exec($sql);
	// var_dump($res);
	$sql = 'INSERT user12(username,password,email) VALUES("queen2","'.md5('queen1').'","queen2@mooc.com");';
	$res = $pdo -> exec($sql);
	// echo $sql;
	if($res === false){
		echo $pdo->errorCode();
		echo '<hr/>';
		print_r($pdo->errorInfo());
	}
}catch(PDOException $e){
	echo $e->getMessage();
}
?>