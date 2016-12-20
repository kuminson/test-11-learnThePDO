<?php
header('content-type:text/html;charset=utf-8');
try{
	$dsn = 'mysql:host=127.0.0.1;dbname=article';
	$username = 'root';
	$password = '';
	// 终止自动提交
	$options = array(PDO::ATTR_AUTOCOMMIT,0);
	$pdo = new PDO($dsn,$username,$password,$options);
	// 开启事务
	$pdo->beginTransaction();
	$sql = 'UPDATE userAccount SET money=money-2000 WHERE username="imooc";';
	$res1 = $pdo->exec($sql);
	// 如果失败 捕获失败
	if($res1 == 0){
		throw new PDOException("imooc 转账失败");
	}
	$res2 = $pdo->exec('UPDATE userAccount SET money=money+2000 WHERE username="king"');
	// 如果失败 捕获失败
	if($res2 == 0){
		throw new PDOException("king 接收失败");
	}
	// 提交事务
	$pdo->commit();
}catch(PDOException $e){
	// 回滚事务
	$pdo->rollBack();
	echo $e->getMessage();
}
?>