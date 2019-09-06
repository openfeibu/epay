<?php
exit();
require_once("vendor/autoload.php");

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => 'devyykay_dev',
    'username'  => 'devyykay_dev',
    'password'  => 'azxAABQziT',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);


// Set the event dispatcher used by Eloquent models... (optional)
//use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

//$capsule->setEventDispatcher(new Dispatcher(new Container));

// Make this Capsule instance available globally via static methods... (optional)
$capsule->setAsGlobal();

// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
$capsule->bootEloquent();
$users = Capsule::table('pay_user')->where('id','>',1)->get();
var_dump($users[0]->uuid);
