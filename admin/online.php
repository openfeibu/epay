<?php
	/**
	 * 在线管理
	 **/
	include("../includes/common.php");
	if(!$_SESSION['is_admin']){
		exit("<script language='javascript'>window.location.href='./login.php';</script>");
	}
	$title = '在线管理';
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
					case 'delete':
						$sid = daddslashes($_REQUEST['sid']);
						$sql = "DELETE FROM `pay_online` WHERE `sid` = '{$sid}'";
						if ($DB->exec($sql)){
							showmsg("踢出用户成功！<br/><br/><a href='{$self_url}'>>>返回在线管理</a>", 1);
						}else{
							showmsg('踢出用户成功！' . $DB->errorCode(), 4);
						}
						break;
					default:
						if ($action == 'search') {
							$column = daddslashes($_REQUEST['column']);
							$value = daddslashes($_REQUEST['value']);
							$sql = " `{$column}` like '%{$value}%' ";
							$numrows = $DB->query("SELECT * from pay_online WHERE{$sql}")->rowCount();
							$con = '包含 ' . $_REQUEST['value'] . ' 的共有 <b>' . $numrows . '</b> 个在线用户';
						} else {
							$numrows = $DB->query("SELECT * from pay_online WHERE 1")->rowCount();
							$sql = " 1";
							$con = "共有 <b>{$numrows}</b> 个在线用户";
						}
						print <<< EOF_DEFAULT
    <div class="bg-light lter b-b wrapper-md">
        <h1 class="m-n font-thin h3">在线管理</h1>
    </div>
    <div class="wrapper-md" ng-controller="FormDemoCtrl">
    <div class="panel panel-default">
    <div class="panel-heading font-bold">
        在线列表
    </div>

<form action="{$self_url}" method="GET" class="form-inline">
<input type="hidden" name="action" value="search">
  <div class="form-group">
	<select name="column" class="form-control"><option value="username">用户名</option></select>
  </div>
  <div class="form-group">
    <input type="text" class="form-control" name="value" placeholder="搜索用户名" value="{$_REQUEST['value']}">
  </div>
  <button type="submit" class="btn btn-primary">搜索</button>
</form>

{$con}

<div class="table-responsive">
  <table class="table table-striped">
    <thead><tr><th>SID</th><th>用户名</th><th>IP地址</th><th>城市</th><th>过期时间</th><th>操作</th></tr></thead>
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

						$rs = $DB->query("SELECT sid,username,ip,FROM_UNIXTIME(exptime) as ex FROM `pay_online` WHERE{$sql} order by sid desc limit $offset,$pagesize");
						while ($res = $rs->fetch()) {
							echo "<tr><td><b>{$res['sid']}</b></td><td>{$res['username']}</td><td>{$res['ip']}</td>";
							echo "<td>".get_ip_city($res['ip'])."</td>";
							if($sid  == $res['sid']){
                                echo "<td>{$res['ex']}</td><td>本机登录，不能踢出</td></tr>";
                            }
							else{
                                echo "<td>{$res['ex']}</td><td><a href=\"{$self_url}?action=delete&sid={$res['sid']}\" class=\"btn btn-xs btn-info\">踢出</a></td></tr>";
                            }
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