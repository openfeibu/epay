<?php
//记录得到的
function slog($rr,$StrValue) {
	$logfile = fopen($rr,'a+');
	fwrite($logfile,"\r\n".$StrValue);
	fclose($logfile);
}
 
function arraforeach($arr) {
  static $str;
  static $keystr;
  if (!is_array($arr)) {
  return $arr;
  }
  foreach ($arr as $key => $val ) {
  $keystr=$keystr.$key;
  if (is_array($val)) {
  arraforeach($val);
  } else {
  $str[] = $val.$keystr;
  }
  }
  return implode($str);
}
 
function rlog($StrKey,$StrValue,$ArrReq,$method) {
  $StrValue=arraforeach($StrValue);
  return  "<tr><td>$method</td><td>$StrKey</td><td>$StrValue</td></tr>\r\n";
}
 
//不同环境下获取真实的IP
function get_ip(){
    //判断服务器是否允许$_SERVER
    if(isset($_SERVER)){    
        if(isset($_SERVER[HTTP_X_FORWARDED_FOR])){
            $realip = $_SERVER[HTTP_X_FORWARDED_FOR];
        }elseif(isset($_SERVER[HTTP_CLIENT_IP])) {
            $realip = $_SERVER[HTTP_CLIENT_IP];
        }else{
            $realip = $_SERVER[REMOTE_ADDR];
        }
    }else{
        //不允许就使用getenv获取  
        if(getenv("HTTP_X_FORWARDED_FOR")){
              $realip = getenv( "HTTP_X_FORWARDED_FOR");
        }elseif(getenv("HTTP_CLIENT_IP")) {
              $realip = getenv("HTTP_CLIENT_IP");
        }else{
              $realip = getenv("REMOTE_ADDR");
        }
    }

    return $realip;
}      

$linev = "<h1>".date('Y-m-d H:i:s')."</h1>\r\n";
$linev .= "<table>\r\n";
 
//逐个GET
foreach($_GET as $key=>$value) {
	$linev .= "\r\n".rlog($key,$value,$getfilter,"GET");
}
 
//逐个POST
foreach($_POST as $key=>$value) {
	$linev .= "\r\n".rlog($key,$value,$postfilter,"POST");
}
 
//逐个COOKIE
foreach($_COOKIE as $key=>$value) {
	$linev .= "\r\n".rlog($key,$value,$cookiefilter,"COOKIE");
}

//逐个SESSION
foreach($_SESSION as $key=>$value) {
	$linev .= "\r\n".rlog($key,$value,$cookiefilter,"SESSION");
}
 
//尝试记录RAW等POST过来的xml,json数据 
$datainput = file_get_contents('php://input');
if($datainput){
	$datainput=arraforeach($datainput);
	$linev .= "\r\n".rlog("Input",$datainput,$datainput,"Input");
}else{
	$datainput = $GLOBALS['HTTP_RAW_POST_DATA'];
	$datainput = arraforeach($datainput);
	if($datainput) $linev .= "\r\n".rlog("Input",$datainput,$datainput,"RAW_POST");
}
$ip=get_ip();
$linev .=  "\r\n".rlog("From",$_SERVER['HTTP_REFERER'],$_SERVER['HTTP_REFERER'],"From");
$linev .=  "\r\n".rlog("url",$_SERVER["REQUEST_URI"],$_SERVER["REQUEST_URI"],"url");
$linev .=  "\r\n".rlog("IP",$ip,$ip,"IP");
$linev .=  "\r\n".rlog("Agent",$_SERVER['HTTP_USER_AGENT'],$_SERVER['HTTP_USER_AGENT'],"Agent");
$linev .=  "\r\n</table>\r\n";
 
 
$lines='<style type="text/css">table{border:1px solid #0180CF; margin:0 auto;font-size:12px;width:92%;}table td{border:1px solid #a2c6d3;padding:5px;word-wrap:break-word;word-break:break-all;}</style>';
 
$rr = '../../logs/'.$_SERVER['HTTP_HOST'].'_logs_'.date('Y-m-d').'.html';//按天存放
 
if(!file_exists($rr)){
	slog($rr,$lines);
}
slog($rr,$linev);
?>