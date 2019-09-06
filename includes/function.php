<?php
function proxy_get($url){
    return 0;
    /*
    $url='http://123.206.57.110:3030/'.urlencode($url);
    $ch=curl_init($url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Linux; U; Android 4.4.1; zh-cn; R815T Build/JOP40D) AppleWebKit/533.1 (KHTML, like Gecko)Version/4.0 MQQBrowser/4.5 Mobile Safari/533.1');
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $content=curl_exec($ch);
    curl_close($ch);
    return($content);
    */
}

function curl_get($url){
    $ch = curl_init($url);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Linux; U; Android 4.4.1; zh-cn; R815T Build/JOP40D) AppleWebKit/533.1 (KHTML, like Gecko)Version/4.0 MQQBrowser/4.5 Mobile Safari/533.1');
    curl_setopt($ch,CURLOPT_TIMEOUT,30);
    $content = curl_exec($ch);
    curl_close($ch);
    return ($content);
}

function real_ip(){
    $ip = $_SERVER['REMOTE_ADDR'];
    if(isset($_SERVER['HTTP_CF_CONNECTING_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/',$_SERVER['HTTP_CF_CONNECTING_IP'])){
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    }elseif(isset($_SERVER['HTTP_X_REAL_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/',$_SERVER['HTTP_X_REAL_IP'])){
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    }elseif(isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/',$_SERVER['HTTP_CLIENT_IP'])){
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s',$_SERVER['HTTP_X_FORWARDED_FOR'],$matches)){
        foreach($matches[0] AS $xip){
            if(!preg_match('#^(10|172\.16|192\.168)\.#',$xip)){
                $ip = $xip;
                break;
            }
        }
    }
    return $ip;
}

function get_ip_city($ip){
    $url = 'http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip='; //这个地址不能用了
    $url = "http://ip.taobao.com/service/getIpInfo.php?ip={$ip}";
    // $city = curl_get($url);//此函数有问题
    $city = file_get_contents($url);
    $city = json_decode($city,true);

    if($city['data']['city']){
        // $location = $city['province'].$city['city'];
        $location = $city['data']['country'].$city['data']['city'].$city['data']['isp'];
    }else{
        $location = $city['data']['country'];
    }
    if($location){
        return $location;
    }else{
        return false;
    }
}

function send_mail($to,$sub,$msg){
    global $conf;
    include_once ROOT.'includes/smtp.class.php';
    $From = $conf['mail_name'];
    $Host = $conf['mail_stmp'];
    $Port = $conf['mail_port'];
    $SMTPAuth = 1;
    $Username = $conf['mail_name'];
    $Password = $conf['mail_pwd'];
    $Nickname = $conf['sitename'];
    $SSL = false;
    $mail = new SMTP($Host,$Port,$SMTPAuth,$Username,$Password,$SSL);
    $mail->att = array();
    if($mail->send($to,$From,$sub,$msg,$Nickname)){
        return true;
    }else{
        return $mail->log;
    }
}

function daddslashes($string,$force = 0,$strip = FALSE){
    !defined('MAGIC_QUOTES_GPC') && define('MAGIC_QUOTES_GPC',get_magic_quotes_gpc());
    if(!MAGIC_QUOTES_GPC || $force){
        if(is_array($string)){
            foreach($string as $key => $val){
                $string[$key] = daddslashes($val,$force,$strip);
            }
        }else{
            $string = addslashes($strip ? stripslashes($string) : $string);
        }
    }
    return $string;
}

function strexists($string,$find){
    return !(strpos($string,$find) === FALSE);
}

function dstrpos($string,$arr){
    if(empty($string)) return false;
    foreach((array)$arr as $v){
        if(strpos($string,$v) !== false){
            return true;
        }
    }
    return false;
}

function checkmobile(){
    $useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
    $ualist = array('android','midp','nokia','mobile','iphone','ipod','blackberry','windows phone');
    if((dstrpos($useragent,$ualist) || strexists($_SERVER['HTTP_ACCEPT'],"VND.WAP") || strexists($_SERVER['HTTP_VIA'],"wap"))) return true;else
        return false;
}

function authcode($string,$operation = 'DECODE',$key = '',$expiry = 0){
    $ckey_length = 4;
    $key = md5($key ? $key : ENCRYPT_KEY);
    $keya = md5(substr($key,0,16));
    $keyb = md5(substr($key,16,16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string,0,$ckey_length) : substr(md5(microtime()),-$ckey_length)) : '';
    $cryptkey = $keya.md5($keya.$keyc);
    $key_length = strlen($cryptkey);
    $string = $operation == 'DECODE' ? base64_decode(substr($string,$ckey_length)) : sprintf('%010d',$expiry ? $expiry + time() : 0).substr(md5($string.$keyb),0,16).$string;
    $string_length = strlen($string);
    $result = '';
    $box = range(0,255);
    $rndkey = array();
    for($i = 0; $i <= 255; $i++){
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }
    for($j = $i = 0; $i < 256; $i++){
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    for($a = $j = $i = 0; $i < $string_length; $i++){
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    if($operation == 'DECODE'){
        if((substr($result,0,10) == 0 || substr($result,0,10) - time() > 0) && substr($result,10,16) == substr(md5(substr($result,26).$keyb),0,16)){
            return substr($result,26);
        }else{
            return '';
        }
    }else{
        return $keyc.str_replace('=','',base64_encode($result));
    }
}

function random($length,$numeric = 0){
    $seed = base_convert(md5(microtime().$_SERVER['DOCUMENT_ROOT']),16,$numeric ? 10 : 35);
    $seed = $numeric ? (str_replace('0','',$seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
    $hash = '';
    $max = strlen($seed) - 1;
    for($i = 0; $i < $length; $i++){
        $hash .= $seed{mt_rand(0,$max)};
    }
    return $hash;
}

function showmsg($content = '未知的异常',$type = 4,$back = false){
    switch($type){
        case 1:
            $panel = "success";
            break;
        case 2:
            $panel = "info";
            break;
        case 3:
            $panel = "warning";
            break;
        case 4:
            $panel = "danger";
            break;
    }

    echo "<div class='panel panel-{$panel}'>
      <div class='panel-heading'>
        <h3 class='panel-title'>提示信息</h3>
        </div>
        <div class='panel-body'>";
    echo $content;

    if($back){
        echo '<hr/><a href="'.$back.'"><< 返回上一页</a>';
    }else{
        echo '<hr/><a href="javascript:history.back(-1)"><< 返回上一页</a>';
    }

    echo '</div>
    </div>';
}

function sysmsg($msg = '未知的异常',$die = true){
    ?>
    <!DOCTYPE html>
    <html xmlns="http://www.w3.org/1999/xhtml" lang="zh-CN">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>站点提示信息</title>
        <style type="text/css">
            html {
                background: #eee
            }

            body {
                background: #fff;
                color: #333;
                font-family: "微软雅黑", "Microsoft YaHei", sans-serif;
                margin: 2em auto;
                padding: 1em 2em;
                max-width: 700px;
                -webkit-box-shadow: 10px 10px 10px rgba(0, 0, 0, .13);
                box-shadow: 10px 10px 10px rgba(0, 0, 0, .13);
                opacity: .8
            }

            h1 {
                border-bottom: 1px solid #dadada;
                clear: both;
                color: #666;
                font: 24px "微软雅黑", "Microsoft YaHei",, sans-serif;
                margin: 30px 0 0 0;
                padding: 0;
                padding-bottom: 7px
            }

            #error-page {
                margin-top: 50px
            }

            h3 {
                text-align: center
            }

            #error-page p {
                font-size: 9px;
                line-height: 1.5;
                margin: 25px 0 20px
            }

            #error-page code {
                font-family: Consolas, Monaco, monospace
            }

            ul li {
                margin-bottom: 10px;
                font-size: 9px
            }

            a {
                color: #21759B;
                text-decoration: none;
                margin-top: -10px
            }

            a:hover {
                color: #D54E21
            }

            .button {
                background: #f7f7f7;
                border: 1px solid #ccc;
                color: #555;
                display: inline-block;
                text-decoration: none;
                font-size: 9px;
                line-height: 26px;
                height: 28px;
                margin: 0;
                padding: 0 10px 1px;
                cursor: pointer;
                -webkit-border-radius: 3px;
                -webkit-appearance: none;
                border-radius: 3px;
                white-space: nowrap;
                -webkit-box-sizing: border-box;
                -moz-box-sizing: border-box;
                box-sizing: border-box;
                -webkit-box-shadow: inset 0 1px 0 #fff, 0 1px 0 rgba(0, 0, 0, .08);
                box-shadow: inset 0 1px 0 #fff, 0 1px 0 rgba(0, 0, 0, .08);
                vertical-align: top
            }

            .button.button-large {
                height: 29px;
                line-height: 28px;
                padding: 0 12px
            }

            .button:focus, .button:hover {
                background: #fafafa;
                border-color: #999;
                color: #222
            }

            .button:focus {
                -webkit-box-shadow: 1px 1px 1px rgba(0, 0, 0, .2);
                box-shadow: 1px 1px 1px rgba(0, 0, 0, .2)
            }

            .button:active {
                background: #eee;
                border-color: #999;
                color: #333;
                -webkit-box-shadow: inset 0 2px 5px -3px rgba(0, 0, 0, .5);
                box-shadow: inset 0 2px 5px -3px rgba(0, 0, 0, .5)
            }

            table {
                table-layout: auto;
                border: 1px solid #333;
                empty-cells: show;
                border-collapse: collapse
            }

            th {
                padding: 4px;
                border: 1px solid #333;
                overflow: hidden;
                color: #333;
                background: #eee
            }

            td {
                padding: 4px;
                border: 1px solid #333;
                overflow: hidden;
                color: #333
            }
        </style>
    </head>
    <body id="error-page">
    <?php
    echo '<h3>站点提示信息</h3>';
    echo $msg;
    ?>
    </body>
    </html>
    <?php
    if($die == true){
        exit;
    }
}

function creat_callback($data){
    global $DB;
    $userrow = $DB->query("SELECT * FROM pay_user WHERE `id` = '{$data['pid']}' limit 1")->fetch();
    $array = array(
        'pid'          => $data['pid'],
        'trade_no'     => $data['trade_no'],
        'out_trade_no' => $data['out_trade_no'],
        'type'         => $data['type'],
        'name'         => $data['name'],
        'money'        => $data['money'],
        'trade_status' => 'TRADE_SUCCESS',
    );
    $arg = argSort(paraFilter($array));
    $prestr = createLinkstring($arg);
    $urlstr = createLinkstringUrlencode($arg);
    $sign = md5Sign($prestr,$userrow['key']);
    if(strpos($data['notify_url'],'?')){
        $url['notify'] = $data['notify_url'].'&'.$urlstr.'&sign='.$sign.'&sign_type=MD5';
    }else{
        $url['notify'] = $data['notify_url'].'?'.$urlstr.'&sign='.$sign.'&sign_type=MD5';
    }
    if(strpos($data['return_url'],'?')){
        $url['return'] = $data['return_url'].'&'.$urlstr.'&sign='.$sign.'&sign_type=MD5';
    }else{
        $url['return'] = $data['return_url'].'?'.$urlstr.'&sign='.$sign.'&sign_type=MD5';
    }
    return $url;
}

function getdomain($url){
    $arr = parse_url($url);
    return $arr['host'];
}

/**
 *
 * 根据php的$_SERVER['HTTP_USER_AGENT'] 中各种浏览器访问时所包含各个浏览器特定的字符串来判断是属于PC还是移动端
 * @author           discuz3x
 * @lastmodify    2014-04-09
 * @return  BOOL
 */
function checkmobile2(){
    global $_G;
    $mobile = array();
//各个触控浏览器中$_SERVER['HTTP_USER_AGENT']所包含的字符串数组
    static $touchbrowser_list = array(
        'iphone',
        'android',
        'phone',
        'mobile',
        'wap',
        'netfront',
        'java',
        'opera mobi',
        'opera mini',
        'ucweb',
        'windows ce',
        'symbian',
        'series',
        'webos',
        'sony',
        'blackberry',
        'dopod',
        'nokia',
        'samsung',
        'palmsource',
        'xda',
        'pieplus',
        'meizu',
        'midp',
        'cldc',
        'motorola',
        'foma',
        'docomo',
        'up.browser',
        'up.link',
        'blazer',
        'helio',
        'hosin',
        'huawei',
        'novarra',
        'coolpad',
        'webos',
        'techfaith',
        'palmsource',
        'alcatel',
        'amoi',
        'ktouch',
        'nexian',
        'ericsson',
        'philips',
        'sagem',
        'wellcom',
        'bunjalloo',
        'maui',
        'smartphone',
        'iemobile',
        'spice',
        'bird',
        'zte-',
        'longcos',
        'pantech',
        'gionee',
        'portalmmm',
        'jig browser',
        'hiptop',
        'benq',
        'haier',
        '^lct',
        '320x320',
        '240x320',
        '176x220',
    );
//window手机浏览器数组【猜的】
    static $mobilebrowser_list = array('windows phone');
//wap浏览器中$_SERVER['HTTP_USER_AGENT']所包含的字符串数组
    static $wmlbrowser_list = array(
        'cect',
        'compal',
        'ctl',
        'lg',
        'nec',
        'tcl',
        'alcatel',
        'ericsson',
        'bird',
        'daxian',
        'dbtel',
        'eastcom',
        'pantech',
        'dopod',
        'philips',
        'haier',
        'konka',
        'kejian',
        'lenovo',
        'benq',
        'mot',
        'soutec',
        'nokia',
        'sagem',
        'sgh',
        'sed',
        'capitel',
        'panasonic',
        'sonyericsson',
        'sharp',
        'amoi',
        'panda',
        'zte',
    );
    $pad_list = array('pad','gt-p1000');
    if(isset($_SERVER['HTTP_USER_AGENT'])){
        $useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
    }else{
        $useragent = "";
    }

    if(dstrpos2($useragent,$pad_list)){
        return false;
    }
    if(($v = dstrpos2($useragent,$mobilebrowser_list,true))){
        $_G['mobile'] = $v;
        return '1';
    }
    if(($v = dstrpos2($useragent,$touchbrowser_list,true))){
        $_G['mobile'] = $v;
        return '2';
    }
    if(($v = dstrpos2($useragent,$wmlbrowser_list))){
        $_G['mobile'] = $v;
        return '3'; //wml版
    }
    $brower = array('mozilla','chrome','safari','opera','m3gate','winwap','openwave','myop');
    if(dstrpos2($useragent,$brower)) return false;
    $_G['mobile'] = 'unknown';
//对于未知类型的浏览器，通过$_GET['mobile']参数来决定是否是手机浏览器
    if(isset($_G['mobiletpl']) || isset($_GET['mobile'])){
        return true;
    }else{
        return false;
    }
}

/**
 * 判断$arr中元素字符串是否有出现在$string中
 * @param  $string $_SERVER['HTTP_USER_AGENT']
 * @param  $arr          各中浏览器$_SERVER['HTTP_USER_AGENT']中必定会包含的字符串
 * @param  $returnvalue 返回浏览器名称还是返回布尔值，true为返回浏览器名称，false为返回布尔值【默认】
 * @author           discuz3x
 * @lastmodify    2014-04-09
 */
function dstrpos2($string,$arr,$returnvalue = false){
    if(empty($string)) return false;
    foreach((array)$arr as $v){
        if(strpos($string,$v) !== false){
            $return = $returnvalue ? $v : true;
            return $return;
        }
    }
    return false;
}

/**
 * 根据地址获取页面返回
 * @param  $url
 * @author  sunson
 * @lastmodify    2019-01-13
 * @return  string
 */

function getContentByCurl($url){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	$result =  curl_exec($ch);
	curl_close ($ch);
	return $result;
}

/**
 * 根据银行卡判断银行英文缩写
 * @param  $cardNo
 * @author  sunson
 * @lastmodify    2019-01-13
 * @return  array
 */
function getCardInfo($cardNo){
	if(empty($cardNo)) return null; //为空直接返回null
    	$url = "https://ccdcapi.alipay.com/validateAndCacheCardInfo.json?cardNo=".$cardNo."&cardBinCheck=true";
		if(extension_loaded('openssl')){ //判断有没有openssl扩展
			return json_decode(file_get_contents($url),TRUE);
		}else{ //没有就使用curl扩展
			return json_decode(getContentByCurl($url),TRUE);
	    }
}

/**
 * 根据当前序号生成下一个订单
 * @param  $var
 * @author  sunson
 * @lastmodify   2019-02-04
 * @return  string
 */
function gen_trade_no($var="") {
	$chars = '0123456789abcdefghijklmnopqrstuvwxyz';
	$length=strlen($var);
	if($length!=6 || empty($var)){
		return "000000";//为空或者超过6位从新开始
	}
	$charsleng=strlen($chars);
	$char6=substr($var,-1,1);
	$char5=substr($var,-2,1);
	$char4=substr($var,-3,1);
	$char3=substr($var,-4,1);
	$char2=substr($var,-5,1);
	$char1=substr($var,-6,1);
	$pos6=strrpos($chars,$char6);
	$pos5=strrpos($chars,$char5);
	$pos4=strrpos($chars,$char4);
	$pos3=strrpos($chars,$char3);
	$pos2=strrpos($chars,$char2);
	$pos1=strrpos($chars,$char1);
	if($pos6==$charsleng-1){//从最后一位来开始判断,要不要进位
		//进位，也要根据第5位是否处于最后一个
		if($pos5==$charsleng-1){
			if($pos4==$charsleng-1){
				if($pos3==$charsleng-1){
					if($pos2==$charsleng-1){
						if($pos1==$charsleng-1){
							return "000000";//超过6位从新开始
						}
						return substr($chars,$pos1+1,1)."00000";
					}
					return $char1.substr($chars,$pos2+1,1)."0000";
				}
				return $char1.$char2.substr($chars,$pos3+1,1)."000";
			}
			return $char1.$char2.$char3.substr($chars,$pos4+1,1)."00";
    	}
		return $char1.$char2.$char3.$char4.substr($chars,$pos5+1,1)."0";
	}
	return $char1.$char2.$char3.$char4.$char5.substr($chars,$pos6+1,1);//否则直接加1
}

/**
 * 字符串隐藏部分内容
 * @param  $string  原字符创
 * @author  $bengin 开始位置
 * @lastmodify $len  隐藏长度
 * @return  string
 */

function hideStr($string, $bengin=0, $len = 4, $type = 0, $glue = "@") {
    if (empty($string))
        return false;
    $array = array();
    if ($type == 0 || $type == 1 || $type == 4) {
        $strlen = $length = mb_strlen($string);
        while ($strlen) {
            $array[] = mb_substr($string, 0, 1, "utf8");
            $string = mb_substr($string, 1, $strlen, "utf8");
            $strlen = mb_strlen($string);
        }
    }
    if ($type == 0) {
        for ($i = $bengin; $i < ($bengin + $len); $i++) {
            if (isset($array[$i]))
                $array[$i] = "*";
        }
        $string = implode("", $array);
    }else if ($type == 1) {
        $array = array_reverse($array);
        for ($i = $bengin; $i < ($bengin + $len); $i++) {
            if (isset($array[$i]))
                $array[$i] = "*";
        }
        $string = implode("", array_reverse($array));
    }else if ($type == 2) {
        $array = explode($glue, $string);
        $array[0] = hideStr($array[0], $bengin, $len, 1);
        $string = implode($glue, $array);
    } else if ($type == 3) {
        $array = explode($glue, $string);
        $array[1] = hideStr($array[1], $bengin, $len, 0);
        $string = implode($glue, $array);
    } else if ($type == 4) {
        $left = $bengin;
        $right = $len;
        $tem = array();
        for ($i = 0; $i < ($length - $right); $i++) {
            if (isset($array[$i]))
                $tem[] = $i >= $left ? "*" : $array[$i];
        }
        $array = array_chunk(array_reverse($array), $right);
        $array = array_reverse($array[0]);
        for ($i = 0; $i < $right; $i++) {
            $tem[] = $array[$i];
        }
        $string = implode("", $tem);
    }
    return $string;
}
//判断白名单用户
function is_safe_ip($ip="",$ips="")
{
	if (!$ip) $ip = real_ip();  //获取客户端IP
	if ($ips) {
		if (is_string($ips)) { //ip用"," 例如白名单IP：192.168.1.13,123.23.23.44,193.134.*.*
			$ips = explode(",", $ips);
		}
	}
	if (in_array($ip, $ips)) {
		return true;
	}
	$ipregexp = implode('|', str_replace(array('*', '.'), array('\d+', '\.'), $ips));
	$rs       = preg_match("/^(" . $ipregexp . ")$/", $ip);
	if ($rs) return true;
	return;
}
?>