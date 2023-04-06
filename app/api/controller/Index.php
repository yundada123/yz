<?php
declare (strict_types = 1);

namespace app\api\controller;
use app\api\Service\wxservice;
use EasyWeChat\OfficialAccount\Application;
use think\facade\Cache;
use think\facade\View;
use think\facade\Session;
use think\Request;


class Index
{
    public $user;
    /**
     *
     * 用thinkphp6，弄1个功能 用户注册登录的h5：
     * 获取微信用户授权后，用手机号短信验证码验证，完成注册。
     * 当用户后续进入时，如果登录信息失效，先进行微信用户授权验证，
     * 如果存在该用户直接完成登录，
     * 如果不存在，再通过手机短信验证，
     * 如果手机号存在则完成登录，
     * 如果不存在，完成注册
     */
    public function index(Request $request)
    {
        $user=Session::get('wechat_user');
//        $phone=Cache::get('user_'.$request->param('phone'));
        if(empty($user)){//微信信息不存在
            $this->user=$this->wxsq();
            return View::fetch();
        }else{
            return View::fetch();
//            $redirectUrl='http://wx.6sum.cn/api/index/index2';
//            header("Location: {$redirectUrl}");
        }
    }

    public function index2(Request $request)
    {
        $user=Session::get('wechat_user');
        $phone=$request->param('phone');
        $userModel=\app\api\model\User::where('phone',$phone)->findOrEmpty();
        if($userModel->isEmpty()){
            $this->user=$this->wxsq();
            return View::fetch('index');
        }else{
            echo "微信昵称:".$userModel->nickname."<br>";
            echo "手机号:".$userModel->phone."<br>";
            echo "<br><a href='http://wx.6sum.cn/api/index/index3'>清除登录信息</a><br>";
            echo "<a href='http://wx.6sum.cn/api/index/index4'>清除缓存</a>";
            dd($user);
        }
    }

    public function index3()
    {
        Session::delete('wechat_user');
        $redirectUrl='http://wx.6sum.cn/api/index/index2';
        header("Location: {$redirectUrl}");

    }

    public function index4(Request $request)
    {
        $DBuser=Cache::clear();
        dd($DBuser);

    }
    /***
     * 微信登录授权
     * @return void
     */
    public function wxsq()
    {
        $wxser=new wxservice();
//        $wxser->run();
        return $wxser->getAuth();
    }

    public function wxcallback()
    {
        $app = new Application(config('wx'));

        $oauth = $app->getOauth();

// 获取 OAuth 授权用户信息
        $user = $oauth->userFromCode($_GET['code']);
        Session::set('wechat_user',$user->toArray());
        $targetUrl = empty(session('intend_url')) ? '/api/index' : session('intend_url');
        header('Location:'. $targetUrl); // 跳转回授权前的目标页面：user/profile
    }

}
