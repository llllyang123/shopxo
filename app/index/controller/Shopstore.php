<?php

namespace app\index\controller;

use app\service_tenants\SeoService;
// use app\service_tenants\SearchService;
use app\service_tenants\ShopStoreService;
use app\service_tenants\ApiService;

/**
 * 商品分类
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Shopstore extends Common
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
     * 首页
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2017-02-22T16:50:32+0800
     */
    public function Index()
    {
        // 是否需要登录
        if(MyC('home_search_is_login_required', 0) == 1)
        {
            IsUserLogin();
        }

        // 是否禁止搜索
        $ret = ShopStoreService::SearchProhibitUserAgentCheck();
        if($ret['code'] != 0)
        {
            return MyView('public/tips_error', ['msg' => $ret['msg']]);
        }

        // post搜索
        if(!empty($this->data_post['wd']))
        {
            return MyRedirect(MyUrl('index/shopstore/index', ['wd'=>StrToAscii($this->data_post['wd']), 'shop' => $this->data_request['shop']]));
        }
        
        $params = $this->data_request;
        
        // 商品信息
        // $goods = $ret['data'][0];
        //商家店铺信息
        // $store = GoodsService::GoodsStoreInfo($goods);
        
        // 搜素条件
        $map = ShopStoreService::SearchWhereHandle($params);

        // 获取商品列表
        $ret = ShopStoreService::GoodsList($map, $params);
        
        // 分页
        $page_params = [
            'number'    => $ret['data']['page_size'],
            'total'     => $ret['data']['total'],
            'where'     => $params,
            'page'      => $ret['data']['page'],
            'url'       => MyUrl('index/shopstore/index'),
            'bt_number' => IsMobile() ? 2 : 4,
        ];
        $page = new \base\Page($page_params);
        $page_html = $page->GetPageHtml();

        // 面包屑导航
        // $breadcrumb_data = BreadcrumbService::Data('GoodsSearch', $params);

        // 数据列表展示布局（0九宫格、1图文列表）
        $list_layout_key = 'user_search_layout_type';
        $list_layout_value = MySession($list_layout_key);
        if(isset($params['layout']))
        {
            $list_layout_value = empty($params['layout']) ? 0 : intval($params['layout']);
            MySession($list_layout_key, $list_layout_value);
        } else {
            if(empty($list_layout_value))
            {
                $list_layout_value = 0;
            }
        }

        // 关键字处理
        if(!empty($params['wd']))
        {
            $params['wd'] = AsciiToStr($params['wd']);
        }

        // 价格滑条
        if(!empty($params['price']))
        {
            $arr = explode('-', $params['price']);
            if(count($arr) == 2)
            {
                $params['price_min'] = $arr[0];
                $params['price_max'] = $arr[1];
            }
        }

        // 模板数据
        $assign = [
            // 基础参数
            'is_map'            => $map['is_map'],
            'params'            => $params,
            'page_html'         => $page_html,
            'data_total'        => $ret['data']['total'],
            'data_list'         => $ret['data']['data'],
            // 排序方式
            'map_order_by_list' => ShopStoreService::SearchMapOrderByList($params),
            // 面包屑导航
            // 'breadcrumb_data'   => $breadcrumb_data,
            // 列表布局类型
            'list_layout_value' => $list_layout_value,
            // 范围滑条组件
            'is_load_jrange'    => 1,
            // 滑条价格最大金额
            'range_max_price'   => ShopStoreService::SearchGoodsMaxPrice(),
            'store'             => $ret['data']['store'],
            'shop'              => !empty($params['shop']) ? $params['shop'] : 0,
        ];
        // // 品牌列表
        // $assign['brand_list'] = ShopStoreService::SearchMapHandle(ShopStoreService::CategoryBrandList($map, $params), 'bid', 'id', $params);

        // 指定数据
        $assign['search_map_info'] = ShopStoreService::SearchMapInfo($params);

        // 商品分类
        $assign['category_list'] = ShopStoreService::SearchMapHandle(ShopStoreService::GoodsCategoryList($params), 'cid', 'id', $params);

        // 筛选价格区间
        $assign['screening_price_list'] = ShopStoreService::SearchMapHandle(ShopStoreService::ScreeningPriceList($params), 'peid', 'id', $params);

        // 商品产地
        $assign['goods_place_origin_list'] = ShopStoreService::SearchMapHandle(ShopStoreService::SearchGoodsPlaceOriginList($map, $params), 'poid', 'id', $params);

        // 商品参数
        $assign['goods_params_list'] = ShopStoreService::SearchMapHandle(ShopStoreService::SearchGoodsParamsValueList($map, $params), 'psid', 'id', $params, ['is_ascii'=>true, 'field'=>'value']);

        // 商品规格
        $assign['goods_spec_list'] = ShopStoreService::SearchMapHandle(ShopStoreService::SearchGoodsSpecValueList($map, $params), 'scid', 'id', $params, ['is_ascii'=>true, 'field'=>'value']);

        // 增加搜索记录
        $params['user_id'] = empty($this->user) ? 0 : $this->user['id'];
        $params['search_result_data'] = $ret['data'];
        ShopStoreService::SearchAdd($params);

        // seo信息
        // 默认关键字
        $seo_title = empty($params['wd']) ? '' : $params['wd'];
        if(!empty($assign['search_map_info']))
        {
            // 分类、品牌
            $seo_info = empty($assign['search_map_info']['category']) ? (empty($assign['search_map_info']['brand']) ? [] : $assign['search_map_info']['brand']) : $assign['search_map_info']['category'];
            if(!empty($seo_info))
            {
                $seo_title = empty($seo_info['seo_title']) ? $seo_info['name'] : $seo_info['seo_title'];
                // 关键字和描述
                if(!empty($seo_info['seo_keywords']))
                {
                    $assign['home_seo_site_keywords'] = $seo_info['seo_keywords'];
                }
                if(!empty($seo_info['seo_desc']))
                {
                    $assign['home_seo_site_description'] = $seo_info['seo_desc'];
                }
            }
        }
        $assign['home_seo_site_title'] = SeoService::BrowserSeoTitle(empty($seo_title) ? MyLang('search.base_nav_title') : $seo_title, 1);

        // 模板赋值
        MyViewAssign($assign);
        // 钩子
        $this->PluginsHook();
        return MyView();
    }

    /**
     * 商品搜索数据列表
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-07-12
     * @desc    description
     */
    public function DataList()
    {
        
        $params = $this->data_request;
        
        // 搜素条件
        $map = ShopStoreService::SearchWhereHandle($this->data_request);

        // 获取数据
        $ret = ShopStoreService::GoodsList($map, $this->data_request);
        
        // 分页
        $page_params = [
            'number'    => $ret['data']['page_size'],
            'total'     => $ret['data']['total'],
            'where'     => $this->data_request,
            'page'      => $ret['data']['page'],
            'url'       => MyUrl('index/search/index'),
            'bt_number' => IsMobile() ? 2 : 4,
        ];
        $page = new \base\Page($page_params);
        $page_html = $page->GetPageHtml();

        // 搜索记录
        $this->data_request['user_id'] = isset($this->user['id']) ? $this->user['id'] : 0;
        $this->data_request['search_result_data'] = $ret['data'];
        ShopStoreService::SearchAdd($this->data_request);
        
        // 数据列表展示布局（0九宫格、1图文列表）
        $list_layout_key = 'user_search_layout_type';
        $list_layout_value = MySession($list_layout_key);
        if(isset($this->data_request['layout']))
        {
            $list_layout_value = empty($this->data_request['layout']) ? 0 : intval($this->data_request['layout']);
            MySession($list_layout_key, $list_layout_value);
        } else {
            if(empty($list_layout_value))
            {
                $list_layout_value = 0;
            }
        }
        
        // 模板数据
        $assign = [
            // 基础参数
            'is_map'            => $map['is_map'],
            'params'            => $params,
            'page_html'         => $page_html,
            'data_total'        => $ret['data']['total'],
            'data_list'         => $ret['data']['data'],
            // 排序方式
            'map_order_by_list' => ShopStoreService::SearchMapOrderByList($this->data_request),
            // 面包屑导航
            // 'breadcrumb_data'   => $breadcrumb_data,
            // 列表布局类型
            'list_layout_value' => $list_layout_value,
            // 范围滑条组件
            'is_load_jrange'    => 1,
            // 滑条价格最大金额
            'range_max_price'   => ShopStoreService::SearchGoodsMaxPrice(),
        ];
        
        // 指定数据
        $assign['search_map_info'] = ShopStoreService::SearchMapInfo($this->data_request);

        // 渲染html
        $ret['data']['data'] = MyView('', ['data'=>$ret['data']['data']]);
        
         // 模板赋值
        MyViewAssign($assign);
        // 钩子
        $this->PluginsHook();
        return MyView();
        // 返回数据
        // return ApiService::ApiDataReturn($ret);
    }
    
        /**
     * 钩子处理
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2019-04-22
     * @desc    description
     */
    private function PluginsHook()
    {
        $hook_arr = [
            // 搜索页面顶部钩子
            'plugins_view_search_top',

            // 搜索页面底部钩子
            'plugins_view_search_bottom',

            // 搜索页面顶部内部结构里面钩子
            'plugins_view_search_inside_top',

            // 搜索页面底部内部结构里面钩子
            'plugins_view_search_inside_bottom',

            // 搜索页面数据容器顶部钩子
            'plugins_view_search_data_top',

            // 搜索页面数据容器底部钩子
            'plugins_view_search_data_bottom',

            // 搜索条件顶部钩子
            'plugins_view_search_map_top',

            // 搜索页面搜索导航条顶部钩子
            'plugins_view_search_nav_top',

            // 搜索页面搜索导航条内前面钩子
            'plugins_view_search_nav_inside_begin',

            // 搜索页面搜索导航条内尾部钩子
            'plugins_view_search_nav_inside_end',

            // 搜索页面筛选条件内前面钩子
            'plugins_view_search_map_inside_begin',

            // 搜索页面筛选条件内基础底部钩子
            'plugins_view_search_map_inside_base_bottom',

            // 搜索页面筛选条件内尾部钩子
            'plugins_view_search_map_inside_end',
        ];
        $assign = [];
        foreach($hook_arr as $hook_name)
        {
            $assign[$hook_name.'_data'] = MyEventTrigger($hook_name,
                [
                    'hook_name'    => $hook_name,
                    'is_backend'   => false,
                ]);
        }
        MyViewAssign($assign);
    }
    
}
?>