<?php
namespace epay;

class file{

    //复制文件夹
    public static function copydir($source,$dest){
        if(!file_exists($dest)){
            mkdir($dest);
        }
        $handle = opendir($source);
        while (($item = readdir($handle)) !== false){
            if($item == '.' || $item == '..'){
                continue;
            }
            $_source = $source."/".$item;
            $_dest = $dest."/".$item;
            if(is_file($_source)){
                copy($_source,$_dest);
            }
            if(is_dir($_source)) self::copydir($_source,$_dest);
        }
        closedir($handle);
    }

    //删除文件夹下的符合规则的文件
    public static function delete($dir,$files,$num = 0){
        if ($handle = opendir($dir)) {
            $i = 0;
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    if(is_dir($dir.$file)){
                        //continue;
                    }
                    if(preg_match($files,$file)){
                        echo "yes";
                        unlink($dir.$file);
                        echo "DELETE FILE($file): SUCCESS<br>";
                        $i++;
                    }
                }else{
                    continue;
                }
                echo $file."<br>";
                echo "<hr>";
                if($num > 0){
                    if($i >= $num){
                        break;
                    }
                }
            }
            closedir($handle);
        }else{
            var_dump($handle);
        }
        echo $i;
        return $i;
    }

    //压缩文件夹
    public static function zip(){

    }
}
