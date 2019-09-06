<?php
/**
 * 基本信息
 **/
include("../includes/common.php");
if(!isset($_SESSION['is_admin'])){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
$admin_userid = $_SESSION['admin_userid'];
$title = '修改密码';
include './head.php';
$self_url = $_SERVER['PHP_SELF'];
//require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/debug.php";
//谷歌验证器时区
date_default_timezone_set("Asia/Hong_Kong");
//var_dump(date_default_timezone_get());
$type = $userrow['type'];

//查找pay_admin表
$sql = "SELECT * FROM `pay_admin` WHERE `id` = '{$admin_userid}' LIMIT 1";
$result = $DB->query($sql);
$user = $result->fetch();

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : "";
switch($action){
    case 'user':
        $pwd0 = daddslashes($_REQUEST['pwd0']);
        $pwd1 = daddslashes($_REQUEST['pwd1']);
        $pwd2 = daddslashes($_REQUEST['pwd2']);
        if($pwd1 != $pwd2 || $pwd1 == '' || $pwd1 == null){
            exit("<script language='javascript'>alert('两次输入不一致或为空，修改密码失败！');history.go(-1);</script>");
        }
        if($pwd0 != $conf['admin_pwd']){
            exit("<script language='javascript'>alert('原密码错误，修改密码失败！');history.go(-1);</script>");
        }

        //弱密码检测
        if(6 > \epay\func::judgepassword($pwd1)){
            exit("<script language='javascript'>alert('新密码太简单，请使用正确的密码！');history.go(-1);</script>");
        }
        // $key = daddslashes($_REQUEST['key']);

        $sql = "UPDATE `pay_admin` SET `admin_pwd` ='{$pwd1}' WHERE `id`='0' AND `admin_pwd` = '{$pwd0}';";
        //$sql .= "INSERT INTO `pay_user_others` (`id`, `return_url`, `callback_url`, `mobile_url`, `note1`, `note2`) VALUES ('$pid', '', '{$callback_url}', '{$mobile_url}', '', '');";
        break;
    case 'twoauth':
        $twoauth0 = daddslashes($_REQUEST['twoauth0']);
        $twoauth1 = daddslashes($_REQUEST['twoauth1']);
        $twoauth2 = daddslashes($_REQUEST['twoauth2']);
        if($twoauth1 != $twoauth2 || $twoauth1 == '' || $twoauth1 == null){
            exit("<script language='javascript'>alert('两次输入不一致或为空，修改密码失败！');history.go(-1);</script>");
        }
        if($twoauth0 != $conf['twoauth']){
            exit("<script language='javascript'>alert('原密码错误，修改密码失败！');history.go(-1);</script>");
        }

        // $key = daddslashes($_REQUEST['key']);

        $sql = "UPDATE `pay_admin` SET `twoauth` ='{$twoauth1}' WHERE `id`='0' AND `twoauth` = '{$twoauth0}';";
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

$msg = isset($msg) ? "<div class=\"alert alert-info\">{$msg}</div>" : "";

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
                <input type="hidden" name="action" value="user">
				<div class="form-group">
					<label class="col-sm-2 control-label">管理员账号</label>
					<div class="col-sm-9">
						<input class="form-control" type="text" value="{$conf['admin_user']}" disabled>
					</div>
				</div>
				<div class="form-group">
                    <label class="col-sm-2 control-label">修改登录密码</label>
                    <div class="col-sm-3">
                        <input class="form-control" type="password" value="" name="pwd0" placeholder="原登录密码">
                    </div>
                    <div class="col-sm-3">
                        <input class="form-control" type="password" value="" name="pwd1" placeholder="新登录密码">
                    </div>
                    <div class="col-sm-3">
                        <input class="form-control" type="password" value="" name="pwd2" placeholder="再次输入新登录密码">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-4">
                            <input type="submit" name="submit" value="确定修改" class="btn btn-primary form-control"/>
                        <br/>
                    </div>
                </div>
            </form>

				<div class="line line-dashed b-b line-lg pull-in"></div>
				<h4>二次验证密码：</h4>
            <form class="form-horizontal devform" action="{$self_url}" method="post">
                <input type="hidden" name="action" value="twoauth">
				<div class="form-group">
                    <label class="col-sm-2 control-label">修改二次验证密码：</label>
                    <div class="col-sm-3">
                        <input class="form-control" type="password" value="" name="twoauth0" placeholder="原二次验证密码（初始为0）">
                    </div>
                    <div class="col-sm-3">
                        <input class="form-control" type="password" value="" name="twoauth1" placeholder="新二次验证密码">
                    </div>
                    <div class="col-sm-3">
                        <input class="form-control" type="password" value="" name="twoauth2" placeholder="再次输入新二次验证密码">
                    </div>
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


include_once __DIR__."/foot.php";
?>
<script>
    account = document.getElementById("account");
    account.className = "active";
    userinfo = account.getElementsByTagName("li")[2];
    userinfo.className = "active";
</script>
