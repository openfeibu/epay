<?php
/**
 * 工具箱
 **/
set_time_limit(0);
include("../includes/common.php");
if(!$_SESSION['is_admin']){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
$uuid = $conf['uuid'];
if(empty($uuid)){
    exit("UUID为空");
}
$title = '工具箱';
include './head.php';
$self_url = $_SERVER['PHP_SELF'];
require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/autoload.php";
//require_once __DIR__."/../includes/api/debug.php";
?>

  <!-- content -->
  <div id="content" class="app-content" role="main">
    <div class="app-content-body ">
        <div class="bg-light lter b-b wrapper-md">
            <h1 class="m-n font-thin h3"><?php echo $title;?></h1>
        </div>
        <div class="wrapper-md">


<?php
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : "";
switch($action){
    case 'qrcode':
        $data = $_REQUEST['data'];
        $data = urlencode($data);
        print <<< EOF
<img src="../api/qrcode.php?data={$data}">
EOF;

        break;
    case 'rsa':
        require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/autoload.php";
        require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/debug.php";
        $data = $_REQUEST['data'];
        $result = "
-----BEGIN RSA PRIVATE KEY-----
MIIEpQIBAAKCAQEA5r5xOZ5vL0icAekTILl6hC0EtViUL5gJYfvmig9E9XQtD+el
X+Pa/R2TFKNf/xMtxy5/bVX3N2+q+zLtNLheFm222vIsA24lEJ1ykjzErvamaidJ
6dVZtPGLAoGAHIPeHgt+bl4Ql5gUVmga4WU3bN9+aIsBEVb1JiheOjcksUvb4UMu
tHwvzlpW+oUrQkLlls3vzLEL8pT0Y+Z7LA/u7PADd8st8w7eP/Orkr4HXGl5iZlP
RNn7d+5elgsWF5/N8GDJwzDIzYX3itRwLfq37IcZcrCiMlTGWfffmDA=
-----END RSA PRIVATE KEY-----
";
        $result = "
{$data}
";

        $result = \epay\tool::delTargetLine2($result,"RSA PRIVATE KEY");
        $result = \epay\tool::delTargetLine2($result,"RSA PRIVATE KEY");
        $result = \epay\tool::deLineEnd($result);
        echo $result;
        break;
    case 'person_api':
        if(isset($_REQUEST['url']) && isset($_REQUEST['money']) && isset($_REQUEST['num']) && isset($_REQUEST['type'])){
            $url_request = $_REQUEST['url'];
            //判断网址是否以/结尾，如果否，则添加/
            if(strrchr($url_request,'/') != '/'){
                $url_request = $url_request."/";
            }
            $money = $_REQUEST['money'];
            $num = $_REQUEST['num'];
            $second = $_REQUEST['second'];
            $second = round($second,0);
            $type = $_REQUEST['type'];
        }else{
            echo "参数不完整。";
            exit();
        }
        require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/init2.php";
        $DB->exec("SET SESSION wait_timeout=1800");
        $money = round($money,2);
        $i = 0;
        if($num > 0){
            $mark = date("YmdHis").rand(0,9).rand(0,9).rand(0,9);
            $url = "{$url_request}getpay?money={$money}&mark={$mark}&type={$type}";
            $result = file_get_contents($url);
            $result = json_decode($result,true);
            if(!empty($result['payurl'])){
                $data = array(
                    "uuid" => $uuid,
                    "mark" => $mark,
                    "type" => $type,
                    "addtime" => date("Y-m-d H:i:s"),
                    "money" => $money,
                    "status" => 0,
                    "mobile_url" => $url_request,
                    "note2" => json_encode($result,320),
                );
                $re = $DB2->insert('pay_person',$data,true);
                echo $re;
                echo "<br>还剩：{$num}个";
            }else{
                echo "获取失败。";
                var_dump($url);
                var_dump($result);
            }

            //$i++;
            $_REQUEST['num']--;
            //sleep($second);
            $second = $second * 1000;
            $flash = http_build_query($_REQUEST);
            echo "<script>
setInterval(function() {
    location.href = '{$self_url}?{$flash}';
},{$second})
</script>";
        }else{
            echo "执行完毕";
        }
        break;
    case 'qrcodedecode':
        if(is_uploaded_file($_FILES['data'])){
            var_dump($_FILES['data']);
        }else{
            var_dump($_REQUEST);
            var_dump($_FILES);
        }
        break;
    default:
        require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/init.php";
        $data = $DB2->fetchRowMany("SELECT * FROM `pay_person` WHERE `status` = 0 OR `status` = 1; ");
        $data_list = "";
        foreach($data as $value){
            $data_list .= "<tr><td>{$value['id']}</td><td>{$value['trade_no']}</td><td>{$value['mark']}</td><td>{$value['mobile_url']}</td><td>{$value['type']}</td><td>{$value['money']}</td></tr>";
        }
        print <<< EOF
        <a href="" style="color: blue;">点击刷新</a>
<h2>RSA格式转化</h2>
<form action="" method="post">
<input type="hidden" name="action" value="rsa">
<textarea name="data" rows="3" cols="80"></textarea>
<input type="submit" value="提交">
</form>

<hr>
<h2>生成二维码</h2>
<form action="" method="post">
<input type="hidden" name="action" value="qrcode">
<textarea name="data" rows="2" cols="80"></textarea>
<input type="submit" value="提交">
</form>

<hr>
<h2>二维码转换</h2>
<form action="" method="post" enctype="multipart/form-data">
<input type="hidden" name="action" value="qrcodedecode">
<input type="file" name="data">
<input type="submit" value="提交">
</form>

<hr>
<h2>生成收款码</h2>
<form action="" method="get">
<input type="hidden" name="action" value="person_api">
隧道地址：<input type="text" name="url" value="">
金额（单位：元）：<input type="text" name="money" value="">
数量：<input type="text" name="num" value="">
类型：<select type="text" name="type">
<option value="alipay">支付宝</option>
<option value="wechat">微信</option>
</select>
生成速度(单位：秒)：<input type="text" name="second" value="10">
<input type="submit" value="提交">
</form>

<hr>
<h2>二维码列表</h2>
<div class="table-responsive">
  <table class="table table-striped">
    <thead><tr><th>ID</th><th>订单号</th><th>二维码号</th><th>隧道地址</th><th>类型</th><th>金额</th></tr></thead>
    <tbody>
    {$data_list}
    </tbody>
  </table>
</div>
EOF;
        break;
}
?>

        </div>
    </div>
  </div>
  <!-- /content -->
<?php include_once __DIR__."/foot.php" ?>