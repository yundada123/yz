<?php

namespace app\api\controller;

use app\api\Service\wxservice;
use think\facade\Cache;
use think\facade\Session;
use think\Request;
use EasyWeChat\OfficialAccount\Application;


class User
{
    public $user;
    public function __construct()
    {

    }
    public function login(Request $request)
    {
        return json(['phone'=>$request->param('phone'),'user'=>session('wechat_user')]);

    }

    public function reg(Request $request)
    {
        $phone=$request->param('phone');
        $code=$request->param('code');
        $vcode=new Sms();
        $user=Session::get('wechat_user');
        if(empty($user)){
            return json(['code'=>222,'msg'=>'授权信息获取失败']);
        }
        $yzphone=$vcode->checkCode($phone);
        if(!$yzphone){
            return json(['code'=>222,'msg'=>'验证码错误']);
        }
        $userModel=\app\api\model\User::where('phone',$phone)->findOrEmpty();
//        $DBuser=Cache::get('user_'.$phone);
        if($userModel->isEmpty()){//如果手机号存在  并且微信用户存在
            $userModel->phone=$phone;
            $userModel->openid=$user['id'];
            $userModel->nickname=$user['nickname'];
            $userModel->avatar=$user['avatar'];
            $userModel->time=date('Y-m-d H:i:s',time());
            $userModel->save();
            return  json(['code'=>203,'msg'=>$user,'url'=>'http://wx.6sum.cn/api/index/index2?phone='.$phone]);
        }else{
//            Cache::set('user_'.$phone,$phone);

            return  json(['code'=>203,'msg'=>'注册成功','url'=>'http://wx.6sum.cn/api/index/index2?phone='.$phone]);
        }

        return json(['phone'=>$request->param('phone'),'code'=>$request->param('code'),'user'=>session('wechat_user')]);
    }

    /***
     * 微信登录授权
     * @return void
     */
    public function wxsq()
    {
        $wxser=new wxservice();
        $wxser->run();
        return $wxser->getAuth();
    }

    public function wxcallback()
    {
        $app = new Application(config('wx'));

        $oauth = $app->getOauth();

// 获取 OAuth 授权用户信息
        $user = $oauth->userFromCode($_GET['code']);
        Cache::set('wechat_user',$user->toArray(),60);
        $targetUrl = empty(Cache::get('intend_url')) ? '/' : Cache::get('intend_url');
        header('Location:'. $targetUrl); // 跳转回授权前的目标页面：user/profile
    }
}