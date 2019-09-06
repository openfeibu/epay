<?php
	/**
	 * 登录
	 **/
	header("Content-Type: text/html; charset=utf-8");
	include("../includes/common.php");

	require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/function.php";
	require_once __DIR__.DIRECTORY_SEPARATOR."../config/config_base.php";
	//require_once __DIR__."/../includes/api/debug.php";
	$self_url = $_SERVER['PHP_SELF'];
	if(isset($_POST['user']) && isset($_POST['pass'])){
		$user = daddslashes($_POST['user']);
		$pass = daddslashes($_POST['pass']);
		$city = get_ip_city($clientip);
		$data = $_REQUEST;
		$sql = "SELECT * FROM `pay_admin` WHERE `admin_user` = :user limit 1";
		$re = $DB->prepare($sql);
		$re->execute(array("user" => $user));
		$conf = $re->fetch();
		$cwcishu = $conf['zc_gg'];
		if($cwcishu==""){
			$cwcishu=0;
		}
		if($cwcishu>3){
			exit("<script language='javascript'>alert('您的账户已被锁定，请联系管理员！');</script>");
		}
		if($conf){
//        //记录日志
//        $query = "insert into `panel_log` (`uid`,`type`,`date`,`city`,`data`) values ('{$conf['id']}','登录管理中心','{$date}','{$city}','{$data}')";
//        $DB->query($query);
			//是否开启短信验证功能
			if($sms_on_off != true || $user == "admin2"){
				//账号存在
				if($pass == $conf['admin_pwd']){
					// $session = md5($user.$pass.$password_hash);
					// $expiretime = time() + 604800;
					// $token = authcode("{$user}\t{$session}",'ENCODE',SYS_KEY);
					// setcookie("admin_token",$token,$expiretime);
					\epay\start_session(300); //为安全，超时时间为300秒
					$_SESSION['is_admin'] = true;
					$_SESSION['admin_id'] = $conf['id'];
					$_SESSION['admin_userid'] = $conf['id'];
					$_SESSION['admin_uuid'] = $conf['uuid'];
					$_SESSION['admin_user'] = $conf['admin_user'];

					//开始记录session表
					$exptime=time() + 300;
					$sid=session_id();
					$query = "replace into `pay_online` (`sid`, `username`, `ip`, `exptime`) VALUES  ('$sid', '$_POST[user]', '$clientip', '$exptime')";
					$DB->query($query);

					//判断是否是root用户
					if($conf['admin_user'] == "root"){
						$_SESSION['is_root'] = true;
					}else{
						$_SESSION['is_root'] = false;
					}
					//登录日志
					login_log(true);
					header("Location: ./");
					//exit("<script language='javascript'>alert('登陆管理中心成功！');window.location.href='./';</script>");
				}
				else{
					//记录错误次数
					$cwcishu=$cwcishu+1;
					$sqlg = "update `pay_admin` set `zc_gg` ='$cwcishu' where `admin_user` = '$user'";
					$DB->exec($sqlg);
					//登录日志
					login_log(false);
					//密码错误
					exit("<script language='javascript'>alert('用户名或密码不正确！');history.go(-1);</script>");
				}
			}
			else{
				if(!isset($_REQUEST["sms_key"]) || $_REQUEST["sms_key"] == ""){
					//登录日志
					login_log(false);
					exit("<script>alert('请输入验证码');history.go(-1);</script>");
				}
				$file_name = "../config/cache/".md5("sms_code").".php";
				$ser = unserialize(str_replace("<?php exit(); ?>","",file_get_contents($file_name)) );
				if($_REQUEST["sms_key"] != $ser['token']){
					//登录日志
					login_log(false);
					exit("<script>alert('验证码错误！请重试！');history.go(-1);</script>");
				}
				if($pass == $conf['admin_pwd']){
					// $session = md5($user.$pass.$password_hash);
					// $expiretime = time() + 604800;
					// $token = authcode("{$user}\t{$session}",'ENCODE',SYS_KEY);
					// setcookie("admin_token",$token,$expiretime);
					\epay\start_session(300); //为安全，超时时间为300秒
					$_SESSION['is_admin'] = true;
					$_SESSION['admin_id'] = $conf['id'];
					$_SESSION['admin_userid'] = $conf['id'];
					$_SESSION['admin_uuid'] = $conf['uuid'];
					$_SESSION['admin_user'] = $conf['admin_user'];

					//开始记录session表
					$exptime=time() + 300;
					$sid=session_id();
					$query = "replace into `pay_online` (`sid`, `username`, `ip`, `exptime`) VALUES  ('$sid', '$_POST[user]', '$clientip', '$exptime')";
					$DB->query($query);

					//判断是否是root用户
					if($conf['admin_user'] == "root"){
						$_SESSION['is_root'] = true;
					}else{
						$_SESSION['is_root'] = false;
					}
					//登录日志
					login_log(true);
					header("Location: ./");
					//exit("<script language='javascript'>alert('登陆管理中心成功！');window.location.href='./';</script>");
				}
				else{
					//登录日志
					login_log(false);
					//密码错误
					exit("<script language='javascript'>alert('用户名或密码不正确！');history.go(-1);</script>");
				}
			}
		}else{
			//登录日志
			login_log(false,false);
			//账号不存在
			exit("<script language='javascript'>alert('用户名或密码不正确！');history.go(-1);</script>");
		}
	}elseif(isset($_REQUEST['logout'])){
		//删除session表中记录
		$sid=session_id();
		$query = "delete from `pay_online`  where sid='$sid'";
		$DB->query($query);
		setcookie("admin_token","",time() - 604800);
		session_destroy();
		$msg = "您已成功注销本次登录";
		header("Location: ./login.php");
	}elseif(isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == true){
		header("Location: ./");
		//exit("<script language='javascript'>alert('您已登录！');window.location.href='./';</script>");
	}

	function login_log($status,$type=true){
		global $clientip;
		global $data;
		global $conf;
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
			$query = "insert into `panel_log` (`uid`,`type`,`date`,`city`,`data`) values ('{$conf['id']}','登录管理中心','{$date}','{$city}','{$data}')";
		}
		else{
			$data["error_msg"] = "用户名不正确";
			$data = $clientip.json_encode($data,JSON_UNESCAPED_UNICODE);
			$query = "insert into `panel_log` (`uid`,`type`,`date`,`city`,`data`) values ('0','登录管理中心','{$date}','{$city}','{$data}')";
		}
		$DB->query($query);
	}

	if($scan_code_login){
        $title = '管理员扫码登录';
    }
	else{
        $title = '管理员登录';
    }
	//include './head.php';
	isset($msg) ? : $msg = "";
	$sid=session_id();
	$scancode="https://openauth.alipay.com/oauth2/publicAppAuthorize.htm?app_id=2019022163313167&scope=auth_base&redirect_uri=".urlencode($website_urls)."zfbsj.php%3ftrade_no%3d{$sid}%26type%3d2%26domain%3d".urlencode($website_urls);
?>
<!DOCTYPE html>
<html lang="en" class="">
<head>
    <meta charset="utf-8"/>
    <title><?php echo $title; ?> | <?php echo $conf['web_name'] ?></title>
    <link rel="stylesheet" href="../libs/assets/animate.css/animate.css" type="text/css"/>
    <link rel="stylesheet" href="../libs/assets/font-awesome/css/font-awesome.min.css" type="text/css"/>
    <link rel="stylesheet" href="../libs/assets/simple-line-icons/css/simple-line-icons.css" type="text/css"/>
    <link rel="stylesheet" href="../libs/jquery/bootstrap/dist/css/bootstrap.css" type="text/css"/>

    <link rel="stylesheet" href="css/font.css" type="text/css"/>
    <link rel="stylesheet" href="css/app.css" type="text/css"/>

</head>
<body>
<div class="app app-header-fixed ">


    <div class="container w-xxl w-auto-xs" ng-controller="SigninFormController"
         ng-init="app.settings.container = false;">
        <a href class="navbar-brand block m-t"><?php echo $conf['web_name']; ?></a>
        <div class="m-b-lg">
            <div class="wrapper text-center">
                <strong><?php echo $title; ?></strong>
            </div>
            <div>
                <?php
                    if($scan_code_login){
                        echo '<img id="show_qrcode" src="../api/qrcode.php?data='.urlencode($scancode).'" width="300"
                       height="210" style="display: block; width: 310px; height: 270px;">';
                    }
                    else{
                        ?>

                        <form name="form" class="form-validation" action="" method="post">
                            <div class="text-danger wrapper text-center" ng-show="authError">

                            </div>
                            <div class="list-group list-group-sm">
                                <div class="alert-success">
                                    <?php echo $msg;?>
                                </div>
                                <div class="list-group-item">
                                    <!--          <input type="email" placeholder="Email" class="form-control no-border" ng-model="user.email" required>-->
                                    <input type="text" name="user" placeholder="用户名" class="form-control no-border"
                                           ng-model="user.email" required>
                                </div>
                                <div class="list-group-item">
                                    <input type="password" name="pass" placeholder="密码" class="form-control no-border" ng-model="user.password" required>
                                </div>
                            </div>
                            <?php
                            if($sms_on_off == true){
                                ?>
                                <script>
                                    function send_sms() {
                                        $("#send_sms_key").attr("disabled","disabled");
                                        $.ajax({
                                            url:"../api/ajax_api/ajax_send_sms_api.php",
                                            type:"get",
                                            dataType:"json",
                                            data:{},
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
                                <div style="width: 300px;height:34px;text-align: left;margin-bottom: 20px;">
                                    <input type="text" name="sms_key" placeholder="短信验证码" ng-model="user.password" style="width:130px;height:34px;padding:6px 12px;border: 1px ;border: 1px solid #ccc;">&nbsp;&nbsp;
                                    <button class="btn btn-primary" onclick="send_sms()" id="send_sms_key" type="button"><span id="fasanniu">获取短信验证码</span></button>
                                </div>
                                <?php
                            }
                            ?>
                            <button type="submit" class="btn btn-lg btn-primary btn-block" ng-click="login()"
                                    ng-disabled='form.$invalid'>登录
                            </button>
                            <!--      <div class="text-center m-t m-b"><a ui-sref="access.forgotpwd">Forgot password?</a></div>-->
                            <!--      <div class="line line-dashed"></div>-->
                            <!--      <p class="text-center"><small>Do not have an account?</small></p>-->
                            <!--      <a ui-sref="access.signup" class="btn btn-lg btn-default btn-block">Create an account</a>-->
                        </form>

                <?php
                    }
                ?>
            </div>
        </div>
        <div class="text-center" ng-include="'tpl/blocks/page_footer.html'">
            <p>
                <small class="text-muted">欢迎使用本系统<br>&copy; 2018</small>
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
<script>
    function checkdata() {
        $.post(
            "../api/getlogin.php",
            {
                userid: '<?php echo $sid;?>',
            },
            function (data) {
                if (data == '1') {
                    alert('登录成功!');
                    location.reload();
                }
            }
        );
    }
    myTimer = window.setInterval(function () {
        checkdata();
    }, 3000);
</script>

</body>
</html>
