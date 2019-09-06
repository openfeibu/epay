<?php
//不缓存
header('X-Accel-Buffering: no');
header('Cache-Control:no-cache,must-revalidate');
header('Pragma:no-cache');
header("Expires:0");
/**
 * 解析url中参数信息，返回参数数组
 */
function convertUrlQuery($query)
{
    $queryParts = explode('&', $query);
    $params = array();
    foreach ($queryParts as $param) {
        $item = explode('=', $param);
        $params[$item[0]] = $item[1];
    }
    return $params;
}
if(isset($_GET['trade_no'])) {
    header('Content-type:text/json');
    require_once __DIR__ . DIRECTORY_SEPARATOR . "../config_base.php";
    require_once __DIR__ . DIRECTORY_SEPARATOR . "../includes/api/init.php";
    //开始处理业务逻辑
    $order = \epay\order::find($_REQUEST['trade_no']);
    if ($order && $order['status'] != 1) { //业务逻辑更改，要针对状态9
        //查找通道描述
        $channel    = \epay\channel::find($order['mobile_url']);
        $note2 = json_decode($order['note2'], true);
        //开始分解数据
        $para=parse_url($note2['payurl']);
        $para_array=convertUrlQuery($para['query']);
        $para_array[body]=$channel[body];
        $para_array['amount']=$order['money']-$order['money2'];
        echo json_encode($para_array);
    }
}
?>