<?php
$dk80 = exec('netstat -an |grep 80 |wc -l');//获取80端口的连接数
$httpd = exec('ps -ef|grep httpd|wc -l');//获取80端口的连接数
//echo '只读取结果的最后一行'.$out01."\n";
//var_dump('以数组的形式返回所有的输出');
if(!isset($_REQUEST["token"]) || $_REQUEST["token"] != "sAt9xV4iLi5xNXbNMO7tHpp0MVSyQQu9dWjn4ilksZQmidjIxTLnPF7yao10NU3y"){
    echo error;
    exit();
}
exec("netstat -n | awk '/^tcp/ {++S[\$NF]} END {for(a in S) print a, S[a]}'",$arr);
$data = null;
foreach ($arr as $v){
    $ttt = explode(" ",$v);
    $data[$ttt[0]] = $ttt[1];
}
$data["dk80"] = $dk80;
$data["httpd"] = $httpd;
echo json_encode($data,JSON_UNESCAPED_UNICODE);
