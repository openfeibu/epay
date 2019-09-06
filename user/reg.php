<?php
exit;
/**
     * 商户注册
     **/
    include("../includes/common.php");
    $title = $website_name.' - 商户注册';


?>


<!DOCTYPE html>
<html lang="zh-cn">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title><?=$title?></title>
  <meta name="keywords" content=""/>
  <meta name="description" content=""/>
  <link href="//cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet"/>
  <script src="//cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>

<link rel="stylesheet" href="https://template.down.swap.wang/ui/angulr_2.0.1/html/css/app.css" type="text/css" />

  <script src="//cdn.bootcss.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
  <!--[if lt IE 9]>
    <script src="http://libs.useso.com/js/html5shiv/3.7/html5shiv.min.js"></script>
    <script src="http://libs.useso.com/js/respond.js/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>
<body>

  <nav class="navbar navbar-fixed-top navbar-default">
    <div class="container">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
          <span class="sr-only">导航按钮</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="./">商户注册</a>
      </div><!-- /.navbar-header -->
      <div id="navbar" class="navbar-collapse collapse" aria-expanded="false" style="height: 1px;">
        <ul class="nav navbar-nav navbar-right">
          
<li><a href="/user/login.php"><span class="glyphicon glyphicon glyphicon-shopping-cart"></span>   登录</a>
          </li>

           <li> <a href="/user/reg.php?my=add"><span class="glyphicon glyphicon-cloud"></span>   注册</a>
          </li>



      </ul>
      </div><!-- /.navbar-collapse -->
    </div><!-- /.container -->
  </nav><!-- /.navbar -->
  <div class="container" style="padding-top:80px;">
    <div class="col-md-12 center-block" style="float: none;">







<?php

$my=isset($_GET['my'])?$_GET['my']:null;

if($my=='add'){
    echo '<div class="panel panel-primary">
<div class="panel-heading"><h3 class="panel-title">商户注册</h3></div>';
echo '<div class="panel-body">';
echo '<form action="./reg.php?my=add_submit" method="POST">
<div class="form-group">
<label>结算方式:</label><br><select class="form-control" name="alipay_uid"><option value="支付宝">支付宝</option><option value="QQ钱包">QQ钱包</option></select>
</div>
<div class="form-group">
<label>结算账号:</label><br>
<input type="text" class="form-control" name="account" value="" required>
</div>
<div class="form-group">
<label>结算姓名:</label><br>
<input type="text" class="form-control" name="username" value="" required>
</div>
<div class="form-group">
<label>网站域名:</label><br>
<input type="text" class="form-control" name="url" value="" placeholder="可留空">
</div>
<input type="submit" class="btn btn-primary btn-block"
value="立即注册"></form>';
echo '<br/><a href="./login.php">>>返回用户中心</a>';
echo '</div></div>';
}elseif($my=='add_submit'){
    $account = $_POST['account'];
    $username = $_POST['username'];
    $alipay_uid = $_POST['alipay_uid'];
    $money = '0.00';
    $url = $_POST['url'];
    $type = $_POST['type'];
    $active = '1_激活';
    if($account == NULL or $username == NULL){
        showmsg('注册失败,请确保加*项都不为空!',3);
    }else{
        $key = random(32);
        $sds = $DB->exec("INSERT INTO `pay_user` (`key`, `account`, `username`, `alipay_uid`, `url`, `addtime`, `type`, `active`) VALUES ('{$key}', '{$account}', '{$username}', '{$alipay_uid}', '{$url}', '{$date}', '{$type}', '{$active}')");
        $pid = $DB->lastInsertId();
        if ($sds) {
            showmsg('商户注册成功！商户ID：' . $pid . '<br>商户KEY：' . $key . '<br/><br/><a href="./login.php">>>返回用户中心</a>', 1);
        }else{
            showmsg('商户注册失败！<br/>错误信息：' . $DB->errorCode(), 4);
        }
    }
}