<?php
/**
 * 登录
 **/
header("Content-Type: text/html; charset=utf-8");
include("../includes/common.php");
require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/function.php";
//require_once __DIR__."/../includes/api/debug.php";
$self_url = $_SERVER['PHP_SELF'];
if(isset($_POST['user']) && isset($_POST['pass'])){
    $user = daddslashes($_POST['user']);
    $pass = daddslashes($_POST['pass']);
//if(!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])[a-zA-Z\d!@#$%^&*]{8,20}/',$pass)){
//
//    exit("<script language='javascript'>alert('您的密码过于简单，请联系管理员修改！');</script>");
//}
    $city = get_ip_city($clientip);
    $data = $_REQUEST;
    $sql = "SELECT * FROM `pay_user` WHERE `id` = '{$user}' limit 1";
    $userrow = $DB->query($sql)->fetch();
    $sid=session_id();
    $scancode="https://openauth.alipay.com/oauth2/publicAppAuthorize.htm?app_id=2019022163313167&scope=auth_base&redirect_uri=".urlencode($website_urls)."zfbsj.php%3ftrade_no%3d{$sid}%26type%3d3%26domain%3d".urlencode($website_urls);

    $cwcishu = $userrow['un_reason'];
    if($cwcishu>3){

        exit("<script language='javascript'>alert('您的账户已被锁定，请联系管理员！');history.go(-1);</script>");
    }

    if($userrow){
//        //记录日志
//        $query = "insert into `panel_log` (`uid`,`type`,`date`,`city`,`data`) values ('{$user}','登录用户中心','{$date}','{$city}','{$data}')";
//        $DB->query($query);

        if($agent_sms_switch){
            //账号存在
            if(!isset($_REQUEST["sms_key"]) || $_REQUEST["sms_key"] == ""){
                //登录日志
                login_log(false);
                exit("<script>alert('请输入验证码');history.go(-1);</script>");
            }
            $file_name = "../config/cache/".md5("agent_sms_code_login").".php";
            $ser = unserialize(str_replace("<?php exit(); ?>","",file_get_contents($file_name)) );
            $sessionid = session_id();
            if($_REQUEST["sms_key"] != $ser['token'] || $sessionid != $ser['sessionid']){
                //登录日志
                login_log(false);
                exit("<script>alert('验证码错误或者与发送短信用户不匹配！请重试！');history.go(-1);</script>");
            }
            if($pass == $userrow['pwd']){
//                $pass = $userrow['key'];
//                var_dump($_SESSION['Oauth_alipay_uid']);exit;
//                if($user_id = $_SESSION['Oauth_alipay_uid']){
//                    $sql = "update `pay_user` set `alipay_uid` ='$user_id' where `id`='$user'";
//                    $DB->exec($sql);
//                    unset($_SESSION['Oauth_alipay_uid']);
//                }

                $sqls = "update `pay_user` set `un_reason` ='0' where `id`='$user'";
                $DB->exec($sqls);
                // $session = md5($user.$pass.$password_hash);
                // $expiretime = time() + 604800;
                // $token = authcode("{$user}\t{$session}\t{$expiretime}",'ENCODE',SYS_KEY);
                // setcookie("user_token",$token,$expiretime);
                // $_SESSION['p_id'] = $user;
                // setcookie("p_id",$user);
                \epay\start_session(43200);
                $_SESSION['is_user'] = true;
                $_SESSION['userid'] = $user;
                $_SESSION['uuid'] = $userrow['uuid'];
                $_SESSION['user'] = $userrow['id'];                         //用户的id
                $_SESSION['uid'] = $userrow['uid'];                         //代理商的id
                $_SESSION['agentuuid'] = $userrow['agentuuid'];
                $exptime=time() + 300;
                $sid=session_id();
                $query = "replace into `pay_online` (`sid`, `username`, `ip`, `exptime`) VALUES  ('$sid', '$user', '$clientip', '$exptime')";
                $DB->query($query);
                login_log(true);
                header("Location: ./");
                //exit("<script language='javascript'>alert('登录用户中心成功！');window.location.href='./';</script>");
            }else{


                if($cwcishu==""){
                    $cwcishu=0;
                }

                if($cwcishu>2){
                    $sqls = "update `pay_user` set `active` ='2' where `id`='$user'";
                    $DB->exec($sqls);
                }
                $cwcishu=$cwcishu+1;

                $sqlg = "update `pay_user` set `un_reason` ='$cwcishu' where `id`='$user'";
                $DB->exec($sqlg);


                login_log(false);
                //密码错误
                exit("<script language='javascript'>alert('用户名或密码不正确！');history.go(-1);</script>");
            }
        }
        else{
            //账号存在
            if($pass == $userrow['pwd']){
//                $pass = $userrow['key'];
//                var_dump($_SESSION['Oauth_alipay_uid']);exit;
//                if($user_id = $_SESSION['Oauth_alipay_uid']){
//                    $sql = "update `pay_user` set `alipay_uid` ='$user_id' where `id`='$user'";
//                    $DB->exec($sql);
//                    unset($_SESSION['Oauth_alipay_uid']);
//                }

                $sqls = "update `pay_user` set `un_reason` ='0' where `id`='$user'";
                $DB->exec($sqls);
                // $session = md5($user.$pass.$password_hash);
                // $expiretime = time() + 604800;
                // $token = authcode("{$user}\t{$session}\t{$expiretime}",'ENCODE',SYS_KEY);
                // setcookie("user_token",$token,$expiretime);
                // $_SESSION['p_id'] = $user;
                // setcookie("p_id",$user);
                \epay\start_session(43200);
                if($userrow['type']==2){
                    echo '  <center> <div class="wrapper text-center" ><br><br>
               <h2>支付宝扫码登录</h2><br>
            </div>
           <div> <img id="show_qrcode" src="../api/qrcode.php?data=' .urlencode($scancode). '" width="300"
                       height="210" style="display: block; width: 310px; height: 270px;"></div></center>
                       <script src="../libs/jquery/jquery/dist/jquery.js"></script>
<script>

    function checkdata() {
        $.post(
            "../api/getlogin2.php",
            {
                userid: "'. $sid. '",
            },
            function (data) {
                if (data == "1") {
                    alert("登录成功!");
                    window.location.href="index.php";
                }
            }
        );
    }
    myTimer = window.setInterval(function () {
        checkdata();
    }, 3000);
</script>';
                    return;
                }
                $_SESSION['is_user'] = true;
                $_SESSION['userid'] = $user;
                $_SESSION['uuid'] = $userrow['uuid'];
                $_SESSION['user'] = $userrow['id'];                         //用户的id
                $_SESSION['uid'] = $userrow['uid'];                         //代理商的id
                $_SESSION['agentuuid'] = $userrow['agentuuid'];
                $exptime=time() + 300;
                $sid=session_id();
                $query = "replace into `pay_online` (`sid`, `username`, `ip`, `exptime`) VALUES  ('$sid', '$user', '$clientip', '$exptime')";
                $DB->query($query);
                login_log(true);
                header("Location: ./");
                //exit("<script language='javascript'>alert('登录用户中心成功！');window.location.href='./';</script>");
            }else{


                if($cwcishu==""){
                    $cwcishu=0;
                }

                if($cwcishu>2){
                    $sqls = "update `pay_user` set `active` ='2' where `id`='$user'";
                    $DB->exec($sqls);
                }
                $cwcishu=$cwcishu+1;

                $sqlg = "update `pay_user` set `un_reason` ='$cwcishu' where `id`='$user'";
                $DB->exec($sqlg);


                login_log(false);
                //密码错误
                exit("<script language='javascript'>alert('用户名或密码不正确！');history.go(-1);</script>");
            }
        }

    }else{
        login_log(false,false);
        //账号不存在
        exit("<script language='javascript'>alert('用户名或密码不正确！');history.go(-1);</script>");
    }
}elseif(isset($_REQUEST['logout'])){
    $sid=session_id();
    $query = "delete from `pay_online`  where sid='$sid'";
    $DB->query($query);
    setcookie("user_token","",time() - 604800);
    //不处理全部session，改为销毁商户登录相关Session
    //session_destroy();
    unset( $_SESSION['is_user']);
    unset( $_SESSION['userid']);
    unset($_SESSION['uuid']);
    unset( $_SESSION['user']);
    unset( $_SESSION['uid']);
    unset( $_SESSION['agentuuid']);
    $msg = "您已成功注销本次登录";
    //exit("<script language='javascript'>alert('您已成功注销本次登录！');window.location.href='./login.php';</script>");
}elseif(isset($_SESSION['userid']) && $_SESSION['userid'] != ''){
    header("Location: ./");
    //exit("<script language='javascript'>alert('您已登录！');window.location.href='./';</script>");
}

function login_log($status,$type=true){
    global $clientip;
    global $data;
    global $user;
    global $date;
    global $city;
    global $DB;
    $data["status"] = $status;

    //记录失败日志
    if($type){
        if(!$status){
            $data["error_msg"] = "密码错误";
        }
        $data = $clientip.json_encode($data,JSON_UNESCAPED_UNICODE);
        $query = "insert into `panel_log` (`uid`,`type`,`date`,`city`,`data`) values ('{$user}','登录用户中心','{$date}','{$city}','{$data}')";
    }
    else{
        $data["error_msg"] = "用户名不正确";
        $data = $clientip.json_encode($data,JSON_UNESCAPED_UNICODE);
        $query = "insert into `panel_log` (`uid`,`type`,`date`,`city`,`data`) values ('0','登录用户中心','{$date}','{$city}','{$data}')";
    }
    $DB->query($query);
}
$title = '商户登录中心';
//include './head.php';
isset($msg) ? : $msg = "";
$sid=session_id();
?>
<!DOCTYPE html>
<html lang="en" class="">
<head>
    <meta charset="utf-8"/>
    <title><?php echo $title; ?> | <?php echo $conf['web_name'] ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
    <link rel="stylesheet" href="../libs/assets/animate.css/animate.css" type="text/css">
    <link rel="stylesheet" href="../libs/assets/font-awesome/css/font-awesome.min.css" type="text/css">
    <link rel="stylesheet" href="../libs/assets/simple-line-icons/css/simple-line-icons.css" type="text/css">
    <link rel="stylesheet" href="../libs/jquery/bootstrap/dist/css/bootstrap.css" type="text/css"/>

    <link rel="stylesheet" href="css/font.css" type="text/css"/>
    <link rel="stylesheet" href="css/app.css" type="text/css"/>
    <style>input:-webkit-autofill {
            -webkit-box-shadow: 0 0 0px 1000px white inset;
            -webkit-text-fill-color: #333;
        }
        .yincang
        {
            display:none;
        }
    </style>
</head>
<body>
<!--背景图片-->


<div class="app app-header-fixed  ">
    <div class="container w-xxl w-auto-xs" ng-controller="SigninFormController"
         ng-init="app.settings.container = false;">
        <span class="navbar-brand block m-t"><?php echo $conf['web_name'] ?></span>
        <div class="m-b-lg">
            <div class="wrapper text-center">
                <strong>请输入您的商户信息</strong>
            </div>
            <form name="form" class="form-validation" method="post" action="">
                <div class="text-danger wrapper text-center" ng-show="authError">

                </div>
                <div class="list-group list-group-sm swaplogin">
                    <div class="alert-success">
                        <?php echo $msg; ?>
                    </div>
                    <div class="list-group-item">
                        <input type="text" name="user" id="user_name" placeholder="商户ID" class="form-control no-border" required>
                    </div>
                    <div class="list-group-item">
                        <input type="password" name="pass" placeholder="密码" class="form-control no-border" required>
                    </div>
                    <?php
                    if($agent_sms_switch == true){
                        ?>
                        <script>
                            function send_sms() {
                                if($("#user_name").val()==""){
                                    alert("用户ID不可为空！");
                                    return;
                                }
                                $("#send_sms_key").attr("disabled","disabled");
                                $.ajax({
                                    url:"../api/ajax_api/ajax_agent_send_sms_lgoin_api.php",
                                    type:"get",
                                    dataType:"json",
                                    data:{
                                        user_name:$("#user_name").val()
                                    },
                                    success:function(data){
                                        if(data.success == "true"){
                                            //$("#send_sms_key").removeAttr("disabled");
                                            switch(data.num){
                                                case "1":stime(1);break;
                                                case "2":stime(2);break;
                                                case "3":stime(3);break;
                                                case "4":stime(4);break;
                                                case "5":stime(5);break;
                                            }
                                            alert(data.msg);
                                        }
                                        else{
                                            alert(data.msg);
                                        }
                                    }
                                })
                            }

                            function stime(i,k=1){
                                var time = i * 60;
                                l = time-k;
                                $("#send_sms_key").html(l+"秒后可重试获取！");
                                k++;
                                if(k>time){
                                    $("#send_sms_key").removeAttr("disabled");
                                    $("#send_sms_key").html("获取短信验证码");
                                    return;
                                }
                                setTimeout("stime("+i+","+k+")",1000);
                            }
                        </script>
                        <div style="width: 300px;height:34px;text-align: left;margin-bottom: 20px;margin-top: 10px;">
                            <input type="text" name="sms_key" placeholder="短信验证码" ng-model="user.password" style="width:130px;height:34px;padding:6px 12px;border: 1px ;border: 1px solid #ccc;">&nbsp;&nbsp;
                            <button class="btn btn-primary" onclick="send_sms()" id="send_sms_key" type="button"><span id="fasanniu">获取短信验证码</span></button>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <button type="submit" class="btn btn-lg btn-primary btn-block" ng-click="login()"
                        ng-disabled='form.$invalid'>立即登录
                </button>
                <!--
<a href="oauth.php" ui-sref="access.signup" class="btn btn-lg btn-default btn-block <?php echo isset($_GET['connect']) ? 'hide' : null; ?>"><img src="../assets/icon/alipay.ico" width="28px">支付宝快捷登录</a>
<a href="../user/reg.php?my=add" ui-sref="access.signup" class="btn btn-lg btn-default btn-block">自助申请</a>
-->

            </form>

            <div id="saoma" class="yincang">
                <div class="wrapper text-center" >
                    <strong>支付宝扫码登录</strong>
                </div>
                <div> <img id="show_qrcode" src="../api/qrcode.php?data=<?php echo urlencode($scancode); ?>" width="300"
                           height="210" style="display: block; width: 310px; height: 270px;"></div>

            </div>
        </div>
        <div class="text-center">
            <p>
                <small class="text-muted"><?php echo $conf['web_name'] ?><br>&copy; 2016~2018</small>
            </p>
        </div>
    </div>


</div>

<script src="../libs/jquery/jquery/dist/jquery.js"></script>
<script src="../libs/jquery/bootstrap/dist/js/bootstrap.js"></script>
<script src="js/ui-load.js"></script>
<script src="js/ui-jp.config.js"></script>
<script src="js/ui-jp.js"></script>
<script src="js/ui-nav.js"></script>
<script src="js/ui-toggle.js"></script>
<script src="js/ui-client.js"></script>


</body>
</html>
