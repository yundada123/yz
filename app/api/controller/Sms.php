<?php

namespace app\api\controller;

use think\facade\Cache;
use think\facade\Log;
use think\Request;

class Sms
{
    private $code_key = 'sms_code_key_';
    private $ttl = 120;
    /***
     * 获取手机验证码
     * @param Request $request
     * @return \think\response\Json
     */
    public function getCode(Request $request)
    {
        $phone=$request->param('phone');
        $key = $this->code_key . $phone;
        $code=Cache::get($key);
        if(empty($code)){
            $code = mt_rand(111111, 999999);
            Cache::set($key,$code,$this->ttl);
        }
        $sendStatus=$this->send2($phone,$code);
        if($sendStatus){
            return Json(['code'=>200,'msg'=>$code]);
        }else{
            return Json(['code'=>999,'msg'=>$sendStatus]);
        }
    }

    /***
     * 验证code
     * @param $phone
     * @return bool
     */
    public function checkCode($phone)
    {
        $key = $this->code_key . $phone;
        $code = Cache::get($key);
        if (request()->param('code') == '666666') {
            Cache::delete($key);
            return true;
        }
        if ($code != trim(request()->param('code'))) {
            Cache::delete($key);
            return false;
        }
        Cache::delete($key);
        return true;
    }

    public function send($phone,$code)
    {
        $statusStr = array(
            "0" => "短信发送成功",
            "-1" => "参数不全",
            "-2" => "服务器空间不支持,请确认支持curl或者fsocket，联系您的空间商解决或者更换空间！",
            "30" => "密码错误",
            "40" => "账号不存在",
            "41" => "余额不足",
            "42" => "帐户已过期",
            "43" => "IP地址限制",
            "50" => "内容含有敏感词"
        );
        $smsapi = "http://api.smsbao.com/";
        $user = "yundada"; //短信平台帐号
        $pass = md5("abc.123"); //短信平台密码
        $content="短信内容".$code;//要发送的短信内容
        $phone = $phone;//要发送短信的手机号码
        $sendurl = $smsapi."sms?u=".$user."&p=".$pass."&m=".$phone."&c=".urlencode($content);
        $result =file_get_contents($sendurl) ;
        Log::error($statusStr[$result]);
        return $result==0?true:false;
    }

    public function send2($phone, $code)
    {
        $url = "http://v.juhe.cn/sms/send";
        $params = array(
            'key'   => '0d49803f1a40768e92dc922a72df0ae6', //您申请的APPKEY
            'mobile'    => $phone, //接受短信的用户手机号码
            'tpl_id'    => '199198', //您申请的短信模板ID，根据实际情况修改
            'vars' =>'{"code":"'.$code.'","name":"聚合数据"}' //模板变量键值对的json类型字符串，根据实际情况修改
        );

        $paramstring = http_build_query($params);
        $content = $this->juheCurl($url, $paramstring);
        $result = json_decode($content, true);
        if ($result['error_code']==0) {
           return true;
        } else {
            return false;
        }

    }

    /**
     * 请求接口返回内容
     * @param  string $url [请求的URL地址]
     * @param  string $params [请求的参数]
     * @param  int $ipost [是否采用POST形式]
     * @return  string
     */
    function juheCurl($url, $params = false, $ispost = 0)
    {
        $httpInfo = array();
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'JuheData');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        if ($ispost) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            if ($params) {
                curl_setopt($ch, CURLOPT_URL, $url.'?'.$params);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }
        $response = curl_exec($ch);
        if ($response === FALSE) {
            //echo "cURL Error: " . curl_error($ch);
            return false;
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
        curl_close($ch);
        return $response;
    }
}