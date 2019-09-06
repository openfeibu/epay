<?php
/**
 * 管理员基本信息
 **/
include("../includes/common.php");
if(!$_SESSION['is_admin']){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
$title = '基本设置';
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
    case 'base':
        $web_name = daddslashes(strip_tags($_REQUEST['web_name']));
        $web_qq = daddslashes(strip_tags($_REQUEST['web_qq']));
        $sql = "UPDATE `pay_admin` SET `web_name` = '{$web_name}', `web_qq` = '{$web_qq}' WHERE `id` = '{$admin_userid}'; ";
        break;
    case 'bank':
        $bankxinming = daddslashes(strip_tags($_REQUEST['bankxinming']));
        $bankname = daddslashes(strip_tags($_REQUEST['bankname']));
        $bankopenid = daddslashes(strip_tags($_REQUEST['bankopenid']));
        $bankcardid = daddslashes(strip_tags($_REQUEST['bankcardid']));

        $bankxinming2 = daddslashes(strip_tags($_REQUEST['bankxinming2']));
        $bankname2 = daddslashes(strip_tags($_REQUEST['bankname2']));
        $bankcardid2 = daddslashes(strip_tags($_REQUEST['bankcardid2']));
        $sql = "update `pay_admin` set `bankcardid` ='{$bankcardid}',`bankname` ='{$bankname}',`bankopenid` ='{$bankopenid}',`bankxinming` ='{$bankxinming}',`bankcardid2` ='{$bankcardid2}',`bankname2` ='{$bankname2}',`bankxinming2` ='{$bankxinming2}' where `id`='$pid'";
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

<script>
        function scan_code(ev) {                                            //是否开启扫码模式
            $.ajax({
                type: "GET",
                url: "../api/ajax_scan_code.php",
                data: {
                    token: "set_scan_code",
                    scan_code:ev.checked
                },
                success: function (data) {
                }
            });
        }

        function index_load_2() {
            $.ajax({                                                                    //获取是否开启了扫码
                type: "GET",
                url: "../api/ajax_scan_code.php",
                data: {
                    token: "get_scan_code",
                },
                success: function (data) {
                    $(".scan_code").attr("checked",data);
                    if(data == "true"){
                        $("#scan_code").html("<input type=\"checkbox\" checked onclick=\"scan_code(this)\"><i></i>");
                    }
                    else{
                        $("#scan_code").html("<input type=\"checkbox\" onclick=\"scan_code(this)\"><i></i>");
                    }
                }
            });
        }
    </script>

<div class="wrapper-md control">
    {$msg}
	<div class="panel panel-default">
		<div class="panel-heading font-bold">
			{$title}
		</div>
		<div class="panel-body">
            <!--修改基本信息-->
			<h4>网站基本信息：</h4>
            <form class="form-horizontal devform" action="{$self_url}" method="post">
                <input type="hidden" name="action" value="base">
				<div class="form-group">
					<label class="col-sm-2 control-label">管理员账号</label>
					<div class="col-sm-9">
						<input class="form-control" type="text" value="{$conf['admin_user']}" disabled>
					</div>
				</div>
				<div class="form-group">
                    <label class="col-sm-2 control-label">网站名称</label>
                    <div class="col-sm-3">
                        <input class="form-control" type="text" value="{$user['web_name']}" name="web_name" placeholder="网站名称">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">客服QQ</label>
                    <div class="col-sm-3">
                        <input class="form-control" type="text" value="{$user['web_qq']}" name="web_qq" placeholder="客服QQ">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-4">
                            <input type="submit" name="submit" value="确定修改" class="btn btn-primary form-control"/>
                        <br/>
                    </div>
                </div>
            </form>

				<!--<div class="line line-dashed b-b line-lg pull-in"></div>
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
            </form>-->

				<div class="line line-dashed b-b line-lg pull-in"></div>
				<h4>其他设置：</h4>
            <form class="form-horizontal devform" action="{$self_url}" method="post" style="display: none">
                <input type="hidden" name="action" value="others">
				<div class="form-group">
					<label class="col-sm-2 control-label">开户银行1</label>
					<div class="col-sm-9">
						<input class="form-control" type="text" name="bankname" value="{$userrow['bankname']}">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">开户行联行号 <a href="http://www.lianhanghao.com/" target="_blank"><font color=red>查询</font></a></label>
					<div class="col-sm-9">
						<input class="form-control" type="text" name="bankopenid" value="{$userrow['bankopenid']}">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">您的姓名1</label>
					<div class="col-sm-9">
						<input class="form-control" type="text" name="bankxinming" value="{$userrow['bankxinming']}">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">银行卡号1</label>
					<div class="col-sm-9">
						<input class="form-control" type="text" name="bankcardid" value="{$userrow['bankcardid']}">
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
	
	<div class="panel panel-default">
                    <div class="panel-heading font-bold">
                        开启操作
                    </div>
                    <div class="panel-body">
                        <form class="form-horizontal devform">
                            <div class="form-group">
                                <label class="col-sm-2 control-label">是否开启扫码模式</label>
                                <div class="col-sm-9">
                                    <label class="i-switch m-t-xs m-r" id="scan_code">

                                    </label>
                                </div>
                            </div>
                            <div class="line line-dashed b-b line-lg pull-in"></div>
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
    userinfo = account.getElementsByTagName("li")[1];
    userinfo.className = "active";
</script>
<script>
    index_load_2();
</script>