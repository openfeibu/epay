#### 查询单个订单

请求地址：api/api.php

请将协议规则中的URL地址和本接口的请求地址连接起来，形成一个完整的URL请求地址。

| 字段名 | 变量名 | 必填 | 类型 | 示例值 | 描述 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 操作类型 | [act](#act) | 是 | string | order | 此API固定值 |
| 商户ID | [pid](#userid) | 是 | string | 100 |  |
| 商户订单号 | [out_trade_no](#out_trade_no) | 是 | string | 20160806151343349 |  |
| 签名 | [sign](#sign) | 否 | string | ec43440dcf72f98d8cab1f5fadb09dfd | 请查看【协议规则】中的【安全规范】，此参数可选，如果有签名，则需要验证签名 |

返回结果：

| 字段名 | 变量名 | 必填 | 类型 | 示例值 | 描述 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 返回状态码 | [code](#code) | 是 | string | 1 | 1为成功，其它值为失败 |
| 支付状态 | [status](#status) | 是 | string | 1 | 支付状态：'1'为支付成功，其他为未支付成功。 |
| 返回信息 | [msg](#msg) | 是 | string | 支付成功 | 查询结果说明，"支付成功"为付款成功，其他为未成功。 |
| 订单号 | [trade_no](#trade_no) | 是 | string | 2016080622555342651 | 支付平台订单号 |
| 商户订单号 | [out_trade_no](#out_trade_no) | 是 | string | 20160806151343349 | 商户系统内部的订单号 |
| 支付方式 | [type](#type) | 是 | string | alipay2 | alipay2:支付宝,wechat2:微信等 |
| 商户ID | [pid](#pid) | 是 | string | 10003 | 发起支付的商户ID |
| 创建订单时间 | [addtime](#addtime) | 是 | string | 2016-08-06 22:55:52 |  |
| 完成交易时间 | [endtime](#endtime) | 是 | string | 2016-08-06 22:56:52 |  |
| 商品名称 | [name](#name) | 是 | string | VIP会员 |  |
| 商品金额 | [money](#money) | 是 | string | 1.00 |  |
| 当前时间 | [now](#now) | 是 | string | 2016-08-06 22:57:52 | 服务器当前时间 |
| 签名 | [sign](#sign) | 是 | string | ec43440dcf72f98d8cab1f5fadb09dfd | 请查看【协议规则】中的【安全规范】 |
| 附加数据 | [attach](#attach) | 否 | string | 说明 | 附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据 |

#### 查询批量订单
无
