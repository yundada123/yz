<?php
return [
    'app_id' => 'wx9cdbc664b2e39df0',
    'secret' => '5d102fec121f593593769009fdc10636',
    'token' => 'easywechat',
    'aes_key' => '......',

    /**
     * 接口请求相关配置，超时时间等，具体可用参数请参考：
     * https://github.com/symfony/symfony/blob/5.3/src/Symfony/Contracts/HttpClient/HttpClientInterface.php
     */
    'http' => [
    'throw'  => true, // 状态码非 200、300 时是否抛出异常，默认为开启
    'timeout' => 5.0,
    // 'base_uri' => 'https://api.weixin.qq.com/', // 如果你在国外想要覆盖默认的 url 的时候才使用，根据不同的模块配置不同的 uri
    ],
];