<?php
/**
 * 基本信息
 **/
include("../includes/common.php");
if(!isset($_SESSION['userid'])){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
$title = '账号设置';
include './head.php';
$self_url = $_SERVER['PHP_SELF'];
//require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/debug.php";
//谷歌验证器时区
date_default_timezone_set("Asia/Hong_Kong");
//var_dump(date_default_timezone_get());
$type = $userrow['type'];

//查找pay_user_others表
$sql = "SELECT * FROM `pay_user_others` WHERE `id` = '{$pid}' LIMIT 1";
$result = $DB->query($sql);
$user_others = $result->fetch();

//查找pay_user表
$sql = "SELECT * FROM `pay_user` WHERE `id` = '{$pid}' LIMIT 1";
$result = $DB->query($sql);
$user = $result->fetch();

//非结算商户，允许用户自定义隧道地址
if($type == 2){
    $userrow["callback_url"] = "";
    $userrow['mobile_url'] = "";
    $userrow['mobile_alipay_url'] = "";
    $userrow['mobile_wxpay_url'] = "";
    $userrow['mobile_qqpay_url'] = "";
    if($user_others){
        $userrow["callback_url"] = $user_others['callback_url'];
        $userrow['mobile_url'] = $user_others['mobile_url'];
        $userrow['mobile_alipay_url'] = $user_others['mobile_alipay_url'];
        $userrow['mobile_wxpay_url'] = $user_others['mobile_wxpay_url'];
        $userrow['mobile_qqpay_url'] = $user_others['mobile_qqpay_url'];
    }
    $type_echo = <<< EOF
                <div class="form-group">
                    <label class="col-sm-2 control-label">回调地址</label>
                    <div class="col-sm-9">
                        <input class="form-control" type="text" value="{$userrow['callback_url']}" name="callback_url">
                    </div>
                </div>
                <div class="form-group" style="display: none;">
                    <label class="col-sm-2 control-label">隧道地址(一行一个)（过渡栏，主要以各通道隧道地址为准）</label>
                    <div class="col-sm-9">
                        <textarea class="form-control" type="text" name="mobile_url" rows="10">{$userrow['mobile_url']}</textarea>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">支付宝隧道地址(一行一个)</label>
                    <div class="col-sm-9">
                        <textarea class="form-control" type="text" name="mobile_alipay_url" rows="10">{$userrow['mobile_alipay_url']}</textarea>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">微信隧道地址(一行一个)</label>
                    <div class="col-sm-9">
                        <textarea class="form-control" type="text" name="mobile_wxpay_url" rows="10">{$userrow['mobile_wxpay_url']}</textarea>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">QQ隧道地址(一行一个)</label>
                    <div class="col-sm-9">
                        <textarea class="form-control" type="text" name="mobile_qqpay_url" rows="10">{$userrow['mobile_qqpay_url']}</textarea>
                    </div>
                </div>
EOF;
}else{
    $type_echo = "";
}

isset($_REQUEST['action']) ? $action = $_REQUEST['action'] : $action = "";
switch($action){
    case 'user':
        $key = daddslashes($_REQUEST['key']);

        $sql = "UPDATE `pay_user` SET `key` = '{$key}' WHERE `id`='{$pid}' ;";
        if($type == 2){
            $callback_url = daddslashes($_REQUEST['callback_url']);
            $mobile_url = daddslashes($_REQUEST['mobile_url']);
            $mobile_alipay_url = daddslashes($_REQUEST['mobile_alipay_url']);
            $mobile_wxpay_url = daddslashes($_REQUEST['mobile_wxpay_url']);
            $mobile_qqpay_url = daddslashes($_REQUEST['mobile_qqpay_url']);

            //$sql .= "INSERT INTO `pay_user_others` (`id`, `return_url`, `callback_url`, `mobile_url`, `note1`, `note2`) VALUES ('$pid', '', '{$callback_url}', '{$mobile_url}', '', '');";
            $sql .= "INSERT INTO `pay_user_others` (`id`, `return_url`, `callback_url`, `mobile_url`, `mobile_alipay_url`, `mobile_wxpay_url`, `mobile_qqpay_url`, `note1`, `note2`) VALUES ('$pid', '', '{$callback_url}', '{$mobile_url}', '{$mobile_alipay_url}', '$mobile_wxpay_url', '$mobile_qqpay_url', '', '') ON DUPLICATE KEY UPDATE `callback_url` = '{$callback_url}', `mobile_url` = '{$mobile_url}', `mobile_alipay_url` = '{$mobile_alipay_url}', `mobile_wxpay_url` = '{$mobile_wxpay_url}', `mobile_qqpay_url` = '{$mobile_qqpay_url}' ;";
        }
        break;
        
    case 'alipay':
        $account = daddslashes(strip_tags($_REQUEST['account']));
        $username = daddslashes(strip_tags($_REQUEST['username']));
        $sql = "update `pay_user` set `account` ='{$account}',`username` ='{$username}' where `id`='$pid'";
        break;
    case 'bank':
        $bankxinming = daddslashes(strip_tags($_REQUEST['bankxinming']));
        $bankname = daddslashes(strip_tags($_REQUEST['bankname']));
        $bankopenid = daddslashes(strip_tags($_REQUEST['bankopenid']));
        $bankcardid = daddslashes(strip_tags($_REQUEST['bankcardid']));

        $bankxinming2 = daddslashes(strip_tags($_REQUEST['bankxinming2']));
        $bankname2 = daddslashes(strip_tags($_REQUEST['bankname2']));
        $bankcardid2 = daddslashes(strip_tags($_REQUEST['bankcardid2']));
        $sql = "update `pay_user` set `bankcardid` ='{$bankcardid}',`bankname` ='{$bankname}',`bankopenid` ='{$bankopenid}',`bankxinming` ='{$bankxinming}',`bankcardid2` ='{$bankcardid2}',`bankname2` ='{$bankname2}',`bankxinming2` ='{$bankxinming2}' where `id`='$pid'";
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
  <h1 class="m-n font-thin h3">修改资料</h1>
</div>

<div class="wrapper-md control">
    {$msg}
	<div class="panel panel-default">
		<div class="panel-heading font-bold">
			基本资料
		</div>
		<div class="panel-body">
<!--            修改基本信息-->
				<h4>商户信息查看：</h4>
            <form class="form-horizontal devform" action="{$self_url}" method="post">
                <input type="hidden" name="action" value="user">
				<div class="form-group">
					<label class="col-sm-2 control-label">商户ID</label>
					<div class="col-sm-9">
						<input class="form-control" type="text" value="{$pid}" disabled>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">商户密钥</label>
					<div class="col-sm-9">
						<input class="form-control" type="text" value="{$userrow['key']}" name="key" readonly>
					</div>
				</div>
{$type_echo}
                <div class="form-group" style="display: none;">
                    <div class="col-sm-offset-2 col-sm-4">
                            <input type="submit" name="submit" value="重置密钥" class="btn btn-primary form-control" {$allowmodi}/>
                        <br/>
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
            <h4>网络账号设置：</h4>
            <form class="form-horizontal devform" action="{$self_url}" method="post">
                <input type="hidden" name="action" value="alipay">
                <div class="form-group">
					<label class="col-sm-2 control-label">支付宝账号</label>
					<div class="col-sm-9">
						<input class="form-control" type="text" name="account" value="{$userrow['account']}">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">支付宝姓名</label>
					<div class="col-sm-9">
						<input class="form-control" type="text" name="username" value="{$userrow['username']}">
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
				<h4>银行账号设置1：</h4>
            <form class="form-horizontal devform" action="{$self_url}" method="post">
                <input type="hidden" name="action" value="bank">
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

				<h4>银行账号设置2：</h4>
				<div class="form-group">
					<label class="col-sm-2 control-label">开户银行2</label>
					<div class="col-sm-9">
						<input class="form-control" type="text" name="bankname2" value="{$userrow['bankname2']}">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">您的姓名2</label>
					<div class="col-sm-9">
						<input class="form-control" type="text" name="bankxinming2" value="{$userrow['bankxinming2']}">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">银行卡号2</label>
					<div class="col-sm-9">
						<input class="form-control" type="text" name="bankcardid2" value="{$userrow['bankcardid2']}">
					</div>
				</div>

				<div class="form-group">
				  <div class="col-sm-offset-2 col-sm-4">
				      <input type="submit" name="submit" value="确定修改" class="btn btn-primary form-control" {$allowmodi}/>
				      <br/>
				 </div>
                </div>
				<div class="line line-dashed b-b line-lg pull-in"></div>
				<div class="form-group">
					<label class="col-sm-2"></label>
					<div class="col-sm-6">
					<h4><span class="glyphicon glyphicon-info-sign"></span>注意事项</h4>
						1.支付宝账户和支付宝真实姓名请仔细核对，一旦错误将无法结算到账！<br/>2.结算账号请认真填写核对，一旦提交将无法修改，如需要修改请联系在线客服<br/>3.如有疑问请咨询在线客服					</div>
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
    userinfo = account.getElementsByTagName("li")[1];
    userinfo.className = "active";
</script>
