<?php
//if($_REQUEST("tk") != "7dsfsdf55"){
//    exit();
//}
require_once __DIR__.DIRECTORY_SEPARATOR."../../includes/api/init.php";
require_once('simple_html_dom.php');
// 新建一个Dom实例
$html = new simple_html_dom();
 
$sql = "SELECT `money`,`trade_no`,`note2`,`order_ten` FROM `pay_order` WHERE `type`='yinshengpay' AND `admin_status` = '1' AND `status`='0' AND `order_ten`<'5' LIMIT 100;";
$row1 = $DB->query($sql)->fetchAll();
foreach ($row1 as $row){
    if(isset($row["money"])){
        $json = json_decode($row["note2"],true);
        $trade_no = urldecode($json["yinshengpay"]);
        $ysback = get_yspay($trade_no);
        if(isset($ysback["money"])){
            if($row["money"] == $ysback["money"]){
                $datetime = date("Y-m-d H:i:s");
                $buyer = $ysback["type"]."@".$ysback["money"]."@".$ysback["order_no"]."@".$ysback["time"]."@now:".$datetime;
                $sql1 = "UPDATE `pay_order` SET `status`='1',`endtime`='{$datetime}',`buyer`='{$buyer}' WHERE `trade_no`='{$row["trade_no"]}'";
                echo $sql1."<br>";
                $DB->query($sql1);
                //删除已经完成的临时订单
                $sql4 = "DELETE FROM `pay_yspay` WHERE `trade_no`='{$row["trade_no"]}'  AND `money`='{$row["money"]}' AND `admin_status`='1'";
                $row = $pdo->query($sql4);
            }
            else{
                $order_ten = $row["order_ten"]+1;
                $sql3 = "UPDATE `pay_order` SET `order_ten`='{$order_ten}' WHERE `trade_no`='{$row["trade_no"]}'";
                echo $sql3."<br>";
                $DB->query($sql3);
            }
        }
        else{
            $order_ten = $row["order_ten"]+1;
            $sql2 = "UPDATE `pay_order` SET `order_ten`='{$order_ten}' WHERE `trade_no`='{$row["trade_no"]}'";
            echo $sql12."<br>";
            $DB->query($sql2);
        }
    }
}

//$trade_no = "https://qr.95516.com/00010000/01172335166919003153693463416239";
//echo json_encode(a($trade_no),JSON_UNESCAPED_UNICODE);

function get_yspay($trade_no){
    global $html;
    $ch = curl_init();
    $curlurl = "https://unipayqrcode.ysepay.com/yspos_weixgate/scanCodePay.do?c=unionpay&qrCode=".$trade_no;
    $referurl = "http://www.amztool.cn";
    $ip=mt_rand(11, 191).".".mt_rand(0, 240).".".mt_rand(1, 240).".".mt_rand(1, 240);   //随机ip
    $useragent="Mozilla/5.0 (Linux; Android 6.0; 1503-M02 Build/MRA58K) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/37.0.0.0 Mobile MQQBrowser/6.2 TBS/036558 Safari/537.36 MicroMessenger/6.3.25.861 NetType/WIFI Language/zh_CN";  //随机浏览器useragent
    $header = array(
        'CLIENT-IP:'.$ip,
        'X-FORWARDED-FOR:'.$ip,
    );    //构造ip
    curl_setopt($ch, CURLOPT_URL, $curlurl); //要抓取的网址
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch, CURLOPT_REFERER, $referurl);  //模拟来源网址
    curl_setopt($ch, CURLOPT_USERAGENT, $useragent); //模拟常用浏览器的useragent

    $page_content = curl_exec($ch);
    curl_close($ch);
//echo $page_content;

//从字符串中加载信息
    $html->load($page_content);

//echo $html;

    $type = $html->find('div[id=J_transTyp]',0)->innertext;
    $money = $html->find('span[id=J_amountStr]',0)->innertext;
    $time = $html->find('div[id=J_txnTm]',0)->innertext;
    $order_no = $html->find('div[id=J_logNo]',0)->innertext;
    $back = ["type"=>$type,"money"=>$money,"time"=>$time,"order_no"=>$order_no];
//var_dump($back);
//echo "<br/><br/><br/>";
//echo $type;
//echo "<br/><br/><br/>";
//echo $money;
//echo "<br/><br/><br/>";
//echo $order_no;
//echo "<br/><br/><br/>";
//echo $time;
    return $back;
}


?>