<?php
/**
 * 订单列表
 **/
exit();
include("../includes/common.php");
$title = '订单列表';

function sctonum($num,$double = 5){
    if(false !== stripos($num,"e")){
        $a = explode("e",strtolower($num));
        return bcmul($a[0],bcpow(10,$a[1],$double),$double);
    }
}


include './head.php';
if($islogin == 1){
}else exit("<script language='javascript'>window.location.href='./login.php';</script>");
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
                <li class="active"><a href="./order.php"><span class="glyphicon glyphicon-shopping-cart"></span>
                        订单管理</a></li>
                <li><a href="./slist.php"><span class="glyphicon glyphicon-shopping-cart"></span> 代理结算</a></li>
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


        echo '<form action="order.php" method="GET" class="form-inline"><input type="hidden" name="my" value="search">
  <div class="form-group">
    <label>搜索</label>
	<select name="column" class="form-control"><option value="trade_no">订单号</option><option value="out_trade_no">商户订单号</option><option value="pid">商户号</option><option value="name">商品名称</option><option value="money">金额</option></select>
  </div>
  <div class="form-group">
    <input type="text" class="form-control" name="value" placeholder="搜索内容">
  </div>
  <button type="submit" class="btn btn-primary">搜索</button>
</form>';

        if($my == 'search'){
            $sql = $prevsql."  and `{$_GET['column']}`='{$_GET['value']}'";
            $numrows = $DB->query("SELECT * from pay_order WHERE{$sql}")->rowCount();
            $con = '包含 '.$_GET['value'].' 的共有 <b>'.$numrows.'</b> 条订单';
        }else{
            $numrows = $DB->query("SELECT * from pay_order WHERE  ".$prevsql."  and 1 ")->rowCount();
            $sql = $prevsql." and 1 ";
            $con = '共有 <b>'.$numrows.'</b> 条订单';
        }
        echo "代理商".$user."， 您当前下级用户".$con;
        ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>订单号/商户订单号</th>
                    <th>商品名称/金额</th>
                    <th>商户号/代理佣金</th>
                    <th>支付方式</th>
                    <th>创建时间/完成时间</th>
                    <th>支付状态</th>
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

                $rs = $DB->query("SELECT * FROM pay_order WHERE{$sql} order by addtime desc limit $offset,$pagesize");
                while($res = $rs->fetch()){

                    $userrow = $DB->query("SELECT * FROM pay_admin WHERE admin_user='{$user}' limit 1")->fetch();
                    $admin_alipay_fee = $userrow['alipay_fee'];
                    $admin_qqpay_fee = $userrow['qqpay_fee'];
                    $admin_wxpay_fee = $userrow['wxpay_fee'];

                    $userrow = $DB->query("SELECT * FROM pay_user WHERE id='{$res['pid']}' limit 1")->fetch();
                    $myalipay_fee = $userrow['alipay_fee'] - $admin_alipay_fee;
                    $mywxpay_fee = $userrow['wxpay_fee'] - $admin_wxpay_fee;
                    $myqqpay_fee = $userrow['qqpay_fee'] - $admin_qqpay_fee;

                    $nowrec_money = 0;
                    if($res['type'] == "wxpay"){
                        $nowrec_money = $res['money'] * mywxpay_fee;
                    }
                    if($res['type'] == "alipay"){
                        $nowrec_money = $res['money'] * $myalipay_fee;
                    }
                    if($res['type'] == "qqpay"){
                        $nowrec_money = $res['money'] * $myqqpay_fee;
                    }


                    echo '<tr><td><b>'.$res['trade_no'].'</b><br/>'.$res['out_trade_no'].'</td><td>'.$res['name'].'<br/>￥'.$res['money'].'</td><td>'.$res['pid'].'<br/>+'.$nowrec_money.'</td><td>'.$res['type'].'</td><td>'.$res['addtime'].'<br/>'.$res['endtime'].'</td><td>'.($res['status'] == 1 ? '<font color=green>已完成</font>' : '<font color=blue>未完成</font>').'</td></tr>';
                }
                ?>
                </tbody>
            </table>
        </div>
        <?php
        //echo "SELECT * FROM pay_order WHERE{$sql} order by addtime desc limit $offset,$pagesize";
        echo '<ul class="pagination">';
        $first = 1;
        $prev = $page - 1;
        $next = $page + 1;
        $last = $pages;
        if($page > 1){
            echo '<li><a href="order.php?page='.$first.$link.'">首页</a></li>';
            echo '<li><a href="order.php?page='.$prev.$link.'">&laquo;</a></li>';
        }else{
            echo '<li class="disabled"><a>首页</a></li>';
            echo '<li class="disabled"><a>&laquo;</a></li>';
        }
        for($i = 1; $i < $page; $i++) echo '<li><a href="order.php?page='.$i.$link.'">'.$i.'</a></li>';
        echo '<li class="disabled"><a>'.$page.'</a></li>';
        for($i = $page + 1; $i <= $pages; $i++) echo '<li><a href="order.php?page='.$i.$link.'">'.$i.'</a></li>';
        echo '';
        if($page < $pages){
            echo '<li><a href="order.php?page='.$next.$link.'">&raquo;</a></li>';
            echo '<li><a href="order.php?page='.$last.$link.'">尾页</a></li>';
        }else{
            echo '<li class="disabled"><a>&raquo;</a></li>';
            echo '<li class="disabled"><a>尾页</a></li>';
        }
        echo '</ul>';
        #分页
        ?>
    </div>
</div>