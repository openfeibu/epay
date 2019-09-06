<?php
set_time_limit(0);
require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/init.php";
//require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/debug.php";
$sql1 = "
-- 修改数据库默认引擎，以支持事务。
ALTER TABLE `bc_outmoney` engine=InnoDB;
ALTER TABLE `bc_tradeday` engine=InnoDB;
ALTER TABLE `ims_ewei_shop_article` engine=InnoDB;
ALTER TABLE `panel_log` engine=InnoDB;
ALTER TABLE `panel_user` engine=InnoDB;
ALTER TABLE `pay_admin` engine=InnoDB;
ALTER TABLE `pay_alipayinfo` engine=InnoDB;
ALTER TABLE `pay_alipayinfo` engine=InnoDB;
ALTER TABLE `pay_apply` engine=InnoDB;
ALTER TABLE `pay_balance` engine=InnoDB;
ALTER TABLE `pay_balance_history` engine=InnoDB;
ALTER TABLE `pay_channel` engine=InnoDB;
ALTER TABLE `pay_order` engine=MyISAM;
ALTER TABLE `pay_recharge` engine=InnoDB;
ALTER TABLE `pay_recharge_history` engine=InnoDB;
ALTER TABLE `pay_recharge_record` engine=InnoDB;
ALTER TABLE `pay_settle` engine=InnoDB;
ALTER TABLE `pay_smslog` engine=InnoDB;
ALTER TABLE `pay_transfer` engine=InnoDB;
ALTER TABLE `pay_user` engine=InnoDB;
ALTER TABLE `pay_user_others` engine=InnoDB;
ALTER TABLE `token_wx` engine=InnoDB;
ALTER TABLE `tp_diymen_set` engine=InnoDB;

ALTER TABLE `pay_user_others` DROP `googleauth`;
ALTER TABLE `pay_user` ADD `googleauth` VARCHAR(512) NOT NULL AFTER `key`;
ALTER TABLE `pay_admin` ADD `googleauth` VARCHAR(512) NOT NULL AFTER `twoauth`;
ALTER TABLE `pay_order` ADD `attach` VARCHAR(512) NOT NULL AFTER `data`;
";
$sql2 = "
ALTER TABLE `pay_admin` ADD `uuid` VARCHAR(36) NOT NULL AFTER `id`;
ALTER TABLE `pay_user` ADD `uuid` VARCHAR(36) NOT NULL AFTER `id`;
ALTER TABLE `pay_user` ADD `agentuuid` VARCHAR(36) NOT NULL AFTER `uuid`;
ALTER TABLE `pay_user` ADD `adminuuid` VARCHAR(36) NOT NULL AFTER `agentuuid`;
ALTER TABLE `pay_balance` ADD `uuid` VARCHAR(36) NOT NULL AFTER `id`;
ALTER TABLE `pay_balance_history` ADD `uuid` VARCHAR(36) NOT NULL AFTER `id`;
ALTER TABLE `pay_recharge` ADD `uuid` VARCHAR(36) NOT NULL AFTER `id`;
ALTER TABLE `pay_recharge_history` ADD `uuid` VARCHAR(36) NOT NULL AFTER `id`;
ALTER TABLE `pay_recharge_record` ADD `uuid` VARCHAR(36) NOT NULL AFTER `pid`;
ALTER TABLE `pay_recharge_record` ADD `agentuuid` VARCHAR(36) NOT NULL AFTER `uuid`;
ALTER TABLE `pay_order` ADD `uuid` VARCHAR(36) NOT NULL AFTER `pid`;
ALTER TABLE `pay_order` ADD `agentuuid` VARCHAR(36) NOT NULL AFTER `uuid`;
";

$sql3 = "
-- 修改pay_order表
ALTER TABLE `pay_order` CHANGE `notify_url` `notify_url` VARCHAR(4096) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `pay_order` CHANGE `return_url` `return_url` VARCHAR(4096) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `pay_order` CHANGE `buyer` `buyer` VARCHAR(4096) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `pay_order` CHANGE `uid` `uid` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `pay_order` CHANGE `addtime` `addtime` DATETIME NOT NULL;
ALTER TABLE `pay_order` CHANGE `endtime` `endtime` DATETIME NOT NULL;
ALTER TABLE `pay_order` CHANGE `order_one` `order_one` INT(11) NOT NULL;
ALTER TABLE `pay_order` CHANGE `order_test` `order_test` INT(11) NOT NULL;
ALTER TABLE `pay_order` CHANGE `order_five` `order_five` INT(11) NOT NULL;
ALTER TABLE `pay_order` CHANGE `order_ten` `order_ten` INT(11) NOT NULL;
ALTER TABLE `pay_order` CHANGE `order_twenty` `order_twenty` INT(11) NOT NULL;
ALTER TABLE `pay_order` CHANGE `order_thirty` `order_thirty` INT(11) NOT NULL;
ALTER TABLE `pay_order` CHANGE `remarks` `remarks` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `pay_order` CHANGE `ish5` `ish5` INT(1) NOT NULL DEFAULT '0';
ALTER TABLE `pay_order` CHANGE `AlipayOrderID` `AlipayOrderID` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `pay_order` CHANGE `payee_account` `payee_account` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `pay_order` CHANGE `alreadyoutrmb` `alreadyoutrmb` INT(1) NOT NULL DEFAULT '0';
ALTER TABLE `pay_order` CHANGE `payee_money` `payee_money` INT(11) NOT NULL DEFAULT '0';

-- 修改pay_user表
ALTER TABLE `pay_user` CHANGE `cash_level` `cash_level` TEXT NOT NULL;

-- 添加索引
-- ALTER TABLE `pay_order` ADD INDEX(`cashstatus`);
-- ALTER TABLE `pay_order` ADD INDEX(`endtime`);
-- ALTER TABLE `pay_order` ADD INDEX(`status`);

CREATE TABLE IF NOT EXISTS`pay_person` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(36) NOT NULL,
  `trade_no` varchar(128) NOT NULL,
  `mark` varchar(128) UNIQUE NOT NULL,
  `mobile_url` text NOT NULL,
  `type` varchar(20) NOT NULL,
  `addtime` datetime NOT NULL,
  `endtime` datetime NOT NULL,
  `money` decimal(18,2) NOT NULL,
  `status` int(1) NOT NULL DEFAULT '0',
  `note1` text NOT NULL,
  `note2` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `pay_order` CHANGE `payee_account` `payee_account` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `pay_order` CHANGE `AlipayOrderID` `AlipayOrderID` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `pay_order` CHANGE `uid_status` `appid` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
ALTER TABLE `pay_order` CHANGE `admin_status` `money2` DECIMAL(18,2) NOT NULL;
";
$sql4 = "
ALTER TABLE `pay_channel` ADD `uuid` VARCHAR(36) NOT NULL AFTER `id`;
UPDATE `pay_order` SET `order_five` = '1' WHERE `remarks` != '' OR `endtime` <= '2018-11-20 00:00:00';
";

$sql = $sql4;
$sql = explode(";",$sql);
foreach($sql as $value){
    $value = trim($value);
    if($value == "") continue;
    try{
        $DB->query($value);
        echo $value."<br>";
    }catch(Exception $e){
        echo $e->getMessage()."<br>";
    }
}
