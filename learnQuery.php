<?php
// 链接数据库 得到实例化PDO
$pdo = new PDO('mysql:host=127.0.0.1;dbname=article','root','');
try{
	// sql语句
	$sql = 'SELECT * FROM user2 WHERE id=1;';
	// 访问数据库
	$stmt = $pdo -> query($sql);
	// 输出返回值
	var_dump($stmt);
	echo '<hr/>';
	print_r($stmt);
	// foreach($stmt as $row){
	// 	echo "<hr/>";
	// 	print_r($row);
	// }
	$res = $stmt ->fetch();
	print_r($res);
}catch(PDOException $e){
	echo $e->Message();
}

?>