<?php
/**
 * Google验证器
 **/
include("../includes/common.php");
if(!isset($_SESSION['is_admin'])){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
$admin_userid = $_SESSION['admin_userid'];
$title = 'Google验证器';
include './head.php';
$self_url = $_SERVER['PHP_SELF'];
require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/debug.php";
//谷歌验证器时区
date_default_timezone_set("Asia/Hong_Kong");
//var_dump(date_default_timezone_get());

//查找pay_admin表
$sql = "SELECT * FROM `pay_admin` WHERE `id` = '{}' LIMIT 1";
$result = $DB->query($sql);
$user = $result->fetch();


//Google 身份验证器
$googleauth = $user['googleauth'];
require_once __DIR__.DIRECTORY_SEPARATOR."../includes/PHPGangsta/GoogleAuthenticator.php";
$ga = new PHPGangsta_GoogleAuthenticator();
$googleauth_echo = "<span style=\"float: left;color: red;\">此二维码通过验证后，将不再显示，请妥善保存。</span>";
if($googleauth == ''){
    $secret = $ga->createSecret();
    $ga_data = array();
    $ga_data['status'] = "off";
    $ga_data['secret'] = $secret;
    $ga_data['valid'] = false;
    $ga_data_json = json_encode($ga_data);
    $sql = "UPDATE `pay_admin` SET `googleauth` = '{$ga_data_json}' WHERE `id` = '{$admin_userid}' ";
    $DB->query($sql);
    $googleauth_checked = "";
    $googleauth_value = "";
    $secret_show = $secret;
    $urlencoded = urlencode('otpauth://totp/'.$website_name.'?secret='.$secret.'');
}else{
    $googleauth = json_decode($googleauth,true);
    if($googleauth['status'] == "on"){
        $googleauth_checked = "checked='checked'";
    }else{
        $googleauth_checked = "";
    }
    $secret = $googleauth['secret'];
    if($googleauth['valid'] == false || ($googleauth['valid'] == true && $googleauth['status'] == "off")){
        //var_dump($googleauth);
        //exit();
        //未验证的Google验证器，重置密钥
        if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'googleauth'){
            //不重置
            $urlencoded = "";
            $secret_show = "此密钥不再显示";
        }elseif($googleauth['valid'] == true && !isset($_REQUEST['googleauthreset']) && !isset($_REQUEST['googleauth2'])){
            //不重置
            $urlencoded = "";
            $secret_show = "此密钥不再显示";
        }else{
            $secret = $ga->createSecret();
            $ga_data = array();
            $ga_data['status'] = "off";
            $ga_data['secret'] = $secret;
            $ga_data['valid'] = false;
            $ga_data_json = json_encode($ga_data);
            $sql = "UPDATE `pay_admin` SET `googleauth` = '{$ga_data_json}' WHERE `id` = '{$admin_userid}' ";
            $DB->query($sql);
            $urlencoded = urlencode('otpauth://totp/'.$website_name.'?secret='.$secret.'');
            $secret_show = $secret;
        }
    }else{
        $urlencoded = "";
        $secret_show = "此密钥不再显示。";
    }
    if($googleauth['status'] == 'off'){
        $googleauth_echo .= "<a href='{$self_url}?googleauthreset' style=\"color: blue\">重置Google验证器</a>";
    }
}

if(isset($_REQUEST['googleauth2']) && $_REQUEST['googleauth2'] != ''){
    $googleauth2 = daddslashes($_REQUEST['googleauth2']);
    $onecode = $ga->getCode($secret);
    //var_dump($onecode);
    //var_dump($googleauth2);
    //var_dump($secret);
    if($onecode != $googleauth2){
        exit("<script language='javascript'>alert('验证码不正确，请重试！');history.go(-1);</script>");
    }

    if(isset($_REQUEST['googleauth_status'])){
        //验证器打开
        $status = "on";
    }else{
        //验证器关闭
        $status = "off";
    }
    $ga_data = array();
    $ga_data['status'] = $status;
    $ga_data['secret'] = $secret;
    $ga_data['valid'] = true;
    $ga_data_json = json_encode($ga_data);
    $sql = "UPDATE `pay_admin` SET `googleauth` = '{$ga_data_json}' WHERE `id` = '{$admin_userid}' ";
    $DB->query($sql);
    exit("<script language='javascript'>alert('保存成功！');location.href = '{$self_url}';</script>");
}else{
    $sql = "";
    //exit("<script language='javascript'>alert('未输入验证码！');history.go(-1);</script>");
}


//录入数据库
if($sql != ""){
    $sqs = $DB->exec($sql);
    exit("<script language='javascript'>alert('保存成功！');history.go(-1);</script>");
}


isset($msg) ? $msg = "<div class=\"alert alert-info\">{$msg}</div>" : $msg = "";
$allowmodi = "";


print <<< EOF
 <div id="content" class="app-content" role="main">
    <div class="app-content-body ">

<div class="bg-light lter b-b wrapper-md hidden-print">
  <h1 class="m-n font-thin h3">{$title}</h1>
</div>

<div class="wrapper-md control">
    {$msg}
	<div class="panel panel-default">
		<div class="panel-heading font-bold">
			{$title}
		</div>
		<div class="panel-body">
            <div class="line line-dashed b-b line-lg pull-in"></div>
            <h4>Google验证器</h4>
            <form class="form-horizontal devform" action="{$self_url}" method="get">
                <input type="hidden" name="action" value="googleauth">
                <div class="form-group">
                    <label class="col-sm-2 control-label">是否启用Google身份验证器</label>
                    <div class="col-sm-3">
                        是否启用：
                        <label class="i-switch m-t-xs m-r">
                          <input {$googleauth_checked} type="checkbox" name="googleauth_status" id="googleauth">
                          <i></i>
                        </label>
                    </div>
                    <div class="col-sm-3" id="googleqrcode" style="display: none;">
                        <img src="../api/qrcode.php?data={$urlencoded}" style="float: left;">
                        {$googleauth_echo}
                        <input class="form-control" type="text" value="{$secret_show}" name="googleauth1" placeholder="" disabled="disabled">
                    </div>
                    <div class="col-sm-3">
                        <input class="form-control" type="text" value="" name="googleauth2" placeholder="请输入验证器显示的验证码">
                    </div>
                    <script>
                        document.getElementById("googleauth").onclick = function() {
                            if(this.checked){
                                document.getElementById("googleqrcode").style.display = "block";
                            }else{
                                document.getElementById("googleqrcode").style.display = "none";
                            }
                        }
                    </script>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-4">
                            <input type="submit" name="submit" value="确定修改" class="btn btn-primary form-control"/>
                        <br/>
                    </div>
                </div>
            </form>
		</div>
	</div>
</div>
    </div>
  </div>
EOF;


include __DIR__.'/foot.php';
?>
<script>
    account = document.getElementById("account");
    account.className = "active";
    userinfo = account.getElementsByTagName("li")[3];
    userinfo.className = "active";
</script>
