<?php

namespace epay;

class payhelper{
    public static function getSign_person($array,$key,$type = 'md5'){
        /*
         * 异步通知参数说明：
         * type:支付类型
         * no：订单号
         * money：金额
         * mark：备注
         * dt：时间
         * version：版本号（现在为1）
         * userids：商户ID(需要事先在平台定义好，并告诉商户，商户在收费助手的程序配置里面录入)
         * sign：签名
         * sign签名方法
         * dt+mark+money+no+type+signkey+userids+version这几个参数拼接然后md5，signkey是商户在收费助手的程序配置里面设置的的signkey，userids是商户在收费助手的程序配置里面设置的商户ID[需要事先在平台里面创建好]
         */
        $str = $array['dt'].$array['mark'].$array['money'].$array['no'].$array['type'].$key.$array['userids'].$array['version'];
        //var_dump($key);
        //var_dump($str);
        return md5($str);
    }

}
