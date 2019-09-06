<?php
exit();
require './includes/common.php';
require_once(SYSTEM_ROOT."cspay/epay.config.php");
require_once(SYSTEM_ROOT."cspay/epay_submit.class.php");

/**************************请求参数**************************/

$list = $DB->query("SELECT * FROM bc_outmoney WHERE out_status=0")->fetchAll();

foreach($list as $res){

    //构造要请求的参数数组，无需改动
    $parameter = array(
        "mch_id"       => trim($alipay_config['partner']),
        "out_trade_no" => $res['out_id'],
    );

    //建立请求
    $alipaySubmit = new AlipaySubmit($alipay_config);
    $html_text = $alipaySubmit->buildRequestOutMoneyStatusCurl($parameter);
    //echo $html_text;
    print_r($html_text);


    if($html_text["ret_code"] == "SUCCESS"){
        if($html_text["payStatus"] == 2){
            echo "转账失败<br/>";
            $DB->query("update pay_user set already4paymoney=already4paymoney-{$res['out_money']} where id={$res['User_id']}");
            $DB->query("update bc_outmoney set out_status=2 where out_id={$res['out_id']}");
            echo "update pay_user set already4paymoney=already4paymoney-{$res['out_money']} where id={$res['User_id']}";
        }

        if($html_text["payStatus"] == 1){
            echo "转账处理中";
            //减少金额并插入bc_outmoney表轮寻处理结果

        }


        if($html_text["payStatus"] == 3){
            echo "状态从处理中到转账成功，则改状态即可";
            $DB->query("update bc_outmoney set out_status=1 where out_id={$res['out_id']}");
            //减少金额
        }
    }else{

        echo "转账失败,服务器返回错误信息";


    }


}


//echo "QR is ".$html_text["payinfo"];	
?>
