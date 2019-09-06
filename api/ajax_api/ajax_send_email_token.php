<?php
require_once '../../includes/QQMailer.php';
require_once __DIR__.DIRECTORY_SEPARATOR."../../includes/api/init.php";
require_once __DIR__.DIRECTORY_SEPARATOR."../../config_base.php";
if(!session_id()){
    session_start();
}
if(isset($_SESSION['userid'])){
    $id = $_SESSION['userid'];
}
else{
    echo '{"success":"false","msg":"非法操作！"}';
    exit();
}
$sql = "SELECT * FROM `pay_user` WHERE `id`='{$id}'";
$row = $DB->query($sql)->fetch();
if(!isset($row["note2"])){
    echo '{"success":"false","msg":"未绑定邮箱不能发送！"}';
    exit();
}
$username = $row["username"];
$email = unserialize($row["note2"]);
$token = create_password();
$now_time = date("Y-m-d H:i:s");
$date = date("Y-m-d H:i:s",strtotime("-5 minute",strtotime($now_time)));
//echo $now_time."<br>".$email["em_time"]."<br>".$date."<br>";
if($email["em_time"]>$date && $email["em_time"]<$now_time){
    echo '{"success":"false","msg":"邮件已经发送过，请不要频繁发送！"}';
    exit();
}
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
$title = $website_name.'(获取商户秘钥令牌)';
// 邮件内容
$content = <<< EOF
<p align="center">尊敬的[$username]用户：<br/>您正在进行获取商户秘钥的令牌操作，您的令牌值是：$token<br/>如果不是本人操作，请检查自己的登录密码是否过于简单！若是请修改密码！以保障您的账户安全。<br/><label style="color: red;">该令牌的有效时长为5分钟，过时将无效。</label><br/><p style='text-align:right'>-------- 百度经验 敬上</p></p>
EOF;
// 发送QQ邮件
if($mailer->send($email["email"], $title, $content)){
    $email["em_token"] = $token;
    $email["em_time"] = date("Y-m-d H:i:s");
    $email = serialize($email);//数组序列化
    $sql2 = "UPDATE `pay_user` SET `note2`='{$email}' WHERE `id`='{$id}'";//写入数据库
    $numrows = $DB->query($sql2);
    echo '{"success":"true","msg":"邮件发送成功！"}';
}
else{
    echo '{"success":"false","msg":"邮件发送失败！"}';
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
