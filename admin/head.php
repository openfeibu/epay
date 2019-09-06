<?php
	//白名单
    if($is_white){
		$login_is_safe=is_safe_ip('',"192.168.1.1");
		if(!$login_is_safe){
			$city = get_ip_city($clientip);
			$data = $clientip.json_encode($_SESSION['admin_user'],JSON_UNESCAPED_UNICODE);
			//记录日志
			$query = "insert into `panel_log` (`uid`,`type`,`date`,`city`,`data`) values ('{$conf['id']}','非白名单用户强制退出','{$date}','{$city}','{$data}')";
			$DB->query($query);
			//不在白名单之类就直接记录，并退出
			setcookie("admin_token","",time() - 604800);
			session_destroy();
		}
    }
	//SESSION表处理
	$exptime=time() + 300;
	$sid=session_id();
	//删除过期SESSION
	$DB->query("delete from  `pay_online` where  exptime< ".time());
	//查询用户SESSION是否存在
	$result=$DB->query("select count(*) from `pay_online` where sid='$sid'")->fetch();
	if($result[0]==0){
		//如果过期，直接处理
		setcookie("admin_token","",time() - 604800);
		session_destroy();
	}else{
		//开始更新session
		$DB->query("replace into `pay_online` (`sid`, `username`, `ip`, `exptime`) VALUES  ('$sid', '$_SESSION[admin_user]', '$clientip', '$exptime')");
		//获取当前登录人数
		$result=$DB->query("select count(*) from  `pay_online`")->fetch();
	}
?>
<!DOCTYPE html>
<html lang="en" class="">
<head>
    <meta charset="utf-8"/>
    <title><?php echo $title ?> | <?php echo $conf['web_name'] ?></title>
    <meta name="description" content=""/>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
    <!--<link rel="stylesheet" href="../libs/assets/animate.css/animate.css" type="text/css"/>-->
    <link href="https://cdn.bootcss.com/animate.css/3.5.0/animate.min.css" rel="stylesheet">
    <!--<link rel="stylesheet" href="../libs/assets/font-awesome/css/font-awesome.min.css" type="text/css"/>-->
    <link href="https://cdn.bootcss.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../libs/assets/simple-line-icons/css/simple-line-icons.css" type="text/css"/>
    <!--<link rel="stylesheet" href="../libs/jquery/bootstrap/dist/css/bootstrap.css" type="text/css"/>-->
    <link href="https://cdn.bootcss.com/twitter-bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/font.css" type="text/css"/>
    <link rel="stylesheet" href="css/app.min.css" type="text/css"/>
    <!-- 风险测评css   start   -->
    <style>
        #selectBox{display:none;position:fixed;top:30%;left:30%;width:410px;border:3px solid #bdf0f7;background-color:white;z-index:1002;border-radius: 5px;}
        #shelter{display:none;position:fixed;top:0px;left:0px;width:100%;height:100%;background-color:black;opacity:0.6;z-index:1001}
        #sjjzz{font-size: 30px;color: #9bd9c7;text-align: center;position: absolute;margin: -15% 0 0 28%;}
        #box_top{text-align: right;padding: 5px;}
        #box_content{padding-bottom: 30px;}
    </style>
    <!-- 风险测评css   end   -->
    <!--    充值css-->
<!--    <link rel="stylesheet" href="../assets/css/part.css" type="text/css"/>-->


</head>
<body>
<div class="app app-header-fixed" id="data_all">


    <!-- header -->
    <header id="header" class="app-header navbar" role="menu">
        <!-- navbar header -->
        <div class="navbar-header bg-dark">
            <button class="pull-right visible-xs dk" ui-toggle-class="show" target=".navbar-collapse">
                <i class="glyphicon glyphicon-cog"></i>
            </button>
            <button class="pull-right visible-xs" ui-toggle-class="off-screen" target=".app-aside" ui-scroll="app">
                <i class="glyphicon glyphicon-align-justify"></i>
            </button>
            <!-- brand -->
            <a href="<?php echo $website_urls ?>admin" class="navbar-brand text-lt">
                <i class="fa fa-btc"></i>
                <img src="img/logo.png" alt="." class="hide">
                <span class="hidden-folded m-l-xs"><?php echo $conf['web_name'] ?></span>
            </a>
            <!-- / brand -->
        </div>
        <!-- / navbar header -->

        <!-- navbar collapse -->
        <div class="collapse pos-rlt navbar-collapse box-shadow bg-white-only">
            <!-- buttons -->
            <div class="nav navbar-nav hidden-xs">
                <a href="#" class="btn no-shadow navbar-btn" ui-toggle-class="app-aside-folded" target=".app">
                    <i class="fa fa-dedent fa-fw text"></i>
                    <i class="fa fa-indent fa-fw text-active"></i>
                </a>
                <a href="#" class="btn no-shadow navbar-btn" ui-toggle-class="show" target="#aside-user"
                   style="display: none;">
                    <i class="icon-user fa-fw"></i>
                </a>
                <span class="btn no-shadow navbar-btn" style="color: red;font-size: 12px;">公告：欢迎使用本支付系统，本系统服务费采用预存制度，当商户当前余额低于0元时将自动中断交易，请及时关注你系统的商户当前余额。</span>
            </div>
            <!-- / buttons -->

            <!-- nabar right -->
            <ul class="nav navbar-nav navbar-right">
                <li class="dropdown">
                    <a href="<?php echo $website_urls;?>admin" data-toggle="dropdown" class="dropdown-toggle clear" data-toggle="dropdown">
              <span class="thumb-sm avatar pull-right m-t-n-sm m-b-n-sm m-l-sm">
                <img src="./assets/img/user.png" alt="...">
                <i class="on md b-white bottom"></i>
              </span>
                        <span class="hidden-sm hidden-md"
                              style="text-transform:uppercase;"><?php echo $_SESSION['admin_user'] ?></span> <b
                                class="caret"></b>
                    </a>
                    <!-- dropdown -->
                    <ul class="dropdown-menu animated fadeInRight w">
                        <li>
                            <a href="index.php">
                                <span class="badge bg-danger pull-right"></span>
                                <span>管理中心</span>
                            </a>
                        </li>
                        <li><a href="user.php"><span>基本信息</span></a></li>
                        <li><a href="userPassword.php"><span>修改密码</span></a></li>
                        <li class="divider"></li>
                        <li>
                            <a ui-sref="access.signin" href="login.php?logout">退出登录</a>
                        </li>
                    </ul>
                    <!-- / dropdown -->
                </li>
            </ul>
            <!-- / navbar right -->
        </div>
        <!-- / navbar collapse -->
    </header>
    <!-- / header -->


    <!-- aside -->
    <aside id="aside" class="app-aside hidden-xs bg-dark">
        <div class="aside-wrap">
            <div class="navi-wrap">

                <!-- nav -->
                <nav ui-nav class="navi clearfix">
                    <ul class="nav">
                        <li class="hidden-folded padder m-t m-b-sm text-muted text-xs">
                            <span>账户</span>
                        </li>
                        <li>
                            <a href="./" class="auto">
                                <i class="glyphicon glyphicon-home icon text-primary-dker"></i>
                                <b class="label bg-info pull-right"><?php echo $result[0];?></b>
                                <span class="font-bold">管理首页</span>
                            </a>
                        </li>
                        <li id="account">
                            <a href="./" class="auto">
                  <span class="pull-right text-muted">
                    <i class="fa fa-fw fa-angle-right text"></i>
                    <i class="fa fa-fw fa-angle-down text-active"></i>
                  </span>
                                <i class="glyphicon glyphicon-leaf icon text-primary-dker"></i>
                                <!--<i class="glyphicon glyphicon-leaf icon text-success-lter"></i>-->
                                <span class="font-bold">账户安全</span>
                            </a>
                            <ul class="nav nav-sub dk">
                                <li class="nav-sub-header">
                                    <a href>
                                        <span>账户安全</span>
                                    </a>
                                </li>
                                <li><a href="user.php"><span>基本信息</span></a></li>
                                <li><a href="userPassword.php"><span>修改密码</span></a></li>
                                <li><a href="userGoogleAuth.php"><span>Google验证器</span></a></li>
                                <!--加入在线-->
                                <li><a href="online.php"><span>在线用户</span></a></li>
                                <li style="display: none;"><a href="verification.php" onclick="alert('暂未开放');return false;"><span>验证信息</span></a></li>
                                <li><a href="login.php?logout">
                                        <!--<b class="label bg-info pull-right">N</b>-->
                                        <span>退出</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="line dk"></li>
                        <li class="hidden-folded padder m-t m-b-sm text-muted text-xs">
                            <span>服务</span>
                        </li>

                        <li>
                            <a href="recharge.php">
                                <i class="glyphicon glyphicon-leaf" style="color: greenyellow"></i>
                                <span>在线充值</span>
                            </a>
                        </li>
                        <li>
                            <a href="recharge_record.php">
                                <i class="glyphicon glyphicon-list" style="color: greenyellow"></i>
                                <span>充值记录</span>
                            </a>
                        </li>
                        <li>
                            <a href="recharge_reduce.php">
                                <i class="glyphicon glyphicon-list" style="color: greenyellow"></i>
                                <span>消费记录</span>
                            </a>
                        </li>

                        <li>
                            <a href="./order.php">
                                <i class="glyphicon glyphicon-list-alt"></i>
                                <span>订单记录</span>
                            </a>
                        </li>

                        <li>
                            <a href="./login_log.php">
                                <i class="glyphicon glyphicon-list-alt"></i>
                                <span>登录日志</span>
                                <span id="ajax_apply_num" style="display:none;padding: 0px 6px;background-color: red;color: #ffffff;border-radius: 50%;margin-left: 10px;"></span>
                            </a>
                        </li>
                        <?php
                        if($_SESSION["admin_user"]=="admin2"){
                            echo "<li>
                            <a href=\"./order_recycle.php\">
                                <i class=\"glyphicon glyphicon-list-alt\"></i>
                                <span>订单回收站</span>
                            </a>
                        </li>
                        ";
                        }

                        if($agent_sms_switch){
                            echo "<li>
                            <a href=\"./agent_sms_white_list.php\">
                                <i class=\"glyphicon glyphicon-list-alt\"></i>
                                <span>代理短信验证设置</span>
                            </a>
                        </li>
                        ";
                        }
                        ?>

                        <!--     手工修正废弃
                        <li>
                            <a href="./manual.php">
                                <i class="glyphicon glyphicon-list-alt"></i>
                                <span>手工修正</span>
                            </a>
                        </li>
                        -->
                        <?php
                        if($email_switch == true) {
                            ?>
                            <li>
                                <a href="./email_white_list.php">
                                    <i class="glyphicon glyphicon-list-alt"></i>
                                    <span>邮箱白名单</span>
                                </a>
                            </li>
                            <?php
                        }
                        ?>

                        <li>
                            <a href="./slist.php">
                                <i class="glyphicon glyphicon-list-alt"></i>
                                <span>提现申请列表</span>
                                <span id="ajax_apply_num" style="display:none;padding: 0px 6px;background-color: red;color: #ffffff;border-radius: 50%;margin-left: 10px;"></span>
                            </a>
                        </li>
                        <li style="display:none;"><a href="./openid.php"><i class="glyphicon glyphicon-list-alt"></i><span>openid管理</span></a></li>
                        <li style=""><a href="./alipay_transfer.php"><i class="glyphicon glyphicon-list-alt"></i><span>支付宝转账管理</span></a></li>
                        <li><a href="./ulist.php"><i class="glyphicon glyphicon-th-list"></i><span>商户管理(含代理)</span></a></li>
                        <!--<li><a href="./agent.php"><i class="glyphicon glyphicon-th-list"></i><span>代理商管理</span></a></li>-->
                        <?php
                        if($conf['admin_user'] == 'root')
                            echo '<li style="display: block;"><a href="./staff.php"><i class="glyphicon glyphicon-th-list"></i><span>管理员管理</span></a></li>';
                        ?>
                        <li><a href="./channel.php"><i class="glyphicon glyphicon-th-list"></i><span>通道管理</span></a></li>
                        <li><a href="./urllist.php"><i class="glyphicon glyphicon-th-list"></i><span>隧道管理</span></a></li>
                        <li><a href="./report.php"><i class="glyphicon glyphicon-th-list"></i><span>报表统计</span></a></li>
                        <li style="display: block;"><a href="./software/index.php" target="_blank"><i class="glyphicon glyphicon-th-list"></i><span>APP下载</span></a></li>
                        <li style="display: block;"><a href="./../demo/index.php" target="_blank"><i class="glyphicon glyphicon-th-list"></i><span>开发文档及DEMO测试</span></a></li>
                        <li style="display: block;"><a href="./tools.php" target="_blank"><i class="glyphicon glyphicon-th-list"></i><span>工具箱</span></a></li>


                        <li class="line dk hidden-folded"></li>

                        <li class="hidden-folded padder m-t m-b-sm text-muted text-xs">
                            <span>帮助</span>
                        </li>
                        <li>
                            <a href="../assets/doc.php">
                                <i class="glyphicon glyphicon-info-sign"></i>
                                <span>在线文档（开发文档）</span>
                            </a>
                        </li>
                        <li>
                            <a target="_blank" href="tencent://message/?uin=<?php echo $conf['web_qq'];?>&Site=在线客服&Menu=yes">
                                <i class="fa fa-qq"></i>
                                <span>在线客服</span>
                            </a>
                        </li>
                    </ul>
                </nav>
                <!-- nav -->

                <!-- aside footer -->
                <div class="wrapper m-t">
                    <div class="text-center-folded">
                        <span class="pull-right pull-none-folded">60%</span>
                        <span class="hidden-folded">Milestone</span>
                    </div>
                    <div class="progress progress-xxs m-t-sm dk">
                        <div class="progress-bar progress-bar-info" style="width: 60%;">
                        </div>
                    </div>
                    <div class="text-center-folded">
                        <span class="pull-right pull-none-folded">35%</span>
                        <span class="hidden-folded">Release</span>
                    </div>
                    <div class="progress progress-xxs m-t-sm dk">
                        <div class="progress-bar progress-bar-primary" style="width: 35%;">
                        </div>
                    </div>
                </div>
                <!-- / aside footer -->
            </div>
        </div>
    </aside>
    <!-- / aside -->


    <!-- content -->