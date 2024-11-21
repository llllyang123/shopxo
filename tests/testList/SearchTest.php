<?php
namespace tests;

use PHPUnit\Framework\TestCase;

class SearchTest extends BaseTest
{

    public function testSearchPage(): void
    {
        $url = $this->baseUrl.'/?s=search/index.html'; // 替换为实际页面地址

        $httpCode = $this->putUrl($url);

        // 断言状态码为 200
        $this->assertSame(200, $httpCode, "Failed on $url");
    }
    
}