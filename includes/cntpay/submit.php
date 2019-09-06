<?php
require_once __DIR__.DIRECTORY_SEPARATOR."../api/init.php";

if(!isset($_REQUEST["trade_no"]) || $_REQUEST["trade_no"] == "" || !isset($_REQUEST["money"]) || $_REQUEST["money"] == ""){
    echo '{"status":"false","msg":"参数缺失！"}';
    exit();
}

$key = "z1KQ5k";
$tjurl = "https://cntupay.com/trade/placeOrder";
$post = array(
    "userId" => "M1904120104590148",
    "merchantUserID" => "15871407612",
    "userOrder" => $_REQUEST["trade_no"],
    "number" => number_format($_REQUEST["money"], 2),
    "payType" => "1",
    "isPur" => "1",
    "remark" => "测试",
    "appID" => "PY1zsb6GvU4xAUEs6et6bRv0kG"
);


//var_dump($post);
$str = "";
$str1 = "";
foreach ($post as $k => $v){
    $str .= $v."|";
    $str1 .= $k."|".$v."|";
}

$post["ckValue"] = md5($str.$key);

$back = request_by_curl($tjurl,$post);
$json = json_decode($back,true);
if($json["resultCode"] == "0000"){
    $sql = "UPDATE `pay_order` SET `note1`='{$back}' WHERE `trade_no`='{$_REQUEST["trade_no"]}'";
    $DB->query($sql);
    echo "<script>location.href='{$json["data"]["payPage"]}';</script>";
    exit();
}
else{
    echo '<script>alert("'.$json["resultMsg"].'");history.back(-1);</script>';
    exit();
}

function request_by_curl($url, $post_data = '', $timeout = 30) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    //post提交，否则get
    if ($post_data != '') {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_HEADER, false);
    //跳过SSL验证
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, '0');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, '0');
    $file_contents = curl_exec($ch);
    curl_close($ch);
    return $file_contents;
}

exit();
?>



<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>cnt支付</title>
    <script>
        // window.onload = function(){
        //     document.getElementById("wuyoupay").submit();
        // }
    </script>
</head>
<body>
<div class="container">
    <div class="row" style="margin:15px;0;">
        <div class="col-md-12">
            <form class="form-inline" method="post" id="wuyoupay" action="<?php echo $tjurl; ?>">
                <?php
                foreach ($post as $key => $val) {
                    echo '<input type="hidden" name="' . $key . '" value="' . $val . '">';
                }
                ?>
                <button type="submit" class="btn btn-success btn-lg">扫码支付</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
