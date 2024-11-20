<?php
namespace tests;

use PHPUnit\Framework\TestCase;
use think\Http;

class IndexTest extends BaseTest
{

    public function testIndexPage(): void
    {
        $url = $this->baseUrl.'/index.php'; // 替换为实际页面地址

        $httpCode = $this->putUrl($url);

        // 断言状态码为 200
        $this->assertSame(200, $httpCode, "Failed on $url");
    }
    
}