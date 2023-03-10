<?php
namespace epay;

class tool{
    #在需要查找的内容后一行新起一行插入内容
    public static function insertAfterTarget($filePath, $insertCont, $target){
        $result = null;
        $fileCont = file_get_contents($filePath);
        $targetIndex = strpos($fileCont, $target); #查找目标字符串的坐标

        if ($targetIndex !== false) {
            #找到target的后一个换行符
            $chLineIndex = strpos(substr($fileCont, $targetIndex), "\n") + $targetIndex;
            if ($chLineIndex !== false) {
                #插入需要插入的内容
                $result = substr($fileCont, 0, $chLineIndex + 1) . $insertCont . "\n" . substr($fileCont, $chLineIndex + 1);
                $fp = fopen($filePath, "w+");
                fwrite($fp, $result);
                fclose($fp);
            }
        }
    }

    #删除内容所在的某一行
    public static function delTargetLine($filePath, $target)
    {
        $result = null;
        $fileCont = file_get_contents($filePath);
         $targetIndex = strpos($fileCont, $target); #查找目标字符串的坐标

         if ($targetIndex !== false) {
             #找到target的前一个换行符
             $preChLineIndex = strrpos(substr($fileCont, 0, $targetIndex + 1), "\n");
             #找到target的后一个换行符
             $AfterChLineIndex = strpos(substr($fileCont, $targetIndex), "\n") + $targetIndex;
             if ($preChLineIndex !== false && $AfterChLineIndex !== false) {
                 #重新写入删掉指定行后的内容
                 $result = substr($fileCont, 0, $preChLineIndex + 1) . substr($fileCont, $AfterChLineIndex + 1);
                 $fp = fopen($filePath, "w+");
                 fwrite($fp, $result);
                 fclose($fp);
             }
         }
     }

    #删除内容所在的某一行
    public static function delTargetLine2($string, $target)
    {
        $result = $string;
        $fileCont = $string;
        $targetIndex = strpos($fileCont, $target); #查找目标字符串的坐标

        if ($targetIndex !== false) {
            //var_dump($targetIndex);
            #找到target的前一个换行符
            //$preChLineIndex = strrpos(substr($fileCont, 0, $targetIndex + 1), "\n");
            $preChLineIndex = strrpos(substr($fileCont, 0, $targetIndex + 1), "\n");
            #找到target的后一个换行符
            $AfterChLineIndex = strpos(substr($fileCont, $targetIndex), "\n") + $targetIndex;
            if ($preChLineIndex !== false && $AfterChLineIndex !== false) {
                #重新写入删掉指定行后的内容
                $result = substr($fileCont, 0, $preChLineIndex + 1) . substr($fileCont, $AfterChLineIndex + 1);
            }
        }
        return $result;
    }

    #获取某段内容的行号
    /**
     * @param $filePath
     * @param $target   待查找字段
     * @param bool $first   是否再匹配到第一个字段后退出
     * @return array
     */
    public static function getLineNum($filePath, $target, $first = false)
    {
        $fp = fopen($filePath, "r");
        $lineNumArr = array();
        $lineNum = 0;
        while (!feof($fp)) {
            $lineNum++;
            $lineCont = fgets($fp);
            if (strstr($lineCont, $target)) {
                if($first) {
                    return $lineNum;
                } else {
                    $lineNumArr[] = $lineNum;
                }
            }
        }
        return $lineNumArr;
    }

    public static function deLineEnd($string){
        $result = str_replace("\n","",$string);
        $result = str_replace("\r","",$result);
        return $result;
    }

}