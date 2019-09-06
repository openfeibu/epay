<?php
/**
 * 登录日志
 **/
include("../includes/common.php");
if(!$_SESSION['is_admin']){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
$title = '登录日志';
include './head.php';
$self_url = $_SERVER['PHP_SELF'];
//require_once __DIR__."/../includes/api/debug.php";
?>

    <!-- content -->
    <div id="content" class="app-content" role="main">
        <div class="app-content-body ">

            <?php
            isset($_REQUEST['action']) ? $action = daddslashes($_REQUEST['action']) : $action = "";
            switch($action){
                default:
                    if ($action == 'search') {
                        $column = daddslashes($_REQUEST['column']);
                        $value = daddslashes($_REQUEST['value']);
                        if($value=="1"){
                            $sql = " `uid`='-111'";
                        }
                        else{
                            $sql = " `{$column}` = '{$value}' ";
                        }
                        $log_sql = "SELECT * from panel_log WHERE{$sql}";
                        $log_sql1 = "SELECT count(*) as a from panel_log WHERE{$sql}";
                        $re = $DB->query($log_sql1)->fetch();
                        $numrows = $re["a"];
                        $con = '包含 ' . $_REQUEST['value'] . ' 的共有 <b>' . $numrows . '</b> 条登录日志';
                    } else {
                        $log_sql = "SELECT * from panel_log WHERE `uid`='0'";
                        $log_sql1 = "SELECT count(*) as a from panel_log WHERE `uid`='0'";
                        $re = $DB->query($log_sql1)->fetch();
                        $numrows = $re["a"];
                        $con = "共有 <b>{$numrows}</b> 条登录日志";
                    }
                    $link = $_REQUEST;
                    unset($link['page']);
                    $link = http_build_query($link);
                    $link = "&".$link;
                    print <<< EOF_DEFAULT
    <div class="bg-light lter b-b wrapper-md">
        <h1 class="m-n font-thin h3">隧道列表</h1>
    </div>
    <div class="wrapper-md" ng-controller="FormDemoCtrl">
    <div class="panel panel-default">
    <div class="panel-heading font-bold">
        隧道列表
    </div>

<form action="{$self_url}" method="GET" class="form-inline" style="margin-top: 20px;">
<input type="hidden" name="action" value="search">
  <div class="form-group">
    <label>搜索</label>
	<select name="column" class="form-control"><option value="uid">商户号</option></select>
  </div>
  <div class="form-group">
    <input type="text" class="form-control" name="value" placeholder="搜索内容">
  </div>
  <button type="submit" class="btn btn-primary">搜索</button>&nbsp;
</form>

{$con}

<div class="table-responsive">
  <table class="table table-striped">
    <thead><tr><th>编号</th><th>商户ID</th><th>IP地址</th><th>城市</th><th>登录状态</th><th>登录时间</th></tr></thead>
    <tbody>
EOF_DEFAULT;

                    $pagesize = 30;
                    $pages = intval($numrows / $pagesize);
                    if ($numrows % $pagesize) {
                        $pages++;
                    }
                    if (isset($_REQUEST['page'])) {
                        $page = intval($_REQUEST['page']);
                    } else {
                        $page = 1;
                    }
                    $offset = $pagesize * ($page - 1);

                    $rs = $DB->query("$log_sql order by id desc limit $offset,$pagesize");
                    while ($res = $rs->fetch()) {
                        $offset++;
                        $data_log = explode('{"',$res["data"]);
                        $json = json_decode('{"'.$data_log[1],true);
                        $login_type = "";
                        $error_msg = "";
                        if(isset($json["status"])){
                            if($json["status"]){
                                $login_type = "登录成功";
                            }
                            else{
                                $error_msg = $json["error_msg"];
                                $login_type = "登录失败";
                            }
                        }
                        echo "<tr><td>{$offset}</td></td><td><b>{$res['uid']}</b></td><td>{$data_log[0]}</td><td>{$res['city']}</td><td>{$login_type}</td><td>{$res['date']}</td></tr>";
                    }
                    echo "
    </tbody>
  </table>
</div>
";
                    require '../includes/page.class.php';
#分页
                    echo "</div>";
                    break;
            }
            ?>


        </div>
    </div>
    <!-- /content -->
<?php include_once __DIR__."/foot.php" ?>