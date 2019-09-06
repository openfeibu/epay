## web部分说明：
1、所有的www.domain.com改成具体的网址。

2、添加2个定时任务。

    /api/cron.php  1分钟1次
    /api/cron_notify.php  1分钟1次
    
    如果每天定时更新密码，还需要加入如下定时任务。此任务发送邮件基于sendmail和smtp，服务器必须先安装好。
    /api/chpwd.php 1天1天

3、修改config/config_base.php中的配置信息。

4、修改config/config.php中的配置信息。

5、初始化程序请执行根目录下的test.php

6、在线充值：配置includes/epay/config.php中的配置信息。

#需要给777权限的目录如下：
upload/
config/cache
api/lock.txt
#此目录为记录通道变更信息，为安全并不放在网站根目录下，仍需给予777权限
../../logs

## 后台配置部分：

1、使用root账号，登录到管理员后台。

2、在“管理员管理”，添加一个管理员。

3、使用该管理员开商户。注意：请不要使用root账号直接开通商户。

### 要求版本PHP7.1，库如下：
apcu
curl
fileinfo
gd
iconv
json
libxml
mbstring
mcrypt
mysqli
openssl
pdo_mysql
posix
pcntl
session
tokenizer
zlib