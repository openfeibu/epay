<?php
	@header('Content-Type: text/html; charset=UTF-8');
	if($userrow['active'] == 0){
		sysmsg('由于你的商户违反相关法律法规与《'.$conf['web_name'].'用户协议》，已被禁用！');
	}
	if($userrow['active'] == 2){
		sysmsg('<div style="text-align: center;">支付系统正在升级，请耐心等候！</div>');
	}
	$isorderpage = 0;

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
    $exptime=time() + 300;
    $DB->query("replace into `pay_online` (`sid`, `username`, `ip`, `exptime`) VALUES  ('$sid', '$_SESSION[userid]', '$clientip', '$exptime')");
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
<div class="app app-header-fixed ">


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
            <a href="<?php echo $website_urls ?>user" class="navbar-brand text-lt">
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
                <span class="btn no-shadow navbar-btn" style="color: red;">公告 :  欢迎尊敬的商户用户使用本系统</span>
            </div>
            <!-- / buttons -->

            <!-- nabar right -->
            <ul class="nav navbar-nav navbar-right">
                <li class="dropdown">
                    <a href="<?php echo $website_urls;?>user" data-toggle="dropdown" class="dropdown-toggle clear" data-toggle="dropdown">
              <span class="thumb-sm avatar pull-right m-t-n-sm m-b-n-sm m-l-sm">
                <img src="./assets/img/user.png" alt="...">
                <i class="on md b-white bottom"></i>
              </span>
                        <span class="hidden-sm hidden-md"
                              style="text-transform:uppercase;"><?php echo $_SESSION['userid'] ?></span> <b
                                class="caret"></b>
                    </a>
                    <!-- dropdown -->
                    <ul class="dropdown-menu animated fadeInRight w">
                        <li>
                            <a href="index.php">
                                <span class="badge bg-danger pull-right"></span>
                                <span>用户中心</span>
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
                                <b class="label bg-info pull-right">N</b>
                                <span class="font-bold">用户首页</span>
                            </a>
                        </li>
                        <li id="account">
                            <a href="./" class="auto">
                  <span class="pull-right text-muted">
                    <i class="fa fa-fw fa-angle-right text"></i>
                    <i class="fa fa-fw fa-angle-down text-active"></i>
                  </span>
                                <i class="glyphicon glyphicon-leaf icon text-primary-dker"></i>
                                <!--<b class="label bg-info pull-right">A</b>-->
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
                                <li style="display: none;"><a href="verification.php" onclick="alert('暂未开放');return false;"><span>验证信息</span></a></li>
                                <li><a href="login.php?logout">
                                        <!--<b class="label bg-info pull-right">N</b>-->
                                        <span>退出</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="verification.php">
                                <i class="glyphicon glyphicon-leaf icon text-primary-dker"></i>
                                <b class="label bg-info pull-right">></b>
                                <span class="font-bold">证件提交</span>
                            </a>
                        </li>
                        <li class="line dk"></li>
                        <li class="hidden-folded padder m-t m-b-sm text-muted text-xs">
                            <span>服务</span>
                        </li>

						<?php
							if($userrow['type']== '2') {                                                          //代理商权限
								?>
                                <li style="display:block;">
                                    <a href="recharge.php">
                                        <i class="glyphicon glyphicon-leaf" style="color: greenyellow"></i>
                                        <span>在线充值</span>
                                    </a>
                                </li>
                                <li style="display:block;">
                                    <a href="recharge_record.php">
                                        <i class="glyphicon glyphicon-list" style="color: greenyellow"></i>
                                        <span>充值记录</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="slist.php">
                                        <i class="glyphicon glyphicon-check"></i>
                                        <span>提现申请列表</span>
                                    </a>
                                </li>
								<?php
							}
						?>

                        <li>
                            <a href="./order.php">
                                <i class="glyphicon glyphicon-list-alt"></i>
                                <span>订单记录</span>
                            </a>
                        </li>

                        <li style="display: none;">
                            <a href="settle.php">
                                <i class="glyphicon glyphicon-check"></i>
                                <span>结算记录</span>
                            </a>
                        </li>
                        <li style="">
                            <a href="balance.php">
                                <i class="glyphicon glyphicon-check"></i>
                                <span>余额明细</span>
                            </a>
                        </li>
                        <!--
                        http://www.runoob.com/bootstrap/bootstrap-glyphicons.html
                        Bootstrap 字体图标(Glyphicons)
                        -->

                        <li>
                            <a href="apply.php">
                                <i class="glyphicon glyphicon-euro"></i>
                                <span>申请提现</span>
                            </a>
                        </li>
                        <li>
                            <a href="outpaylist.php">
                                <i class="glyphicon glyphicon-check"></i>
                                <span>提现记录</span>
                            </a>
                        </li>
						<?php
							if($_SESSION['agentuuid'] == '1'){
								?>
                                <li><a href="ulist.php"><i class="glyphicon glyphicon-check"></i><span>商户管理</span></a></li>
                                <li><a href="order2.php"><i class="glyphicon glyphicon-check"></i><span>商户订单</span></a></li>
								<?php
							}
							if($userrow['type'] == '2'){        //是否为结算用户
								?>
                                <li><a href="./channel.php"><i class="glyphicon glyphicon-th-list"></i><span>通道管理</span></a>
								<?php
							}
						?>


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