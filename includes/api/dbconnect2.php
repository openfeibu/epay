<?php
/*
 * 数据库连接
 */
date_default_timezone_set("Asia/Hong_Kong");
require_once __DIR__.DIRECTORY_SEPARATOR."../config.php";
define('DATABASE_NAME',$dbconfig['dbname']);

define('DATABASE_USER',$dbconfig['user']);

define('DATABASE_PASS',$dbconfig['pwd']);

define('DATABASE_HOST',$dbconfig['host']);

define('DATABASE_PORT',$dbconfig['port']);

//require_once __DIR__.'/mysql/class.DBPDO.php';
//$DB = new DBPDO();
//$DB->query("set names utf8");
//$DB->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
//$DB->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

require_once __DIR__.DIRECTORY_SEPARATOR."vendor/autoload.php";
//require_once __DIR__.DIRECTORY_SEPARATOR."debug.php";
use Simplon\Mysql\PDOConnector;
use Simplon\Mysql\Mysql;
use Webpatser\Uuid\Uuid as Uuid;
//如果端口不等于3306就得要另外处理
if($dbconfig['port']!="3306"){
	$dsn = 'mysql:host='.$dbconfig['host'].';port='.$dbconfig['port'].';dbname='.$dbconfig['dbname'];
	$username = $dbconfig['user'];
	$password = $dbconfig['pwd'];
	$options = array(
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
	);
	$DB = new PDO($dsn, $username, $password, $options);
}else{
	//旧流程
	$pdo = new PDOConnector(
		DATABASE_HOST, // server
		DATABASE_USER,      // user
		DATABASE_PASS,      // password
		DATABASE_NAME   // database
	);
	$DB = $pdo->connect('utf8', []); // charset, options
}
$DB->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
$DB->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
//
// you could now interact with PDO for instance setting attributes etc:
// $pdoConn->setAttribute($attribute, $value);
//

$DB2 = new Mysql($DB);
