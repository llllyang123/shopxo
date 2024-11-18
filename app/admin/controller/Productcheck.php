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
namespace app\admin\controller;

use app\admin\controller\Base;
use app\service\ApiService;
use app\service\ProductCheckService;
use think\facade\Db;

/**
 * 角色管理
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class ProductCheck extends Common
{
    	/**
     * 构造方法
     * @author  Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2021-03-03
     * @desc    description
     */
	public function __construct()
	{
		// 调用父类前置方法
		parent::__construct();

		// 需要校验权限
		if(!in_array($this->action_name, ['logininfo', 'login', 'logout', 'adminverifyentry', 'loginverifysend']))
		{
			// 登录校验
            $this->IsLogin();

            // 权限校验
            $this->IsPower();

            // 动态表格初始化
            $this->FormTableInit();
		}
	}
	
    /**
     * 列表
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-14T21:37:02+0800
     */
    public function Index()
    {
        return MyView();
    }

    /**
     * 详情
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2019-08-05T08:21:54+0800
     */
    public function Detail()
    {
        return MyView();
    }

    /**
     * 添加/编辑页面
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-14T21:37:02+0800
     */
    public function SaveInfo()
    {
        // 登录校验
		$this->IsLogin();

		// 参数
		$params = $this->data_request;

		// 不是操作自己的情况下
		if(!isset($params['id']) || $params['id'] != $this->admin['id'])
		{
			// 权限校验
			$this->IsPower();
		}

		// 数据
		$data = $this->data_detail;
		if(!empty($params['id']))
		{
			if(empty($data))
			{
				return ViewError(MyLang('admin.admin_no_data_tips'), MyUrl('admin/index/index'));
			}
		}

		// 模板数据
		$assign = [
			'id' 						=> isset($params['id']) ? $params['id'] : 0,
			'common_gender_list' 		=> MyConst('common_gender_list'),
			'common_pay_log_status_list'	=> MyConst('common_pay_log_status_list'),
		];

// 		// 角色
// 		$role_params = [
// 			'where'		=> [
// 				['is_enable', '=', 1],
// 			],
// 			'field'		=> 'id,name',
// 		];
// 		$role = ProductCheckService::RoleList($role_params);
// 		$assign['role_list'] = $role['data'];

		// 管理员编辑页面钩子
        $hook_name = 'plugins_view_admin_admin_save';
        $assign[$hook_name.'_data'] = MyEventTrigger($hook_name,
        [
            'hook_name'     => $hook_name,
            'is_backend'    => true,
            'admin_id'      => isset($params['id']) ? $params['id'] : 0,
            'data'          => &$data,
            'params'        => &$params,
        ]);

        // 数据
        unset($params['id']);
        $assign['data'] = $data;
        $assign['params'] = $params;

        // 数据赋值
        MyViewAssign($assign);
		return MyView();
    }
    
    /**
     * 审批通过操作
     * 
     * 
     */
    public function Check()
    {
        // 参数
		$params = $this->data_request;
		if(empty($params)){
		    return DataReturn(MyLang('login_close_tips'), -1);
		}
		$data = DB::name('Admin_tenants')->where('id',$params['id'])->find();
		if(empty($data)){
		    return DataReturn(MyLang('login_close_tips'), -1);
		} else {
		    DB::name('Admin_tenants')->where('id',$params['id'])->update(['status' => 0]);
		}
		return ApiService::ApiDataReturn(DataReturn(MyLang('success'), 0));
        return DataReturn(MyLang('success'), 0);
    }

    /**
     * 删除
     * @author   Devil
     */
    // public function Delete()
    // {
    //     return ApiService::ApiDataReturn(ProductCheckService::RoleDelete($this->data_request));
    // }

    /**
     * 状态更新
     * @author   Devil
     */
    // public function StatusUpdate()
    // {
    //     $params = $this->data_request;
    //     $params['admin'] = $this->admin;
    //     return ApiService::ApiDataReturn(ProductCheckService::RoleStatusUpdate($params));
    // }
}
?>