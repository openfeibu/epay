<?php
ini_set('display_errors','On');
error_reporting(E_ALL);
register_shutdown_function('my_shutdown');
function my_shutdown(){
    echo Console_log::fetch_output();
}
class Console_log{
    private static $output = '';
    static function log($data){
        if(is_array($data) || is_object($data)){
            $data = json_encode($data);
        }
        ob_start();
        if(self::$output === ''){
            echo "<script>";
        }
        echo "console.log('{$data}');";
        self::$output .= ob_get_contents();
        ob_end_clean();
    }
    static function fetch_output(){
        if(self::$output !== ''){
            self::$output .= "</script>";
        }
        return self::$output;
    }
}
