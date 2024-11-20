<?php
namespace tests;

use PHPUnit\Framework\TestCase;
use think\Http;

class AdminTest extends BaseTest
{

    public function testIndexPage(): void
    {
        $url = $this->baseUrl.'/adminhqxuks.php?s=admin/logininfo.html'; // 替换为实际页面地址

        $httpCode = $this->putUrl($url);

        // 断言状态码为 200
        $this->assertSame(200, $httpCode, "Failed on $url");
    }
    
}
