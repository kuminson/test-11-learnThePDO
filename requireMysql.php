<?php
// 通过参数形式链接数据库
// try{
// 	$dsn = 'mysql:host=127.0.0.1;dbname=article';
// 	$username = 'root';
// 	$password = '';
// 	$pdo = new PDO($dsn,$username,$password);
// 	var_dump($pdo);
// }catch(PDOException $e){
// 	echo $e->getMessage();
// }

// 通过URI的形式链接数据库
try{
	$dsn = 'uri:file://'.$_SERVER['DOCUMENT_ROOT'].'/connect.txt';
	$username = 'root';
	$password = '';
	$pdo = new PDO($dsn,$username,$password);
	var_dump($pdo);
}catch(PDOException $e){
	echo $e->getMessage();
}
?>