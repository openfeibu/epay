<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . "../config/config.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . "../config_base.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . "../includes/api/init.php";
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
function http_post_data($url, $data_string, $header="") {

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    if(empty($header)){
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($data_string))
        );
    }else{
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8',
                $header,
                'Content-Length: ' . strlen($data_string))
        );
    }
    ob_start();
    curl_exec($ch);
    $return_content = ob_get_contents();
    //echo $return_content."<br>";
    ob_end_clean();

    $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //  return array($return_code, $return_content);
    return  $return_content;
}

if (isset($_GET['pdd_no']) && isset($_GET['trade_no'])) {
    //拿到金额和订单后，就可以自动更新订单
    $order = \epay\order::find($_GET['trade_no']);
    if ($order && $order['status'] != 1) { //业务逻辑更改，要针对状态9
        $channel    = \epay\channel::find($order['mobile_url']);
        $priv=convertUrlQuery($channel['private_key']);
        $pubic=convertUrlQuery($channel['public_key']);
        //1.拼多多登陆
        //$data=$cache->get('access_token_'.$channel['id']);
        //if (empty($cache) || empty($data)){
        $postjosn='{"app_id":12,"access_token":"'.$priv['access_token'].'","open_id":"'.$priv['open_id'].'"}';
        $re=http_post_data("https://api.yangkeduo.com/login",$postjosn);
        $data=json_decode($re,true);
        //将接受到的结果保存到缓存
        //$cache->put('access_token_'.$channel['id'], $data);
        //}
        //2.去拚多多查单状态
        $url="https://api.yangkeduo.com/order/".$_GET['pdd_no'];
        $postjosn='';
        $re=http_post_data($url,$postjosn,"AccessToken:".$data['access_token']);
        $result=json_decode($re,true);
        if ($result[pay_status]=="2" && $order['buyer']==$_GET['pdd_no']) {
            $now = date("Y-m-d H:i:s");
            $sql = "update `pay_order` set `status` ='1',`endtime` ='{$now}', `buyer` = '{$_GET['pdd_no']}@pdd' where `trade_no` = '{$_GET['trade_no']}';";
            $DB->query($sql);
            echo "<script>alert('充值成功');</script>";
        } else {
            //不相等直接报错
            echo "<script>alert('请不要乱提交参数！');</script>";
        }
    }
}
?>
