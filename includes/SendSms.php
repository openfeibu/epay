<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/9 0009
 * Time: 上午 10:07
 */

class SendSms
{
    protected $accessKeyId = "LTAIZBEmeCM8kgpR";
    protected $accessKeySecret = "2Uw6Psx4SFcheZ92ZjT6Y3WgQpjP0g";
    protected $target = "https://dysmsapi.aliyuncs.com/?";

    /**
     * 发送短信
     * @param $PhoneNumbers    //手机号码
     * @param $SignName        //签名名称
     * @param $ParamString     //验证码
     * @param $TemplateCode    //短信模板
     * @return mixed|string
     */
    public function send_sms($PhoneNumbers,$SignName,$TemplateCode,$ParamString=""){
        date_default_timezone_set("GMT");
        $SignatureNonce = $this->guid();
        $data = array(
            //	访问密钥 ID。AccessKey 用于调用 API。
            'AccessKeyId' => $this->accessKeyId,
            //	API 的名称。
            'Action' => 'SendSms',
            //返回参数的语言类型
            'Format' => 'json',
            //
            'OutId' => '123',
            //手机号码
            'PhoneNumbers' => $PhoneNumbers,
            //API支持的RegionID，如短信API的值为：cn-hangzhou。
            'RegionId' => 'cn-hangzhou',
            //签名名称
            'SignName'=> $SignName,
            //签名方式。取值范围：HMAC-SHA1。
            'SignatureMethod' => 'HMAC-SHA1',
            //签名唯一随机数
            //'SignatureNonce'=> '45e25e9b-0a6f-4070-8c85-2956eda1b466',
            'SignatureNonce'=> $SignatureNonce,
            //签名算法版本。取值范围：1.0。
            'SignatureVersion' => '1.0',
            //短信模板
           // 'TemplateCode' => 'SMS_160536008',
            'TemplateCode' => $TemplateCode,
            //短信验证码
            'TemplateParam' => $ParamString,
            //指定的时间戳或日期值格式
            'Timestamp' => date('Y-m-d\TH:i:s\Z'),
            //短信版本
            'Version' =>  '2017-05-25'
        );

        $data['Signature'] = $this->computeSignature($data, $this->accessKeySecret);
        // 发送请求
        //$result = xml_to_array(https_request($target.http_build_query($data)));
        //echo $result['Error']['Code']."--->".$result['Error']['Message'];
        $ali_back = $this->https_request($this->target.http_build_query($data));
        $ali_back = json_decode($ali_back,true);
        return $ali_back;
    }

    // 使用urlencode编码后，将"+","*","%7E"做替换即满足ECS API规定的编码规范
    function percentEncode($str)
    {

        $res = urlencode($str);
        $res = preg_replace('/\+/', '%20', $res);
        $res = preg_replace('/\*/', '%2A', $res);
        $res = preg_replace('/%7E/', '~', $res);
        return $res;
    }

    //阿里云签名
    function computeSignature($parameters, $accessKeySecret)
    {
        // 将参数Key按字典顺序排序
        ksort($parameters);
        // 生成规范化请求字符串
        $canonicalizedQueryString = '';
        foreach($parameters as $key => $value)
        {
            $canonicalizedQueryString .= '&' . $this->percentEncode($key)
                . '=' . $this->percentEncode($value);
        }
        // 生成用于计算签名的字符串 stringToSign
        $stringToSign = 'GET&%2F&' . $this->percentencode(substr($canonicalizedQueryString, 1));
        //echo "<br>".$stringToSign."<br>";
        // 计算签名，注意accessKeySecret后面要加上字符'&'
        $signature = base64_encode(hash_hmac('sha1', $stringToSign, $accessKeySecret . '&', true));
        return $signature;
    }

    //发起http请求
    public function https_request($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        if (curl_errno($curl)) {return 'ERROR '.curl_error($curl);}
        curl_close($curl);
        return $data;
    }

    //生成uuid
    function guid(){
        if (function_exists('com_create_guid')){
            return com_create_guid();
        }else{
            mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);// "-"
            $uuid = //chr(123)// "{"
                substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid,12, 4).$hyphen
                .substr($charid,16, 4).$hyphen
                .substr($charid,20,12);
            //.chr(125);// "}"
            return $uuid;
        }
    }
}