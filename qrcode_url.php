<?php
exit();
/**
 * 从本地获取二维码软件版专用。(默认为显示云端上传的二维码)
 * 注意：
 * 如果你提交的订单为100元 但展示的是1元的二维码 那么订单会不存在 不下发通知
 * 如使用的是自定义金额的收款码用户未按金额约定支付 都会是订单不存在。
 * Date: 2017/2/14
 * Time: 21:51
 */

$money = number_format((float)$_GET['money'],2,'.',''); //金额统一保留2位小数
$type = $_GET['type'];
if(!$type) $type = 'alipay';
if($money <= 0){//这是什么状况 金额都没有。展示no.png
    header('Location: qrcode/no.png');
    exit(0);
}

/**
 * 根据参数转为二维码文件名 (我们只给一个参考 具体根据个人实际开发)
 * @param $money 金额
 * @param int $type 支付类型
 * @param int $act 二维码规则方式
 * @return string  返回二维码路径
 *
 *act参数为1则格式为：qr/支付方式/金额整数部分/金额小数部分.png 支付宝则为：qr/支付方式/金额整数部分/金额小数部分_备注.png
 *比如：100元 小数部分则是00 100元微信QQ路径为：qr/3/100/00.png  100元支付为：qr/3/100/00_0.png
 */
function moneyToFileName($money,$type = 'alipay'){
    $fileName = "qrcode/{$type}/{$money}.png";
    return $fileName;
}


$qrcode_filename = moneyToFileName($money,$type,$tag,0); //根据参数生成默认金额二维码地址
if(!file_exists($qrcode_filename)){ //该金额二维码不存在 亲。
    //检查你是否有默认收款码 有则使用,没有那别人根本无法付款
    $index_fileName = "qrcode/{$type}/index.png";
    $qrcode_filename = file_exists($index_fileName) ? $index_fileName : 'qrcode/img/no.png';
}
header('Location: '.$qrcode_filename); //跳转到二维码真实地址