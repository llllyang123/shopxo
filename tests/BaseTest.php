<?php

namespace tests;

use PHPUnit\Framework\TestCase;
// use think\App;

class BaseTest extends TestCase
{
     // 设置基础 URL 或测试入口
    protected $baseUrl = 'http://172.16.221.135:100'; // 根据你的项目调整
    
    // public function __construct(?string $name = null, array $data = [], string $dataName = '') {
    //     // 引入需要的环境
    //     // require_once __DIR__ . '/../thinkphp/base.php';
    //     require './public/index.php';
    //     // 初始化 App 对象，并将 APP_PATH 指向项目的 application 目录
    //     // App::getInstance()->path(__DIR__ . '/../application/')->initialize();

    //     parent::__construct($name, $data, $dataName);
    //     // define('APPLICATION_VERSION', '1.0.0');
    // }

    protected function setUp(): void
    {
        parent::setUp();
        // 初始化代码，例如加载环境变量或设置测试数据库连接
        if (!defined('ROOT')) {
            // define('ROOT', dirname(__DIR__)); // 确保常量 ROOT 定义
            // define('DS', DIRECTORY_SEPARATOR);
            // define('APP_PATH', __DIR__ . '/../app/');
            // define('__MY_DOMAIN__', 'http://172.16.221.135:100'); // 或从配置文件中获取
            // define('__MY_DOMAIN__', 'http://127.0.0.1:100');
            // // 在配置文件中定义常量，例如 common.php 或 config.php
            // if (!defined('__MY_MAIN_DOMAIN__')) {
            //     define('__MY_MAIN_DOMAIN__', 'http://127.0.0.1:100'); // 或者你使用的本地 IP 和端口
            // }
            // define('__MY_PUBLIC_URL__', 'http://172.16.221.135:100'); // 替换为实际的公共 URL
            // define('__MY_ROOT_PUBLIC__', $_SERVER['DOCUMENT_ROOT'] . '/public'); // 或从配置文件中获取
            // define('app\\service\\APPLICATION', 'some_value'); // Replace with actual value you need
            // define('app\\IS_AJAX', true); 
            // define('app\\service_tenants\\__MY_URL__', 'http://127.0.0.1:100'); // Replace with the actual URL
            // define('APPLICATION_VERSION', '1.0.0');
        }

    }
    
    /**
     * 发起 GET 请求并返回 HTTP 状态码
     *
     * @param string $url      目标 URL
     * @param array  $options  cURL 可选项
     * @return int HTTP 状态码
     */
    public function putUrl(string $url, array $options = []): int
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);  // 返回 header
        curl_setopt($ch, CURLOPT_NOBODY, true); // 忽略 body

        // 应用额外的 cURL 配置
        foreach ($options as $option => $value) {
            curl_setopt($ch, $option, $value);
        }

        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode;
    }
    
}