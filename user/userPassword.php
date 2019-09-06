<?php
/**
 * 基本信息
 **/
include("../includes/common.php");
if(!isset($_SESSION['userid'])){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
$title = '修改密码';
include './head.php';
$self_url = $_SERVER['PHP_SELF'];
//require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/debug.php";
//谷歌验证器时区
date_default_timezone_set("Asia/Hong_Kong");
//var_dump(date_default_timezone_get());
$type = $userrow['type'];

//查找pay_user表
$sql = "SELECT * FROM `pay_user` WHERE `id` = '{$pid}' LIMIT 1";
$result = $DB->query($sql);
$user = $result->fetch();

isset($_REQUEST['action']) ? $action = $_REQUEST['action'] : $action = "";
switch($action){
    case 'login_pwd':
        //修改登录密码
        if(!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])[a-zA-Z\d!@#$%^&*]{8,20}/',$_REQUEST['login_pwd1'])){
            echo "您设置的密码过于简单，请重新设置。";
            return;
        }
        if(isset($_REQUEST['login_pwd0']) && isset($_REQUEST['login_pwd1']) && isset($_REQUEST['login_pwd2'])){
            $login_pwd0 = daddslashes($_REQUEST['login_pwd0']);
            $login_pwd1 = daddslashes($_REQUEST['login_pwd1']);
            $login_pwd2 = daddslashes($_REQUEST['login_pwd2']);
        }else{
            exit("<script language='javascript'>alert('必填项不能为空！');history.go(-1);</script>");
        }
        if($login_pwd1 != $login_pwd2 || $login_pwd2 == ''){
            exit("<script language='javascript'>alert('两次输入的新密码不一致或为空！');history.go(-1);</script>");
        }
        if($login_pwd0 != $userrow['pwd']){
            exit("<script language='javascript'>alert('原取现密码不正确！');history.go(-1);</script>");
        }

        $sql = "UPDATE `pay_user` SET `pwd` = '{$login_pwd1}' WHERE `id`='{$pid}' ;";
        break;
    case 'cash_pwd':
        //修改密码
        if(isset($_REQUEST['cash_pwd0']) && isset($_REQUEST['cash_pwd1']) && isset($_REQUEST['cash_pwd2'])){
            $cash_pwd0 = daddslashes($_REQUEST['cash_pwd0']);
            $cash_pwd1 = daddslashes($_REQUEST['cash_pwd1']);
            $cash_pwd2 = daddslashes($_REQUEST['cash_pwd2']);
        }else{
            exit("<script language='javascript'>alert('必填项不能为空！');history.go(-1);</script>");
        }
        if($cash_pwd1 != $cash_pwd2 || $cash_pwd1 == ''){
            exit("<script language='javascript'>alert('两次输入的新密码不一致或为空！');history.go(-1);</script>");
        }
        if($cash_pwd0 != $userrow['cash_pwd']){
            exit("<script language='javascript'>alert('原取现密码不正确！');history.go(-1);</script>");
        }

        $sql = "update `pay_user` set `cash_pwd` = '{$cash_pwd1}' where `id`='{$pid}'";
        break;
    default:
        $sql = "";
        break;
}

//录入数据库
if($sql != ""){
    $sqs = $DB->exec($sql);
    exit("<script language='javascript'>alert('保存成功！');history.go(-1);</script>");
}


isset($msg) ? $msg = "<div class=\"alert alert-info\">{$msg}</div>" : $msg = "";
$userrow['allowmodi'] == 1 ? $allowmodi = "" : $allowmodi = "disabled";


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
            <!--修改基本信息-->
				<h4>修改登录密码：</h4>
            <form class="form-horizontal devform" action="{$self_url}" method="post">
                <input type="hidden" name="action" value="login_pwd">
				<div class="form-group">
					<label class="col-sm-2 control-label">登录密码</label>
					<div class="col-sm-3">
						<input class="form-control" name="login_pwd0"  type="password" value="" placeholder="原登录密码">
					</div>
					<div class="col-sm-3">
						<input class="form-control" name="login_pwd1"  type="password" value="" placeholder="新登录密码">
					</div>
					<div class="col-sm-3">
						<input class="form-control" name="login_pwd2"  type="password" value="" placeholder="再次输入新登录密码">
					</div>
				</div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-4">
                            <input type="submit" name="submit" value="确定修改" class="btn btn-primary form-control" {$allowmodi}/>
                        <br/>
                    </div>
                </div>
            </form>

            <div class="line line-dashed b-b line-lg pull-in"></div>
            <h4>修改取现密码</h4>
            <form class="form-horizontal devform" action="{$self_url}" method="post">
                <input type="hidden" name="action" value="cash_pwd">
                <div class="form-group">
                    <label class="col-sm-2 control-label">修改取现密码</label>
                    <div class="col-sm-3">
                        <input class="form-control" type="password" value="" name="cash_pwd0" placeholder="原取现密码（初始为：123456）">
                    </div>
                    <div class="col-sm-3">
                        <input class="form-control" type="password" value="" name="cash_pwd1" placeholder="新取现密码">
                    </div>
                    <div class="col-sm-3">
                        <input class="form-control" type="password" value="" name="cash_pwd2" placeholder="再次输入新取现密码">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-4">
                            <input type="submit" name="submit" value="确定修改" class="btn btn-primary form-control" {$allowmodi}/>
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
    userinfo = account.getElementsByTagName("li")[2];
    userinfo.className = "active";
</script>
