<?php
require_once __DIR__."/index.php";
//require_once __DIR__."/../../includes/api/debug.php";
$a = new \alipayTransfer\Alipay();
$b = $a->transfer("123456@qfdsafdsaftq.com","0.01","123");

var_dump($a);
var_dump($b);
