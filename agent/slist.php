<?php
/**
 * 结算列表
 **/
exit();
include("../includes/common.php");
$title = '结算列表';
include './head.php';
if($islogin != 1){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}

function sctonum($num,$double = 5){
    if(false !== stripos($num,"e")){
        $a = explode("e",strtolower($num));
        return bcmul($a[0],bcpow(10,$a[1],$double),$double);
    }else{
        return $num;
    }
}

?>
<nav class="navbar navbar-fixed-top navbar-default">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar"
                    aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">导航按钮</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="./"><?php echo $conf['web_name'] ?></a>
        </div><!-- /.navbar-header -->
        <div id="navbar" class="collapse navbar-collapse">
            <ul class="nav navbar-nav navbar-right">
                <li>
                    <a href="./"><span class="glyphicon glyphicon-home"></span> 平台首页</a>
                </li>
                <li><a href="./order.php"><span class="glyphicon glyphicon-shopping-cart"></span> 订单管理</a></li>
                <li class="active"><a href="./slist.php"><span class="glyphicon glyphicon-shopping-cart"></span>
                        代理结算</a></li>
                <!--
                <li>
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-cloud"></span> 代理结算<b class="caret"></b></a>
                  <ul class="dropdown-menu">

                    <li><a href="./slist.php">结算记录</a><li>
                  </ul>
                </li>
                -->
                <li><a href="./ulist.php"><span class="glyphicon glyphicon-user"></span> 商户管理</a></li>
                <li><a href="./login.php?logout"><span class="glyphicon glyphicon-log-out"></span> 退出登陆</a></li>
            </ul>
        </div><!-- /.navbar-collapse -->
    </div><!-- /.container -->
</nav><!-- /.navbar -->
<div class="container" style="padding-top:70px;">
    <div class="col-md-12 center-block" style="float: none;">
        <?php

        $my = isset($_GET['my']) ? $_GET['my'] : null;

        echo '<form action="slist.php" method="GET" class="form-inline"><input type="hidden" name="my" value="search">
  <div class="form-group">
    <label>搜索</label>
	<select name="column" class="form-control"><option value="pid">商户号</option><option value="type">结算方式</option><option value="account">结算账号</option><option value="username">姓名</option></select>
  </div>
  <div class="form-group">
    <input type="text" class="form-control" name="value" placeholder="搜索内容">
  </div>
  <button type="submit" class="btn btn-primary">搜索</button>
</form>';

        if($my == 'search'){
            $sql = " `{$_GET['column']}`='{$_GET['value']}'";
            $numrows = $DB->query("SELECT * from pay_settle WHERE ".$prevsql." and {$sql}")->rowCount();
            $con = '包含 '.$_GET['value'].' 的共有 <b>'.$numrows.'</b> 条订单';
        }else{
            $numrows = $DB->query("SELECT * from pay_settle WHERE ".$prevsql." and 1")->rowCount();
            $sql = " 1";
            $con = '共有 <b>'.$numrows.'</b> 条订单';
        }
        echo $con;
        ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>结算日期</th>
                    <th><u>支付结算金额(元)</u><br/>支付宝/微信/QQ钱包</th>
                    <th>代理佣金</th>
                    <th>商户号</th>
                    <th>备注</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $pagesize = 30;
                $pages = intval($numrows / $pagesize);
                if($numrows % $pagesize){
                    $pages++;
                }
                if(isset($_GET['page'])){
                    $page = intval($_GET['page']);
                }else{
                    $page = 1;
                }
                $offset = $pagesize * ($page - 1);

                $rs = $DB->query("SELECT * FROM pay_settle WHERE  ".$prevsql." and {$sql} order by time desc limit $offset,$pagesize");
                $sdfsde = "SELECT * FROM pay_settle WHERE  ".$prevsql." and {$sql} order by id desc ";
                while($res = $rs->fetch()){
                    if($res['status'] == 1){
                        $status = "<font color=green>已结算</font>";
                    }else{
                        $status = "<font color=red>未结算</font>";
                    }

                    $userrow = $DB->query("SELECT * FROM pay_admin WHERE admin_user='{$user}' limit 1")->fetch();
                    $admin_alipay_fee = $userrow['alipay_fee'];
                    $admin_qqpay_fee = $userrow['qqpay_fee'];
                    $admin_wxpay_fee = $userrow['wxpay_fee'];

                    $userrow = $DB->query("SELECT * FROM pay_user WHERE id='{$res['pid']}' limit 1")->fetch();
                    $myalipay_fee = $userrow['alipay_fee'] - $admin_alipay_fee;
                    $mywxpay_fee = $userrow['wxpay_fee'] - $admin_wxpay_fee;
                    $myqqpay_fee = $userrow['qqpay_fee'] - $admin_qqpay_fee;
                    echo '<tr><td>'.substr($res['time'],0,10).'</td><td>￥ <b>'.$res['money'].'</b><br/>'.$res['alipay'].'/'.$res['wxpay'].'/'.$res['qqpay'].'</td><td>￥ <b>'.sctonum(strval(round((round($res['alipay'] * $myalipay_fee,5) + round($res['wxpay'] * $mywxpay_fee,5) + round($res['qqpay'] * $myqqpay_fee,5)),5)),5).'</b></td><td>'.substr($res['pid'],0,10).'</td><td>'.$res['memos'].'</td></tr>';
                    //echo '<tr><td><b>'.$res['id'].'</b></td><td>'.$res['pid'].'</td><td>'.$res['type'].'</td><td>'.$res['account'].'&nbsp;'.$res['username'].'</td><td><b>'.$res['money'].'</b>&nbsp;/&nbsp;<b>'.$res['fee'].'</b></td><td>'.$res['time'].'</td><td>'.($res['status']==1?'<font color=green>已完成</font>':'<font color=blue>未完成</font>').'</td></tr>';
                }
                ?>
                </tbody>
            </table>
        </div>
        <?php
        echo '<ul class="pagination">';
        $first = 1;
        $prev = $page - 1;
        $next = $page + 1;
        $last = $pages;
        if($page > 1){
            echo '<li><a href="slist.php?page='.$first.$link.'">首页</a></li>';
            echo '<li><a href="slist.php?page='.$prev.$link.'">&laquo;</a></li>';
        }else{
            echo '<li class="disabled"><a>首页</a></li>';
            echo '<li class="disabled"><a>&laquo;</a></li>';
        }
        for($i = 1; $i < $page; $i++) echo '<li><a href="slist.php?page='.$i.$link.'">'.$i.'</a></li>';
        echo '<li class="disabled"><a>'.$page.'</a></li>';
        for($i = $page + 1; $i <= $pages; $i++) echo '<li><a href="slist.php?page='.$i.$link.'">'.$i.'</a></li>';
        echo '';
        if($page < $pages){
            echo '<li><a href="slist.php?page='.$next.$link.'">&raquo;</a></li>';
            echo '<li><a href="slist.php?page='.$last.$link.'">尾页</a></li>';
        }else{
            echo '<li class="disabled"><a>&raquo;</a></li>';
            echo '<li class="disabled"><a>尾页</a></li>';
        }
        echo '</ul>';
        #分页

        ?>
    </div>
</div>