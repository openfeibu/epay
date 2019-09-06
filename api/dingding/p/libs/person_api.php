<?php
namespace epay;

use Punic\Exception;

class person_api{

    public static function get_mobile_url($trade_no){
        global $DB;
        //查询订单中是否有隧道，有则直接输出
        $sql = "SELECT * FROM `pay_order` WHERE `trade_no` = '{$trade_no}'; ";
        $pay_order = $DB->query($sql)->fetch();
        $mobile_url = $pay_order['mobile_url'];
        if($mobile_url != null && $mobile_url != '' && $mobile_url != '0'){
            return $mobile_url;
        }

        //查找隧道地址
        $sql = "SELECT * FROM `pay_user_others` WHERE `id` = '{$pay_order['pid']}' LIMIT 1;";
        $result = $DB->query($sql);
        if(!$result){
            exit("找不到通道地址。");
        }
        $row = $result->fetch();
        if(!$row){
            exit("查不到通道地址，请联系管理员。");
        }

        //订单中隧道地址不存在，轮询方式获取隧道。
        $type = $pay_order['type'];
        switch($type){
            case 'alipay2':
            case 'alipay2qr':
            case 'alipay2_url':
                $mobile_url = $row['mobile_alipay_url'];
                $pointer_type = "alipay_pointer";
                $type_topay = "alipay";
                break;
            case 'wechat2':
            case 'wechat2qr':
            case 'wechat2_url':
                $mobile_url = $row['mobile_wxpay_url'];
                $pointer_type = "wxpay_pointer";
                $type_topay = "wechat";
                break;
            case 'qqpay2':
            case 'qqpay2qr':
            case 'qqpay2_url':
                $mobile_url = $row['mobile_qqpay_url'];
                $pointer_type = "qqpay_pointer";
                $type_topay = "qq";
                break;
            default:
                $mobile_url = $row['mobile_url'];
                $pointer_type = "pointer";
                $type_topay = "alipay";
                break;
        }

        if($mobile_url == ''){
            $mobile_url = $row['mobile_url'];
            $pointer_type = "pointer";
        }

        //判断轮询方式，查找隧道地址，默认依次轮询。
        if(true){
            //查找指针
            $pointer = $row[$pointer_type];
            $mobile_url = lunxun_yc($mobile_url,$pointer,$trade_no,$type_topay);

            //更新指针
            if($mobile_url['code'] == 1){
                $pointer = intval($mobile_url['msg']);
                $sql = "UPDATE `pay_user_others` SET `{$pointer_type}` = '{$pointer}' WHERE `id` = '{$pay_order['pid']}' LIMIT 1; ";
                $DB->query($sql);
            }
        }else{
            $mobile_url = lunxun_sj($mobile_url);
        }

        //判断通道是否有效
        if($mobile_url['code'] != 1){
            echo $mobile_url['msg'];
            exit();
        }

        $mobile_url = $mobile_url['url'];
        //判断网址是否以/结尾，如果否，则添加/
        if(strrchr($mobile_url,'/') != '/'){
            $mobile_url = $mobile_url."/";
        }

        //记录该通道到订单记录
        $sql = "UPDATE `pay_order` SET `mobile_url` = '{$mobile_url}' WHERE `trade_no` = '{$trade_no}' ";
        $DB->query($sql);
        return $mobile_url;
    }

    public static function get_qrcode($mobile_url,$money,$trade_no,$type){
        global $DB;
        //查询订单中是否有二维码，有则直接输出
        $sql = "SELECT * FROM `pay_order` WHERE `trade_no` = :trade_no; ";
        $re = $DB->prepare($sql);
        $re->execute([":trade_no" => $trade_no]);
        $pay_order = $re->fetch(\PDO::FETCH_ASSOC);
        $note2 = $pay_order['note2'];
        $note2 = json_decode($note2,true);
        if(isset($note2['msg']) && $note2['msg'] == '获取成功'){
            $data = $note2;
            $data['code'] = 1;
            return $data;
        }else{
            unset($note2);
        }

        //去掉空格
        $url = str_replace(' ','',$mobile_url);
        $url = str_replace('　','',$url);
        $url = $url."getpay?money={$money}&mark={$trade_no}&type={$type}";
        $data = file_get_contents($url);
        $data = json_decode($data,true);
        if($data['msg'] == "获取成功"){
            //录入数据库
            $sql = "UPDATE `pay_order` SET `note2` = :note2 WHERE `trade_no` = :trade_no";
            $note2 = json_encode($data,320);
            $data['code'] = 1;
            try{
                $re = $DB->prepare($sql);
                $re->execute(array(":note2" => $note2,":trade_no" => $trade_no));
            }catch(Exception $e){
                //echo $e->getCode();
                $data['code'] = 0;
                $data['msg'] = $e->getCode().$e->getMessage();
            }
        }else{
            $data['code'] = 0;
        }
        return $data;
    }
    public static function get_qrcode2($money,$trade_no,$type,$uuid = ''){
        global $DB;
        global $DB2;
        //查询订单中是否有二维码，有则直接输出
        $sql = "SELECT * FROM `pay_order` WHERE `trade_no` = :trade_no; ";
        $re = $DB->prepare($sql);
        $re->execute([":trade_no" => $trade_no]);
        $pay_order = $re->fetch(\PDO::FETCH_ASSOC);
        $note2 = $pay_order['note2'];
        $note2 = json_decode($note2,true);
        if(isset($note2['msg']) && $note2['msg'] == '获取成功'){
            $data = $note2;
            $data['code'] = 1;
            return $data;
        }else{
            unset($note2);
        }

        //获取二维码
        $conds = ["money" => $money,"type" => $type];
        if($uuid != ''){
            $sql = "SELECT * FROM `pay_person` WHERE `uuid` = :uuid AND `money`= :money AND `type` = :type AND `status` = 0 LIMIT 1";
        }else{
            $sql = "SELECT * FROM `pay_person` WHERE `money`= :money AND `type` = :type AND `status` = 0 LIMIT 1";
        }
        $result = $DB2->fetchRow($sql,$conds);
        $data = json_decode($result['note2'],true);
        if($data['msg'] == "获取成功"){
            //录入数据库
            $note2 = json_encode($data,320);
            try{
                $DB2->update('pay_order',["trade_no" => $trade_no],["note2" => $note2,"trade_no" => $trade_no,"mobile_url" => $result['mobile_url']]);
            }catch(Exception $e){
                $data['code'] = 0;
                $data['msg'] = $e->getCode().$e->getMessage();
            }
        }else{
            $data['code'] = 0;
        }
        $DB2->update('pay_person',['mark' => $result['mark']],["trade_no" => $trade_no,"status" => 1]);
        return $data;
    }
    public static function get_qrcode3($appid,$mobile_url,$payurl,$money,$trade_no,$type,$method = "money2",$uuid = ''){
        global $DB;
        global $DB2;
        //查询订单中是否有二维码，有则直接输出
        $sql = "SELECT * FROM `pay_order` WHERE `trade_no` = :trade_no; ";
        $re = $DB->prepare($sql);
        $re->execute([":trade_no" => $trade_no]);
        $pay_order = $re->fetch(\PDO::FETCH_ASSOC);
        $note2 = $pay_order['note2'];
        $note2 = json_decode($note2,true);
        if(isset($note2['msg']) && $note2['msg'] == '获取成功'){
            $data = $note2;
            $data['code'] = 1;
            return $data;
        }else{
            unset($note2);
        }

        //获取二维码
        $money = round($money,2);
        $note2 = '{"code":1,"msg":"获取成功","payurl":"'.$payurl.'","mark":"'.$trade_no.'","money":"'.$money.'","type":"'.$type.'","account":"'.$mobile_url.'","method":"'.$method.'"}';
        $data = json_decode($note2,true);
        if($data['msg'] == "获取成功"){
            //录入数据库
            $note2 = json_encode($data,320);
            try{
                $DB2->update('pay_order',["trade_no" => $trade_no],["note2" => $note2,"trade_no" => $trade_no,"appid" => $appid,"money2" => $money]);
            }catch(Exception $e){
                $data['code'] = 0;
                $data['msg'] = $e->getCode().$e->getMessage();
            }
        }else{
            $data['code'] = 0;
        }
        return $data;
    }
    public static function get_qrcode4($appid,$mobile_url,$payurl,$money,$money2,$trade_no,$type,$method = "code",$uuid = ''){
        global $DB;
        global $DB2;
        //查询订单中是否有二维码，有则直接输出
        $sql = "SELECT * FROM `pay_order` WHERE `trade_no` = :trade_no; ";
        $re = $DB->prepare($sql);
        $re->execute([":trade_no" => $trade_no]);
        $pay_order = $re->fetch(\PDO::FETCH_ASSOC);
        $note2 = $pay_order['note2'];
        $note2 = json_decode($note2,true);
        if(isset($note2['msg']) && $note2['msg'] == '获取成功'){
            $data = $note2;
            $data['code'] = 1;
            return $data;
        }else{
            unset($note2);
        }

        //获取二维码
        $money = round($money,2);
        $note2 = '{"code":1,"msg":"获取成功","payurl":"'.$payurl.'","mark":"'.$trade_no.'","money":"'.$money.'","type":"'.$type.'","account":"'.$mobile_url.'","method":"'.$method.'"}';
        var_dump($note2);
        $data = json_decode($note2,true);
        var_dump($data);
        if($data['msg'] == "获取成功"){
            //录入数据库
            $note2 = json_encode($data,320);
            try{
                $DB2->update('pay_order',["trade_no" => $trade_no],["note2" => $note2,"trade_no" => $trade_no,"appid" => $appid,"money2" => $money2]);
            }catch(Exception $e){
                $data['code'] = 0;
                $data['msg'] = $e->getCode().$e->getMessage();
            }
        }else{
            $data['code'] = 0;
        }
        return $data;
    }
    public static function get_qrcode5($appid,$mobile_url,$payurl,$money,$money2,$trade_no,$type,$method = "code",$uuid = ''){
        global $DB;
        global $DB2;
        //查询订单中是否有二维码，有则直接输出
        $sql = "SELECT * FROM `pay_order` WHERE `trade_no` = :trade_no; ";
        $re = $DB->prepare($sql);
        $re->execute([":trade_no" => $trade_no]);
        $pay_order = $re->fetch(\PDO::FETCH_ASSOC);
        $note2 = $pay_order['note2'];
        $note2 = json_decode($note2,true);
        if(isset($note2['msg']) && $note2['msg'] == '获取成功'){
            $data = $note2;
            $data['code'] = 1;
            return $data;
        }else{
            unset($note2);
        }

        //获取二维码
        $money = round($money,2);
        $note2 = '{"code":1,"msg":"获取成功","payurl":"'.$payurl.'","mark":"'.$trade_no.'","money":"'.$money.'","type":"'.$type.'","account":"'.$mobile_url.'","method":"'.$method.'"}';
        //var_dump($note2);
        $data = json_decode($note2,true);
        //var_dump($data);
        if($data['msg'] == "获取成功"){
            //录入数据库
            $note2 = json_encode($data,320);
            try{
                $DB2->update('pay_order',["trade_no" => $trade_no],["note2" => $note2,"trade_no" => $trade_no,"appid" => $appid,"money2" => $money2]);
            }catch(Exception $e){
                $data['code'] = 0;
                $data['msg'] = $e->getCode().$e->getMessage();
            }
        }else{
            $data['code'] = 0;
        }
        return $data;
    }
    public static function getpay($money,$mark,$type){
        //去掉空格
        $url = str_replace(' ','',REQUEST_URL);
        $url = str_replace('　','',$url);
        // $url = REQUEST_URL.'getpay?money='.$money.'&mark='.$mark.'&type='.$type;
        $url = $url.'getpay?money='.$money.'&mark='.$mark.'&type='.$type;
        echo $url;
        return;
        $data = getHtml($url,'');
        $de_json = json_decode($data);
        $msg = $de_json->msg;
        if($msg == '获取成功'){
            $payurl = $de_json->payurl;
            $mark = $de_json->mark;
            $money = $de_json->money;
            $type = $de_json->type;
            if($type == "wechat"){
                $type = '1';
            }elseif($type == 'alipay'){
                $type = '2';
            }elseif($type == 'qq'){
                $type = '3';
            }
            gotoPay($money,$payurl,$mark,$type);
        }else{
            echo $msg;
        }
    }

    public static function gotoPay($money,$pay_url,$trade_no,$type){
        global $userid,$return_url,$notify_url;

        echo "<form style='display:none;' id='form1' name='form1' method='post' action='pay.php'>
			  <input name='userid' type='text' value='{$userid}' />
			  <input name='money' type='text' value='{$money}' />
			  <input name='pay_url' type='text' value='{$pay_url}'/>
			  <input name='redirect_url' type='text' value='{$return_url}'/>
			  <input name='return_url' type='text' value='{$return_url}'/>
			  <input name='notify_url' type='text' value='{$notify_url}'>
			  <input name='trade_no' type='text' value='{$trade_no}'/>
			  <input name='type' type='text' value='{$type}'/>
			</form>
			<script type='text/javascript'>function load_submit(){document.form1.submit()}load_submit();</script>";
    }

    public static function getHtml($url,$data = ''){
        $ch = curl_init($url);
        $header[] = 'Mozilla/5.0 (Linux; U; Android 7.1.2; zh-cn; GiONEE F100 Build/N2G47E) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30';
        if(!empty($data)){
            curl_setopt($ch,47,1);
            curl_setopt($ch,10015,$data);
        }
        curl_setopt($ch,10023,$header);
        curl_setopt($ch,64,FALSE); // 对认证证书来源的检查
        curl_setopt($ch,81,FALSE); // 从证书中检查SSL加密算法是否存在
        curl_setopt($ch,19913,true);
        curl_setopt($ch,19914,true);
        curl_setopt($ch,52,1);
        curl_setopt($ch,13,60);

        ob_start();
        @$data = curl_exec($ch);
        ob_end_clean();
        curl_close($ch);
        return $data;
    }
}