<?php
define("SYS_KEY","w3iT89WLK6555PewY5b88fGkLWC8no2");
$website_name = "DOMAIN支付";

//$url1 = "http://www.domain.com/"; //HTTP网址，一定要以/结尾。
//$url2 = "https://www.domain.com/"; //HTTPS网址，一定要以/结尾。
//换成自动获取域名主机
function GetCurUrl(){
	if(!empty($_SERVER["REQUEST_URI"])){
		$scrtName = $_SERVER["REQUEST_URI"];
		$nowurl = $scrtName;
	}else{
		$scrtName = $_SERVER["PHP_SELF"];
		if(empty($_SERVER["QUERY_STRING"])){
			$nowurl = $scrtName;
		}else{
			$nowurl = $scrtName."?".$_SERVER["QUERY_STRING"];
		}
	}
	return $nowurl;
}
$pathinfo=explode("/",GetCurUrl());
$addurl="";
if(sizeof($pathinfo)>3){
		//网站处于二级目录之下，格式必形如v打头。
	if( substr($pathinfo[1],0,1)=="v"){
		$addurl=$pathinfo[1]."/";
	}
}
$url1 = "http://".$_SERVER['HTTP_HOST']."/".$addurl; //HTTP网址，一定要以/结尾。
$url2 = "https://".$_SERVER['HTTP_HOST']."/".$addurl; //HTTPS网址，一定要以/结尾。
$website_urls = $url2; //默认选择HTTPS网址，网关使用该设置。

$googleauth_user = false;//用户端是否强制开启Google验证器
$sms_switch = false;//短信开关
$page_ssl = false;//是否启用https，true是启用，false是不启用（此设置废弃）
$api_ssl = false;//此设置作废
$migrate_db = false; //数据库迁移模式，true为数据库迁移中，默认为false。此模式不可随意改动。
$balance_alert = true; //余额提醒
$scan_code = true;//是否开启扫码模式
$whitemd = false;//是否开启发起支付白名单
$is_white = false; //是否开启admin后台白名单
$email_switch= false;//邮箱开关
$sms_on_off = false;//短信通知开关
$agent_sms_switch = false;//代理短信通知开关
$send_sms_munber = "";//短信开关
$scan_code_login = false;//是否开启扫码登录
$sign_name = "会员管理系统";//短信签名

//以下信息需要和APP中预置的相同。
$VERSION2 = "domain";//APP渠道
$VERSIONV1 = "3";//APP版本1
$APP_FILE_NAME = "PayHelper1.0.3{$VERSION2}";
$VERSIONVKEY = "brTqu2wMD9gncX3vsWBf"; //APP预置密钥，正常使用中，请不要修改。
$APP_RETURN_NAME = "notify30_ddbKdEnNc75Q9FQT25wK.php";//APP回调的文件名称
//$VERSION_RETURN_URL = $website_urls."api/".$APP_RETURN_NAME;//APP回调地址
$VERSION_RETURN_URL = $url1."api/".$APP_RETURN_NAME;//APP回调地址，限定用HTTP
$UPDATE_URL = "https://dev.yykayou.com/test/update/index.php";//APP更新地址
$SOCKET_URL = "127.0.0.1:9938";//websocket链接地址

//以下信息请不要修改
ini_set("session.name",md5(md5($VERSION2).hash("sha256",__FILE__).$website_urls));
ini_set("session.use_strict_mode",1);

//判断网址是否以/结尾，如果否，则添加/
if(strrchr($website_urls,'/') != '/'){
    $website_urls = $website_urls."/";
}

if(is_file(__DIR__.DIRECTORY_SEPARATOR.'360safe/360webscan.php')){//360网站卫士
    require_once __DIR__.DIRECTORY_SEPARATOR."360safe/360webscan.php";
}
