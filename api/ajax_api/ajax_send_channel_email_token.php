<?php
error_reporting(0);
require_once '../../includes/QQMailer.php';
require_once __DIR__.DIRECTORY_SEPARATOR."../../includes/api/init.php";
require_once __DIR__.DIRECTORY_SEPARATOR."../../config_base.php";
if(!session_id()){
    session_start();
}
if(isset($_SESSION['admin_id'])){
    $id = $_SESSION['admin_id'];
}
else if(isset($_SESSION['userid'])){
    $id = $_SESSION['userid'];
}
else{
    echo '{"success":"false","msg":"非法操作！"}';
    exit();
}
if(!isset($_REQUEST["email"])){
    echo '{"success":"false","msg":"参数缺失！"}';
    exit();
}
$email1 = daddslashes($_REQUEST["email"]);
$file1 = "../../config/cache/".md5('email_white_list');
if(!file_exists($file1)){
    echo '{"success":"false","msg":"空白的邮箱白名单！"}';
    exit();
}
$data1 = unserialize(file_get_contents($file1));//反序列化邮箱配置
$success = false;
foreach ($data1 as $value){
    if($value["email"]==$email1){
        $success = true;
        break;
    }
}
if($success){
}
else{
    echo '{"success":"false","msg":"未添加白名单的邮箱属于非法操作！"}';
    exit();
}
$username = "";
$email = "";
if(isset($_SESSION['admin_id'])){
    $sql = "SELECT * FROM `pay_admin` WHERE `id`='{$id}'";
    $row = $DB->query($sql)->fetch();
    $username = $row["admin_user"];
    //$email = unserialize($row["note2"]);
}
else if(isset($_SESSION['userid'])){
    $sql = "SELECT * FROM `pay_user` WHERE `id`='{$id}'";
    $row = $DB->query($sql)->fetch();
    $username = $row["username"];
    $email = unserialize($row["note2"]);
}
$token = create_password();



//获取发送邮箱的配置
$file = "../../config/cache/".md5('QQ_Email_Code');
if(!file_exists($file)){
    echo '{"success":"false","msg":"管理员未绑定发送主邮箱，请联系他！"}';
    exit();
}
$data = unserialize(file_get_contents($file));//反序列化邮箱配置


// 实例化 QQMailer
$mailer = new QQMailer(true);
//设置邮箱配置
$mailer->set_config($data["QQEmail"],$data["QQCode"],$data["QQName"]);

// 添加附件
//$mailer->addFile('20130VL.jpg');
// 邮件标题
$title = $website_name.'(获取修改通道令牌)';
// 邮件内容
$content = <<< EOF
<p align="center">尊敬的[$username]用户：<br/>您正在进行获取修改通道的令牌操作，您的令牌值是：$token<br/>如果不是本人操作，请忽略该邮件！<br/><label style="color: red;">该令牌的有效时长为5分钟，过时将无效。</label><br/><p style='text-align:right'></p></p>
EOF;
// 发送QQ邮件
if(isset($_SESSION['admin_id'])){
    $id11 = ll($email1);
    if($mailer->send($email1, $title, $content)){


        echo '{"success":"true","msg":"邮件发送成功！","back_id":"'.$id11.'"}';
    }
    else{
        echo '{"success":"false","msg":"邮件发送失败！"}';
    }
}
else if(isset($_SESSION['userid'])){
    $id11 = ll($email1);
    if($mailer->send($email["email"], $title, $content)){


        echo '{"success":"true","msg":"邮件发送成功！","back_id":"'.$id11.'"}';
    }
    else{
        echo '{"success":"false","msg":"邮件发送失败！"}';
    }
}

//公用
function ll($email){
    global $token;
    global $id;
    global $DB;
    $ip = get_ip();
    $city = get_ip_city1($ip);
    $data["token"] = $token;
    $data["email"] = $email;
    $data["time"] = date("Y-m-d H:i:s");
    $data1 = serialize($data);//数组序列化
    $file2 = "../../config/cache/".md5('Channel_Update_Token');
    file_put_contents($file2,$data1);//token写日志
    $ct = json_decode($city,true);
    if($ct["code"]==0){
        $city1 = $ct["data"]["country"]."->".$ct["data"]["region"]."->".$ct["data"]["city"]."->".$ct["data"]["county"]."->".$ct["data"]["isp"];
    }else{
        $city1 = "未查询出城市";
    }

    $sql = "INSERT INTO `pay_smslog`(`userid`, `type`, `createtime`, `endtime`, `expiredtime`, `ip`, `city`, `code`, `data`) VALUES 
('{$id}','修改商户通道@{$email}','{$data["time"]}','{$data["time"]}','{$data["time"]}','{$ip}','{$city1}','{$token}','{$city}')";
    $DB->query($sql);
    return $DB->lastInsertId();
}


function get_ip(){
    //判断服务器是否允许$_SERVER
    $realip = $_SERVER["REMOTE_ADDR"];

    return $realip;
}

function get_ip_city1($ip){
    $url = 'http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip='; //这个地址不能用了
    $url = "http://ip.taobao.com/service/getIpInfo.php?ip={$ip}";
    // $city = curl_get($url);//此函数有问题
    $city = file_get_contents($url);
    return $city;
}

// 生成一个随机令牌
function create_password( $length = 6 ) {
    // 密码字符集，可任意添加你需要的字符
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = '';
    for ( $i = 0; $i < $length; $i++ )  {
        // 这里提供两种字符获取方式
        // 第一种是使用 substr 截取$chars中的任意一位字符；
        // // 第二种是取字符数组 $chars 的任意元素
        $password .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        $password .= $chars[ mt_rand(0, strlen($chars) - 1) ];
    }
    return $password;

}
