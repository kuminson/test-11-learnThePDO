<?php
class pdoMysql{
	public static $config=array(); // 保存配置信息
	public static $link = null;  // 保存链接标识符
	public static $pconnect= false;  // 是否开启长连接
	public static $dbVersion = null;  // 保存版本号
	public static $connected = false;  // 数据库连接状态
	public static $PDOStatement = null;  // 保存PDOStatement对象
	public static $queryStr = null;   //保存最后执行的操作
	public static $error = null;    //保存错误信息
	public static $lastInsertId = null;  //保存最后受影响ID
	public static $numRows = 0;  //受影响条数

	// 构造函数
	public function __construct($dbConfig=''){
		// 判定是否开启PDO
		if(!class_exists("PDO")){
			self::throw_exception("不支持PDO，请先开启");
		}
		// 判定是否设置配置内容
		if(!is_array($dbConfig)){
			// 设置默认配置内容
			$dbConfig = array(
				'hostname' => DB_HOST,
				'username' => DB_USER,
				'password' => DB_PSWD,
				'database' => DB_NAME,
				'hostport' => DB_PORT,
				'dbms' => DB_TYPE,
				'dsn' => DB_TYPE.":host=".DB_HOST.";dbname=".DB_NAME
			);
		}
		// 判定主机名是否为空
		if(empty($dbConfig['hostname'])){
			self::throw_exception('没有定义数据库配置，请先定义');
		}
		// 把配置内容保存到配置信息里
		self::$config = $dbConfig;
		// 判定是否传入设置参数
		if(empty(self::$config['params'])){
			// 未传入 则为空
			self::$config['params'] = array();
		}
		if(!isset(self::$link)){
			$configs = self::$config;
			if(self::$pconnect){
				// 开启长连接 ，添加到配置数组中
				$configs['params'][constant("PDO::ATTR_PERSISTENT")]=true;
			}
			// 链接数据库
			try{
				self::$link = new PDO($configs['dsn'],$configs['username'],$configs['password'],$configs['params']);
			}catch(PDOException $e){
				self::throw_exception($e->getMessage());
			}
			// 判定链接是否成功
			if(!self::$link){
				self::throw_exception("PDO链接错误");
				return false;
			}
			// 设定字符集
			self::$link->exec('SET NAMES '.DB_CHARSET);
			// 获取数据库版本号
			self::$dbVersion = self::$link->getAttribute(constant("PDO::ATTR_SERVER_VERSION"));
			// 数据库连接状态为成功
			self::$connected = true;
			// 注销配置文件
			unset($configs);
		}

	}

	/*
	 * 得到所有记录
	 * @param string $sql
	 * @return array 
	 */
	public static function getAll($sql=null){
		if($sql != null){
			self::query($sql);
		}
		$result = self::$PDOStatement -> fetchAll(constant("PDO::FETCH_ASSOC"));
		return $result;
	}
	/*
	 * 得到结果集中 一条记录
	 * @param string $sql
	 * @return array
	 */
	public static function getRow($sql=null){
		if($sql != null){
			self::query($sql);
		}
		$result = self::$PDOStatement -> fetch(constant("PDO::FETCH_ASSOC"));
		return $result;
	}

	/*
	 * 执行增删改操作
	 * @param string $sql
	 * @return string
	 */
	public static function execute($sql=null){
		$link = self::$link;
		if(!$link){
			return false;
		}
		// 缓存sql语句
		self::$queryStr = $sql;
		// 释放结果集
		if(!empty(self::$PDOStatement)){
			self::free();
		}
		// 执行sql语句
		$result = $link -> exec(self::$queryStr);
		// 如果有错误 报错
		self::haveErrorThrowException();
		if($result){
			// 缓存最后受影响ID
			self::$lastInsertId = $link->lastInsertId();
			// 缓存受影响条数
			self::$numRows = $result;
			return self::$numRows;
		}
	}

	/*
	 * 根据主键查询记录
	 * @param string $tabName
	 * @param int $priId
	 * @param string $fields
	 * @return mixed
	 */
	public static function findById($tabName,$priId,$fields='*'){
		$sql = 'SELECT %s FROM %s WHERE id="%d"';
		return self::getRow(sprintf($sql,self::parseFields($fields),$tabName,$priId));
	}


	/*
	 * 解析字段
	 * @param mixed $fields
	 * @return string
	 */
	public static function parseFields($fields){
		// 判断是数组
		if(is_array($fields)){
			// 处理数组
			array_walk($fields,array('pdoMysql','addSpecialChar'));
			// 拼成字符串
			$fieldsStr = implode(',',$fields);
		// 判断是字符串
		}elseif(is_string($fields) && !empty($fields)){
			// 判断没有分页
			if(strpos($fields,'`') === false){
				// 拆分字符串成数组
				$fields = explode(',',$fields);
				// 处理数组
				array_walk($fields, array('pdoMysql','addSpecialChar'));
				// 拼成字符串
				$fieldsStr = implode(',',$fields);
			}else{
				$fieldsStr = $fields;
			}
		// 判断是空值
		}else{
			$fieldsStr = "*";
		}
		return $fieldsStr;
	}

	/*
	 * 通过反引号引用字段
	 * @param pointer $value
	 * @return string
	 */
	public static function addSpecialChar(&$value){
		if($value === '*' || strpos($value,'.') !== false || strpos($value, '`') !== false){
			// 不做处理
		}elseif(strpos($value,'`') === false){
			$value = '`'.trim($value).'`';
		}
		return $value;
	}
	/*
	 *释放结果集
	 */
	public static function free(){
		self::$PDOStatement = null;
	}


	public static function query($sql=''){
		$link = self::$link;
		// 判断没有链接标识符 返回false
		if(!$link){
			return false;
		}
		// 判断有结果集，释放结果集
		if(!empty(self::$PDOStatement)){
			self::free();
		}
		// 把sql语句 赋值给最后操作
		self::$queryStr = $sql;
		// 准备sql语句
		self::$PDOStatement = $link -> prepare(self::$queryStr);
		// 执行sql语句
		$res = self::$PDOStatement->execute();
		// 判断sql语句执行是否有误
		self::haveErrorThrowException();
		// 如果没错误终止函数的话 返回结果
		return $res;
	}

	/*
	 * 输出错误信息
	 */
	public static function haveErrorThrowException(){
		// 取得statement或者链接标识符
		$obj = empty(self::$PDOStatement)? self::$link: self::$PDOStatement;
		// 取得错误信息
		$arrError = $obj->errorInfo();
		// 输出错误信息
		// print_r($arrError);
		if(self::$queryStr == ''){
			self::throw_exception('没有可执行的SQL语句');
			return false;
		}
		if($arrError[0]!=='00000'){
			self::$error = 'SQLSTATE: '.$arrError[0].'<br/>SQL Error: '.$arrError[2].'<br/>Error SQL:'.self::$queryStr.'<br/>';
			self::throw_exception(self::$error);
			return false;
		}
	}
	/*
	 * 自定义抛出错误
	 * @param string $errMsg
	 */
	public static function throw_exception($errMsg){
		echo $errMsg;
	}
}

// 引入配置文件
require_once "config.php";
// 实例化pdoMysql类
$pdo = new pdoMysql;
// var_dump($pdo);

// 测试方法getAll
// $sql = 'SELECT * FROM user';
// print_r($pdo -> getAll($sql));

// 测试方法getRow
// $sql = 'SELECT * FROM user WHERE id="1"';
// print_r($pdo -> getRow($sql));

// 测试execute
// $sql = 'INSERT user(username,password) VALUES("queen4","queen4")';
// var_dump($pdo->execute($sql));
// echo '<br/>';
// echo $pdo::$lastInsertId;

// 测试findById
$tabName = 'user';
$priId = 1;
$fields = array('id','age');
var_dump($pdo->findById($tabName,$priId,$fields));
?>