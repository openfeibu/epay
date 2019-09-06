<?php
/* *
 * 配置文件
 */
 
 
//↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
//商户ID
$alipay_config['partner']		= '';

//商户KEY
$alipay_config['key']			= '';


//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑


//签名方式 不需修改
$alipay_config['sign_type']    = strtoupper('MD5');

//字符编码格式 目前支持 gbk 或 utf-8
$alipay_config['input_charset']= strtolower('utf-8');

//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
$alipay_config['transport']    = 'http';

//支付API地址
$alipay_config['apiurl']    = 'http://43.249.29.218/cspay/api/v1/transRequest';

//提现API地址
$alipay_config['outmoneyapiurl']    = 'http://43.249.29.218/cspay/api/v1/dftransRequest';

//提现是否成功交易查询API地址
$alipay_config['outmoneystatusapiurl']    = 'http://43.249.29.218/cspay/api/v1/dftradeQuery';


//提现是否成功交易查询API地址
$alipay_config['balanceRequesturl']    = 'http://43.249.29.218/cspay/api/v1/balanceRequest';


?>