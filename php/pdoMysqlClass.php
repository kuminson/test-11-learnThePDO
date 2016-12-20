<?php
class pdoMysql{
	public static $config=array(); // ����������Ϣ
	public static $link = null;  // �������ӱ�ʶ��
	public static $pconnect= false;  // �Ƿ���������
	public static $dbVersion = null;  // ����汾��
	public static $connected = false;  // ���ݿ�����״̬
	public static $PDOStatement = null;  // ����PDOStatement����
	public static $queryStr = null;   //�������ִ�еĲ���
	public static $error = null;    //���������Ϣ
	public static $lastInsertId = null;  //���������Ӱ��ID
	public static $numRows = 0;  //��Ӱ������

	// ���캯��
	public function __construct($dbConfig=''){
		// �ж��Ƿ���PDO
		if(!class_exists("PDO")){
			self::throw_exception("��֧��PDO�����ȿ���");
		}
		// �ж��Ƿ�������������
		if(!is_array($dbConfig)){
			// ����Ĭ����������
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
		// �ж��������Ƿ�Ϊ��
		if(empty($dbConfig['hostname'])){
			self::throw_exception('û�ж������ݿ����ã����ȶ���');
		}
		// ���������ݱ��浽������Ϣ��
		self::$config = $dbConfig;
		// �ж��Ƿ������ò���
		if(empty(self::$config['params'])){
			// δ���� ��Ϊ��
			self::$config['params'] = array();
		}
		if(!isset(self::$link)){
			$configs = self::$config;
			if(self::$pconnect){
				// ���������� ����ӵ�����������
				$configs['params'][constant("PDO::ATTR_PERSISTENT")]=true;
			}
			// �������ݿ�
			try{
				self::$link = new PDO($configs['dsn'],$configs['username'],$configs['password'],$configs['params']);
			}catch(PDOException $e){
				self::throw_exception($e->getMessage());
			}
			// �ж������Ƿ�ɹ�
			if(!self::$link){
				self::throw_exception("PDO���Ӵ���");
				return false;
			}
			// �趨�ַ���
			self::$link->exec('SET NAMES '.DB_CHARSET);
			// ��ȡ���ݿ�汾��
			self::$dbVersion = self::$link->getAttribute(constant("PDO::ATTR_SERVER_VERSION"));
			// ���ݿ�����״̬Ϊ�ɹ�
			self::$connected = true;
			// ע�������ļ�
			unset($configs);
		}

	}

	/*
	 * �õ����м�¼
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
	 * �õ�������� һ����¼
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
	 * ִ����ɾ�Ĳ���
	 * @param string $sql
	 * @return string
	 */
	public static function execute($sql=null){
		$link = self::$link;
		if(!$link){
			return false;
		}
		// ����sql���
		self::$queryStr = $sql;
		// �ͷŽ����
		if(!empty(self::$PDOStatement)){
			self::free();
		}
		// ִ��sql���
		$result = $link -> exec(self::$queryStr);
		// ����д��� ����
		self::haveErrorThrowException();
		if($result){
			// ���������Ӱ��ID
			self::$lastInsertId = $link->lastInsertId();
			// ������Ӱ������
			self::$numRows = $result;
			return self::$numRows;
		}
	}

	/*
	 * ����������ѯ��¼
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
	 * �����ֶ�
	 * @param mixed $fields
	 * @return string
	 */
	public static function parseFields($fields){
		// �ж�������
		if(is_array($fields)){
			// ��������
			array_walk($fields,array('pdoMysql','addSpecialChar'));
			// ƴ���ַ���
			$fieldsStr = implode(',',$fields);
		// �ж����ַ���
		}elseif(is_string($fields) && !empty($fields)){
			// �ж�û�з�ҳ
			if(strpos($fields,'`') === false){
				// ����ַ���������
				$fields = explode(',',$fields);
				// ��������
				array_walk($fields, array('pdoMysql','addSpecialChar'));
				// ƴ���ַ���
				$fieldsStr = implode(',',$fields);
			}else{
				$fieldsStr = $fields;
			}
		// �ж��ǿ�ֵ
		}else{
			$fieldsStr = "*";
		}
		return $fieldsStr;
	}

	/*
	 * ͨ�������������ֶ�
	 * @param pointer $value
	 * @return string
	 */
	public static function addSpecialChar(&$value){
		if($value === '*' || strpos($value,'.') !== false || strpos($value, '`') !== false){
			// ��������
		}elseif(strpos($value,'`') === false){
			$value = '`'.trim($value).'`';
		}
		return $value;
	}
	/*
	 *�ͷŽ����
	 */
	public static function free(){
		self::$PDOStatement = null;
	}


	public static function query($sql=''){
		$link = self::$link;
		// �ж�û�����ӱ�ʶ�� ����false
		if(!$link){
			return false;
		}
		// �ж��н�������ͷŽ����
		if(!empty(self::$PDOStatement)){
			self::free();
		}
		// ��sql��� ��ֵ��������
		self::$queryStr = $sql;
		// ׼��sql���
		self::$PDOStatement = $link -> prepare(self::$queryStr);
		// ִ��sql���
		$res = self::$PDOStatement->execute();
		// �ж�sql���ִ���Ƿ�����
		self::haveErrorThrowException();
		// ���û������ֹ�����Ļ� ���ؽ��
		return $res;
	}

	/*
	 * ���������Ϣ
	 */
	public static function haveErrorThrowException(){
		// ȡ��statement�������ӱ�ʶ��
		$obj = empty(self::$PDOStatement)? self::$link: self::$PDOStatement;
		// ȡ�ô�����Ϣ
		$arrError = $obj->errorInfo();
		// ���������Ϣ
		// print_r($arrError);
		if(self::$queryStr == ''){
			self::throw_exception('û�п�ִ�е�SQL���');
			return false;
		}
		if($arrError[0]!=='00000'){
			self::$error = 'SQLSTATE: '.$arrError[0].'<br/>SQL Error: '.$arrError[2].'<br/>Error SQL:'.self::$queryStr.'<br/>';
			self::throw_exception(self::$error);
			return false;
		}
	}
	/*
	 * �Զ����׳�����
	 * @param string $errMsg
	 */
	public static function throw_exception($errMsg){
		echo $errMsg;
	}
}

// ���������ļ�
require_once "config.php";
// ʵ����pdoMysql��
$pdo = new pdoMysql;
// var_dump($pdo);

// ���Է���getAll
// $sql = 'SELECT * FROM user';
// print_r($pdo -> getAll($sql));

// ���Է���getRow
// $sql = 'SELECT * FROM user WHERE id="1"';
// print_r($pdo -> getRow($sql));

// ����execute
// $sql = 'INSERT user(username,password) VALUES("queen4","queen4")';
// var_dump($pdo->execute($sql));
// echo '<br/>';
// echo $pdo::$lastInsertId;

// ����findById
$tabName = 'user';
$priId = 1;
$fields = array('id','age');
var_dump($pdo->findById($tabName,$priId,$fields));
?>