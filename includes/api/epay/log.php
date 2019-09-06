<?php
namespace epay;
class log{
    /**
     * 请确保项目文件有可写权限，不然打印不了日志。
     */
    public static function writeLog($log_file,$data) {
        // $text=iconv("GBK", "UTF-8//IGNORE", $text);
        //$text = characet ( $text );
        if(!file_exists($log_file)){
            $log2_file = $log_file;
            $log2_file = str_replace("/etc/log/","/etc/log2/",$log2_file);
            $str = '
<?php
$config_log = __DIR__.DIRECTORY_SEPARATOR."../config_log.php";
if(file_exists($config_log)){
    require_once $config_log;
}else{
    exit();
}
?>
';

            file_put_contents ( $log_file, "{$str}\r\n", FILE_APPEND );
            file_put_contents ( $log2_file, "{$str}\r\n", FILE_APPEND );
        }
        date_default_timezone_set("Asia/Hong_Kong");
        if(is_array($data) || is_object($data)){
            $text = var_export($data,true);
        }else{
            $text = $data;
        }
        file_put_contents ( $log_file, date ( "Y-m-d H:i:s" ) . "  " . \epay\real_ip() ." ". $text . "\r\n", FILE_APPEND );
    }
}
