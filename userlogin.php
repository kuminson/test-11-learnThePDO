<?php
$sdn = 'mysql:host=127.0.0.1;dbname=article';
$user = 'root';
$pass = '';
try{
$pdo = new PDO($sdn,$user,$pass);
$username = $_POST["username"];
$password = md5($_POST["password"]);
$sql = "SELECT * FROM user2 WHERE username='{$username}' AND password='{$password}';";
$stmt = $pdo->query($sql);
print_r($stmt->fetch());

}catch(PDOException $e){
	echo $e->Message();
}
?>