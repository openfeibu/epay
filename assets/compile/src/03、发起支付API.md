#### 发起支付请求

URL地址： api/submit.php

请将协议规则中的URL地址和本接口的请求地址连接起来，形成一个完整的URL请求地址。

| 名称 | 变量名 | 必填 | 类型 | 示例值 | 描述 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 商户ID | [pid](#userid) | 是 | string | 10003 |  |
| 支付方式 | [type](#type) | 是 | string | alipay2 | 详见下文的【支付方式】 |
| 商户订单号 | [out_trade_no](#out_trade_no) | 是 | string | 1530844815 | 该订单号在同步或异步地址中原样返回 |
| 异步通知地址 | [notify_url](#userid) | 是 | string | http://www.example.com/notify_url.php | 服务器异步通知地址 |
| 跳转通知地址 | [return_url](#userid) | 是 | string | http://www.example.com/notify_url.php | 页面跳转通知地址 |
| 商品名称 | [name](#userid) | 是 | string | VIP会员 |  |
| 附加数据 | [attach](#attach) | 否 | string(127) | 说明 | 附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据 |
| 商品金额 | [money](#userid) | 是 | string | 0.01 |  |
| 网站名称 | [sitename](#userid) | 否 | string | 衣库商城 |  |
| 返回格式 | [format](#format) | 否 | string | json | 返回格式，可选设置为json，使用该参数，将返回json数据。默认为直接跳转到支付平台网站。 |
| 签名字符串 | [sign](#userid) | 是 | string | 202cb962ac59075b964b07152d234b70 | 请查看【协议规则】中的【安全规范】 |
| 签名类型 | [sign_type](#userid) | 是 | string | MD5 | 默认为MD5，不参与签名 |

##### 支付方式

| type值 | 描述 |
|:---:|:---:|
| alipay2 | 支付宝 |
| wechat2 | 微信 |
| qqpay2 | QQ钱包，未开通 |
| alipay2qr | 和aliap2相同，但是只提供json数据 |
| wechat2qr | 和wechat2相同，但是只提供json数据 |


如果选择返回二维码json数据，商户需要根据该数据自行生成二维码。

##### 返回json数据如下：

msg	"获取成功"

payurl	"HTTPS://QR.ALIPAY.COM/FKX03651D6HLZ0TOQ8DVF4?t=1531737657054"

mark	"2018071618405128"

money	"0.01"

type	"alipay"

account	"135******45"



#### 支付结果通知

URL地址：您的回调地址，即发起支付请求的return_url（同步）、notify_url（异步）地址，异步回调时，如果网站处理成功，则需要输出'success'（不包含引号），服务器获取该值后，将不再发送异步通知。

同步回调（前台）：采用GET方法。

异步回调（后台）：采用POST方法。如果服务器收不到返回的“success”，服务器将会重复发送5次通知。

特别提醒：

1、请一定要核对返回的“支付状态（status）”，支付状态为"1"时，表示支付成功，为其他时，表示支付失败。

2、请一定要核对支付金额是否正确。


| 字段名 | 变量名 | 必填 | 类型 | 示例值 | 描述 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 返回状态码 | [code](#code) | 是 | string | 1 | 1为成功，其它值为失败 |
| 支付状态 | [status](#status) | 是 | string | 1 | 支付状态：'1'为支付成功，'error:错误信息'为未支付成功。 |
| 支付方式 | [type](#type) | 是 | string | alipay2 | 可选的参数是：alipay2（支付宝）、 wechat2（微信）。 |
| 金额 | [money](#money) | 是 | string | 0.01 |  |
| 订单号 | [trade_no](#trade_no) | 是 | string | O87f4NTor-Jm4nIMOJTL8yT9D9Sk57ZyD5rnlg_zjTs | 在支付系统中的订单号 |
| 商户订单号 | [out_trade_no](#out_trade_no) | 是 | string | 1530844815 | 该订单号在同步或异步地址中原样返回 |
| 时间 | [endtime](#endtime) | 是 | string | 2018-01-02 20:20:20 | 完成交易时间 |
| 版本号 | [version](#version) | 是 | string | 1 | 版本号，现在为1 |
| 商户ID | [pid](#pid) | 是 | string | 10003 |  |
| 签名 | [sign](#sign) | 是 | string | ecb317051cee7103df66b452daca099c | 请查看【协议规则】中的【安全规范】 |
| 签名类型 | [sign_type](#userid) | 是 | string | MD5 | 默认为MD5，不参与签名 |
| 附加数据 | [attach](#attach) | 否 | string | 说明 | 附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据 |
<!--## 用户信息接口详情-->

