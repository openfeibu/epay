<?php
namespace epay;

class func{
    

    /**
     * 去除空白
     */
    public static function DeleteHtml($str)
    {
        return self::trimall($str);
        $str = trim($str); //清除字符串两边的空格
        $str = preg_replace("/\t/","",$str); //使用正则表达式替换内容，如：空格，换行，并将替换为空。
        $str = preg_replace("/\r\n/","",$str);
        $str = preg_replace("/\r/","",$str);
        $str = preg_replace("/\n/","",$str);
        $str = preg_replace("/ /","",$str);
        $str = preg_replace("/  /","",$str);  //匹配html中的空格
        return trim($str); //返回字符串
    }

    public static function trimall($str){
        $qian=array(" ","　","\t","\n","\r");
        return str_replace($qian, '', $str);
    }

    /**
     * 判断密码重点级别
     * @return [type] [description]
     */
    public static function judgepassword($str){
        $score = 0;
        if(preg_match("/[0-9]+/",$str)){
            $score++;
        }
        if(preg_match("/[0-9]{3,}/",$str)){
            $score++;
        }
        if(preg_match("/[a-z]+/",$str)){
            $score++;
        }
        if(preg_match("/[a-z]{3,}/",$str)){
            $score++;
        }
        if(preg_match("/[A-Z]+/",$str)){
            $score++;
        }
        if(preg_match("/[A-Z]{3,}/",$str)){
            $score++;
        }
        if(preg_match("/[_|\-|+|=|*|!|@|#|$|%|^|&|(|)]+/",$str)){
            $score += 2;
        }
        if(preg_match("/[_|\-|+|=|*|!|@|#|$|%|^|&|(|)]{3,}/",$str)){
            $score++;
        }
        if(strlen($str) >= 10){
            $score++;
        }
        return $score;
    }

}