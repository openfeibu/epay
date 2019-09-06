<?php
exit();
require './includes/common.php';

$thtime = date("Y-m-d H:i:s",time() - 3600 * 6);

$DB->exec("delete from pay_order where status=0 and addtime<'{$thtime}'");

$rs = $DB->query("SELECT * from pay_user where money!='0.00'");

$allmoney = 0;
while($row = $rs->fetch()){
    $allmoney += $row['money'];
}
file_put_contents(SYSTEM_ROOT.'all.txt',$allmoney);

$rs = $DB->query("SELECT * from pay_settle");
$allmoney = 0;
while($row = $rs->fetch()){
    $allmoney += $row['money'];
}
file_put_contents(SYSTEM_ROOT.'settle.txt',$allmoney);

echo 'ok';