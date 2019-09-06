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
//$DB->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
//$DB->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

require_once __DIR__.DIRECTORY_SEPARATOR."vendor/autoload.php";

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

//使用localhost
$dbconfig1 = array(
    'driver'    => 'mysql',
    'host'      => DATABASE_HOST,
    'database'  => DATABASE_NAME,
    'username'  => DATABASE_USER,
    'password'  => DATABASE_PASS,
    'port'      => DATABASE_PORT,
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
);

//读写分离
$dbconfig2 = array(
    'driver'    => 'mysql',
    'write'     => [
        'host' => DATABASE_HOST,
    ],
    'read'      => [
        'host' => "120.79.205.29",
    ],
    'database'  => DATABASE_NAME,
    'username'  => DATABASE_USER,
    'password'  => DATABASE_PASS,
    'port'      => DATABASE_PORT,
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
);


//$capsule->addConnection([
//    'driver'    => 'mysql',
//    'host'      => "127.0.0.1",
//    'database'  => DATABASE_NAME,
//    'username'  => DATABASE_USER,
//    'password'  => DATABASE_PASS,
//    'port'      => "3307",
//    'charset'   => 'utf8',
//    'collation' => 'utf8_unicode_ci',
//    'prefix'    => '',
//]);
$capsule->addConnection($dbconfig2);


// Set the event dispatcher used by Eloquent models... (optional)
//use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

//$capsule->setEventDispatcher(new Dispatcher(new Container));

// Make this Capsule instance available globally via static methods... (optional)
$capsule->setAsGlobal();

// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
$capsule->bootEloquent();
//$users = Capsule::table('pay_user')->where('id','>',1)->get();
//var_dump($users[0]);
