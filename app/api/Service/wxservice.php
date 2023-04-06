<?php
namespace app\api\Service;

use EasyWeChat\OfficialAccount\Application;
use think\facade\Cache;
use think\facade\Session;

class wxservice {
    private $config = [];
    public function __construct()
    {
        $this->config=config('wx');
    }

    public function run()
    {
        return new Application($this->config);
    }

    public function getAuth()
    {
        $app=$this->run();
        $oauth=$app->getOAuth();
        if (empty(Session::get('wechat_user'))) {
//            Cache::set('intend_url','http://wx.6sum.cn/api/index',60);
            Session::set('intend_url','http://wx.6sum.cn/api/index');
            //生成完整的授权URL
            $redirectUrl = $oauth->redirect('http://wx.6sum.cn/api/index/wxcallback');
            header("Location: {$redirectUrl}");
            exit;
        } else {
            // 已经登录过，则从 session 中取授权者信息
//            $user = $_SESSION['wechat_user'];
            $user = Session::get('wechat_user');
            return $user;
            // ...
        }
    }
}
