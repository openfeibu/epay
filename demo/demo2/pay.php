<?php
if(isset($_REQUEST['pid']) && isset($_REQUEST['type']) && isset($_REQUEST['out_trade_no']) && isset($_REQUEST['notify_url']) && isset($_REQUEST['return_url']) && isset($_REQUEST['name']) && isset($_REQUEST['money']) && isset($_REQUEST['sign'])){
    $type = $_REQUEST['type'];
    $out_trade_no = $_REQUEST['out_trade_no'];
    $name = $_REQUEST['name'];
    $money = $_REQUEST['money'];

    //可选参数
    if(isset($_REQUEST['sitename'])){
        $sitename = $_REQUEST['sitename'];
    }else{
        $sitename = '';
    }
}else{
    echo "参数不完整。";
    exit();
}
//require_once __DIR__.DIRECTORY_SEPARATOR."../../includes/api/debug.php";
require_once __DIR__.DIRECTORY_SEPARATOR."config.php";
require_once __DIR__.DIRECTORY_SEPARATOR."libs/epay2.php";
require_once __DIR__.DIRECTORY_SEPARATOR."libs/function.php";


//构建提交数据
$config['pid'] = $_REQUEST['pid'];
$config['key'] = $_REQUEST['key'];
$aop = new \epay2\epay2($config);
$post = $_REQUEST;
if(isset($post['key'])) unset($post['key']);

$aop->post = $post;

//生成签名
$response = $aop->submit();
//var_dump($response);

$url = $response['url'];
$data = $response['data'];
//查看是否提交签名
if(isset($_REQUEST['sign']) && $_REQUEST['sign'] != ''){
    $data['sign'] = $_REQUEST['sign'];
}

//可选参数
$option = "";

//初始化
$curl = curl_init();
//设置抓取的url
curl_setopt($curl, CURLOPT_URL, $url);
//设置头文件的信息作为数据流输出
curl_setopt($curl, CURLOPT_HEADER, 0);
//设置获取的信息以文件流的形式返回，而不是直接输出。
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//设置post方式提交
curl_setopt($curl, CURLOPT_POST, 1);
//设置post数据
$post_data = $data;
curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
//执行命令
$data = curl_exec($curl);
//关闭URL请求
curl_close($curl);
//显示获得的数据
echo $data;
?>