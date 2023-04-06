<?php
declare (strict_types = 1);

namespace app\api\controller;
use app\BaseController;
use think\response\Json;

class Controller extends BaseController
{

    /**
     * 初始化
     */
    public function initialize()
    {
        //拦截验证
        //do something
    }


    /**
     * 返回封装后的 API 数据到客户端
     */
    protected function renderData(int $status = null, string $message = '', array $data = []): Json
    {
        is_null($status) && $status = config('status.success');
        return json(compact('status', 'message', 'data'));
    }

    /**
     * 返回操作成功json
     * @param array|string $data
     * @param string $message
     * @return Json
     */
    protected function renderSuccess($data = [], string $message = 'success'): Json
    {
        if (is_string($data)) {
            $message = $data;
            $data = [];
        }
        return $this->renderData(200, $message, $data);
    }

    /**
     * 返回操作失败json
     * @param string $message
     * @param array $data
     * @return Json
     */
    protected function renderError(string $message = 'error', array $data = []): Json
    {
        return $this->renderData(500, $message, $data);
    }

    /**
     * 获取post数据 (数组)
     * @param $key
     * @return mixed
     */
    protected function postData($key = null)
    {
        return $this->request->post(empty($key) ? '' : "{$key}/a");
    }

    /**
     * 获取post数据 (数组)
     * @param string $key
     * @return mixed
     */
    protected function postForm(string $key = 'form')
    {
        return $this->postData($key);
    }

    /**
     * 获取post数据 (数组)
     * @param $key
     * @return mixed
     */
    protected function getData($key = null)
    {
        return $this->request->get(is_null($key) ? '' : $key);
    }
}