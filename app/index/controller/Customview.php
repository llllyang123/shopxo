<?php
// +----------------------------------------------------------------------
// | ShopXO 国内领先企业级B2C免费开源电商系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011~2099 http://shopxo.net All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( https://opensource.org/licenses/mit-license.php )
// +----------------------------------------------------------------------
// | Author: Devil
// +----------------------------------------------------------------------
namespace app\index\controller;

use app\service_tenants\CustomViewService;
use app\service_tenants\SeoService;

/**
 * 自定义页面
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class CustomView extends Common
{
	/**
     * 构造方法
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-11-30
     * @desc    description
     */
    public function __construct()
    {
        parent::__construct();
    }

	/**
     * 详情
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-06T21:31:53+0800
     */
	public function Index()
	{
		if(!empty($this->data_request['id']))
		{
			$id = intval($this->data_request['id']);
			$params = [
				'where' => [
                    ['is_enable', '=', 1],
                    ['id', '=', $id],
                ],
				'm' => 0,
				'n' => 1,
			];
			$ret = CustomViewService::CustomViewList($params);
			if(!empty($ret['data']) && !empty($ret['data'][0]))
			{
                $data = $ret['data'][0];

				// 访问统计
				CustomViewService::CustomViewAccessCountInc(['id'=>$data['id']]);

				// 模板数据
				$assign = [
                    'data'      => $data,
                    'is_header' => $data['is_header'],
                    'is_footer' => $data['is_footer'],
				];

                // seo
                $seo_title = empty($data['seo_title']) ? $data['name'] : $data['seo_title'];
                $assign['home_seo_site_title'] = SeoService::BrowserSeoTitle($seo_title, 2);
                if(!empty($data['seo_keywords']))
                {
                    $assign['home_seo_site_keywords'] = $data['seo_keywords'];
                }
                if(!empty($data['seo_desc']))
                {
                    $assign['home_seo_site_description'] = $data['seo_desc'];
                }

                // 数据赋值
                MyViewAssign($assign);
				return MyView();
			}
		}
		MyViewAssign('msg', MyLang('customview.custom_view_no_data_tips'));
		return MyView('public/tips_error');
	}
}
?>