<!DOCTYPE html>
<html>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<head>
    <title>测试支付</title>
</head>
<body>
<?php
header('X-Accel-Buffering: no');
$i = 0;
set_time_limit(0);
ob_end_flush();
require_once __DIR__.DIRECTORY_SEPARATOR."../../includes/api/debug.php";
//echo phpinfo();
while($i < 10000){
    $i++;
    $nonce_str = rand(0,9).rand(0,9);
    //var_dump($nonce_str);
    //continue;
    $post = array(
        "type"         => 'alipay2qr',
        "out_trade_no" => date("YmdHis").$nonce_str,
        "notify_url"   => "https://dev.yykayou.com/dev/api/post.php",
        "return_url"   => "https://dev.yykayou.com/dev/api/post.php",
        "name"         => "VIP会员",
        "attach"       => "说明",
        "money"        => round(rand(10,100),2),
        "sitename"     => "衣库商城",
        "format"       => "json",
        "sign_type"    => "MD5",
    );
//require_once __DIR__.DIRECTORY_SEPARATOR."../../includes/api/debug.php";
    require_once __DIR__.DIRECTORY_SEPARATOR."config.php";
    require_once __DIR__.DIRECTORY_SEPARATOR."libs/epay2.php";
    require_once __DIR__.DIRECTORY_SEPARATOR."libs/function.php";


//构建提交数据
    $aop = new \epay2\epay2($config);

    $aop->post = $post;

//生成签名
    $response = $aop->submit();

    $url = $response['url'];
    $data = $response['data'];
//查看是否提交签名
    if(isset($_REQUEST['sign']) && $_REQUEST['sign'] != ''){
        $data['sign'] = $_REQUEST['sign'];
    }

//可选参数
    $option = "";

    foreach($data as $key => $value){
        if(!empty($value)){
            $option .= "<input type='hidden' name='{$key}' value='{$value}'>";
        }
    }
//print <<< EOF
//        <script>
//        window.onload = function (ev) {
//            document.getElementById('auto').submit();
//        }
//        </script>
//    <form action="{$url}" method="post" id="auto" style="display: none;" target="_self">
//
//{$option}
//
//		<input type="submit" value="提交">
//	</form>
//EOF;
    $url_get = $url."?".http_build_query($data);
    var_dump($url_get);
    //return;
    $response = file_get_contents($url_get);
    //$response = $url_get;

    echo $response;
    flush();
    //sleep(1);
}
?>

</body>
</html>