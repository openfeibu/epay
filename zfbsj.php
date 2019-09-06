<?php
$datayi=json_encode($_REQUEST,JSON_UNESCAPED_UNICODE );
$dataer=json_decode($datayi);
$shuju=$dataer->auth_code;

$now=date("Y-m-d H:i:s");

$params="app_id=2019022163313167&charset=GBK&code=" .$shuju. "&grant_type=authorization_code&method=alipay.system.oauth.token&sign_type=RSA2&timestamp=" .$now. "&version=1.0";

$priKey="MIIEowIBAAKCAQEAsNfzI5fy0D2SjKlKG1aGTdeUSL2vO+OddE6Eid9ZuaScHm1YRjxVyzSJety1Guf67q2Y9Is9AHP//ojq3HNs6TeVQ3y5on+k8Hgy/ff0C1f3l7aB3yuS1Xo0MOcuA/tjwAeLpIO0lsIx/Hizv5OyjPBsI6zOTqYerTuQWflRZylg/G2jMaOGn/lR0Mau07tPtuNEOzziRezOABKNYPyuE8wHIOYTobihWQY9V1bmbYfpELe5opg8fysFGeBbOUqP3yiMFY1OjbEqd03kB3SSikdgAcC2aGcn6dFeufsMy7c2PGociChdJJz4MdpR7JKB0UPhqjmxe6afVp5Xw5dGlQIDAQABAoIBADj1FMZ+BiKz8iOD0u6DK7aenI/hKDNt2CTBtUaCvW9haTcTmL7mZ+uO/EiqFVqZJOUC3KTGw9sFgYoFpz9hnc5vrI+T+GYyHnmk/Sd3WQ7eQkyG58VtT1mahnzJuLRnn3UV+Q//q4Zy/Y6Hgb1OYM+sD+hdnAUQ1AiPiqqv9lv3GqFVbnDvXv1D9XnG9w2W4rDBHNQVJTKPypk+s9GhhYwAElunlfSjt0Bm1hg5FCcWADSpYPncPETUZDT44XaawnLvQ6LGFLyMDpeiPGprbdlUHv8muiJYFZY61FxXJjeK7p+Zs7eAw47NNEAOv4c6Jt/vUgtsvYQ69U3tuxB07fECgYEA30yoxnqTvJVnA3D1Ad8pMxoW4nejwx0mhrNQXgzQBlYXBL1dD6HTObtkg5pKhdU3PS2UL43p4sBFgxi3RWsKyirHdwZSxekPjHTy1g6GIUjWhlwzQ8me6wIlboK19TSmIrNJcV1Nbdos2beS9lESyuvB34QfcGL5EbxRXjv3Qt8CgYEAyr2w+Y0f/kgMq3LUn/KCfMS5+ftENZ/Dc2KSxn1L9KnMHwOzGtihGcB8obx56e5+3STBxFp5ySpwxj61pQVCbX0N5pu/j4xp++Jsqfr4h5xURb29Q/A1JixPyCebOBv6Dh68Ztjpc4p3EV0u9gwG/XTus2FBARjE6uGgKVYkeQsCgYEAmC/tD2jXa73/eW7w7RelQrjTRAH6UK57ZW6spYzh8o+WHJra5B6MkVpQRQlRJSmN/lokFs0HzIzyU0jzD5LGxcamL07V9yDCgh3qq002yVSVMPmBzv+JGe5aiJj5XmdFm3zJ30OR0/7QdooQ1WOoBTjs52Nq8fPigN3yLuF7wc0CgYAOoeY1/KOEfLq9XQpPiPc1/hTWH/ZqUF+Q4lppjP2AaQjcbC3vEBeAfk4AdlPuOFJbr510iHsls7Rz+m6tvFVxBYeNT0xiFeZIUa06D20EjJngdrNERf/wA162uXUQdaR0hG9glOM0fZfeXvVWuMOAY4Ie3DQO2jTMJwCiOx9ixQKBgD2N2KU2Ka4sSkr6oscr4GGsH9W9A3lJSd1Cl1vqyIzX3FBGHLg9ewWQEfLrII7odVD0rOs0TJ4oDeVpiNFarPt5HxTl0Kn69KcA8v758SBuswIwU14XaF9MqRsZKUYQ+jAqLwvK3n655y7lCZthU1jGRryAl7tATgd7pJo3tbBi";
$res = "-----BEGIN RSA PRIVATE KEY-----\n" .
    wordwrap($priKey, 64, "\n", true) .
    "\n-----END RSA PRIVATE KEY-----";
openssl_sign($params, $sign, $res, OPENSSL_ALGO_SHA256);
$sign = base64_encode($sign);

$post_data=array();
$post_data["app_id"]="2019022163313167";
$post_data["method"]="alipay.system.oauth.token";
$post_data["charset"]="GBK";
$post_data["sign_type"]="RSA2";
$post_data["timestamp"]=$now;
$post_data["sign"]=$sign;
$post_data["version"]="1.0";
$post_data["grant_type"]="authorization_code";
$post_data["code"]=$shuju;

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, 'https://openapi.alipay.com/gateway.do');
curl_setopt($curl, CURLOPT_HEADER, 1);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//设置post方式提交
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
//执行命令
$data = curl_exec($curl);
//关闭URL请求
curl_close($curl);
$fenge=explode('user_id":"',$data);
//这里加入判断异常
if(sizeof($fenge)<3)	exit('嗯？小老弟想搞事情？');
//拿到支付宝userid
$alipayuserid=substr($fenge[2],0,16);
if(substr($alipayuserid,0,4)!="2088") exit('嗯？小老弟想搞事情？');
//GET提交参数
	$opts = array(
		'http'=>array(
			'method'=>"GET",
			'timeout'=>10,//单位秒
		)
	);
//加入一个文件指定。
$pageurl="getadv";
if(isset($_GET[type])){
	switch ($_GET[type]){
		case "0":
			$pageurl="getbonusadv";
			break;
		case "1":
			$pageurl="getzhi";
			break;
		case "2":
			if(!empty($alipayuserid) && !empty($_GET[trade_no])){
				//域名处理
				if(isset($_GET[domain])){
					$url=$_GET[domain].'/api/getremote.php?trade_no='.$_GET[trade_no]."&uid=".$alipayuserid; //远程
				}else{
					$url='./api/getremote.php?trade_no='.$_GET[trade_no]."&uid=".$alipayuserid; //本地
				}
				$content=file_get_contents($url, false, stream_context_create($opts)); //打开远程支付宝登录文件
				if($content==1){
					echo "<script>alert('登录成功！');</script>";
					exit;
				}
				echo "<script>alert('登录错误！');</script>";
				exit;
			}
			break;
		case "3":
			if(!empty($alipayuserid) && !empty($_GET[trade_no])){
				//域名处理
				if(isset($_GET[domain])){
					$url=$_GET[domain].'/api/getremote2.php?trade_no='.$_GET[trade_no]."&uid=".$alipayuserid; //远程
				}else{
					$url='./api/getremote2.php?trade_no='.$_GET[trade_no]."&uid=".$alipayuserid; //本地
				}
				$content=file_get_contents($url, false, stream_context_create($opts)); //打开远程支付宝登录文件
				if($content==1){
					echo "<script>alert('登录成功！');</script>";
					exit;
				}
				echo "<script>alert('登录错误！');</script>";
				exit;
			}
			break;
		case "4":
			$url='./api/getremote3.php?uid='.$alipayuserid; //本地
			$content=file_get_contents($url, false, stream_context_create($opts)); //打开远程支付宝登录文件
			if($content==1){
				echo "<script>alert('验证成功！');</script>";
				exit;
			}
			echo $url."<script>alert('验证错误！');</script>";
			exit;
			break;
        case "5":
            $url='./api/getremote4.php?uid='.$alipayuserid. '&user=' .$_GET[user]; //本地

            $opts = array(
                'http'=>array(
                    'method'=>"GET",
                    'timeout'=>10,//单位秒
                )
            );
            $content=file_get_contents($url, false, stream_context_create($opts)); //打开远程支付宝登录文件
            if($content==1){
                echo "<script>alert('验证成功！');</script>";
                exit;
            }
            echo "<script>alert('验证错误！');</script>";
            exit;
            break;
        case "6":
            if(!empty($alipayuserid) && !empty($_GET[trade_no])){
                //域名处理
                if(isset($_GET[domain])){
                    $url=$_GET[domain].'/Account/aliback?trade_no='.$_GET[trade_no]."&uid=".$alipayuserid; //远程
                }else{
                    $url='./Account/aliback?trade_no='.$_GET[trade_no]."&uid=".$alipayuserid; //本地
                }
                $content=file_get_contents($url, false, stream_context_create($opts)); //打开远程支付宝登录文件
                if($content==1){
                    echo "<script>alert('登录成功！');</script>";
                    exit;
                }
                echo "<script>alert('登录错误！');</script>";
                exit;
            }
            break;
		default:
			;
	}
}
//域名处理
if(isset($_GET[domain])){
	header('location:'.$_GET[domain].'/api/'.$pageurl.'.php?trade_no='.$_GET[trade_no]."&uid=".$alipayuserid);
}else{
		header('location:./api/'.$pageurl.'.php?trade_no='.$_GET[trade_no]."&uid=".$alipayuserid);
}
?>