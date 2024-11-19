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

use think\facade\Db;
use app\admin\controller\Base;
use app\service\ApiService;
use app\service\ConfigService;

/**
 * 邮箱设置
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Promotion extends Base
{
	/**
     * 列表
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-06T21:31:53+0800
     */
	public function Index()
	{
		// 导航
		$type = empty($this->data_request['type']) ? 'index' : $this->data_request['type'];
		$assign = [
			// 配置信息
			'data'      => ConfigService::ConfigList(),
			// 管理导航
			'nav_data'  => MyLang('email.base_nav_list'),
			// 页面导航
			'type'      => $type,
		];
		MyViewAssign($assign);
		return MyView($type);
	}

	/**
	 * 保存
	 */
	public function Save()
	{
		return ApiService::ApiDataReturn(ConfigService::ConfigSave($_POST));
	}
	
	/**
	 * 邮件发送
	 */
	public function EmailSend()
	{
	    // 发送验证码
        $code = GetNumberCode(4);
		// 验证码公共基础参数
		$verify_params = [
			'expire_time' 	=> MyC('common_verify_expire_time'),
			'interval_time'	=>	MyC('common_verify_interval_time'),
		];
		$obj = new \base\Email($verify_params);
		$content = Db::name('config')->where('only_tag', 'home_email_promotion_template')->find();
		$email_params = [
			'email'		=>	isset($this->data_request['email']) ? $this->data_request['email'] : '',
			'content'	=>	!empty($content) ? $content['value'] : 'welcome web',
			'title'		=>	MyC('home_site_name').' - '.MyLang('email.test_title'),
			'code'      =>  $code,
		];
		// 发送
		if($obj->SendHtml($email_params, 'PromotionalEmailLog'))
		{
			$ret = DataReturn(MyLang('send_success'), 0);
		} else {
			$ret = DataReturn(MyLang('push_fail').'('.$obj->error.')', -100);
		}
		return ApiService::ApiDataReturn($ret);
	}
	
	/**
	 * 给所有用户邮件发送
	 */
	public function EmailSendAll()
	{
	    // 发送验证码
        $code = GetNumberCode(4);
		// 验证码公共基础参数
		$verify_params = [
			'expire_time' 	=> MyC('common_verify_expire_time'),
			'interval_time'	=>	MyC('common_verify_interval_time'),
		];
		$obj = new \base\Email($verify_params);
		$content = Db::name('config')->where('only_tag', 'home_email_promotion_template')->find();
		$email_params = [
			'email'		=>	isset($this->data_request['email']) ? $this->data_request['email'] : '',
			'content'	=>	!empty($content) ? $content['value'] : 'welcome web',
			'title'		=>	"你好，欢迎来我们这里购物",
			'code'      =>  $code,
		];
		$user_list = Db::name('user')->select()->toArray();

	    $email_list = [];
	    foreach ($user_list as $k=>$v){
		    if(!empty($v['email'])){
		        array_push($email_list, $v['email']);
		    }
		}
		$email_params['email'] = $email_list;
        // 发送
		if($obj->SendHtml($email_params, 'PromotionalEmailLog'))
		{
			$ret = DataReturn(MyLang('send_success'), 0);
		} else {
			$ret = DataReturn(MyLang('push_fail').'('.$obj->error.')', -100);
		}
		return ApiService::ApiDataReturn($ret);
	}

// 	/**
// 	 * 邮件测试
// 	 */
// 	public function EmailTest()
// 	{
// 		// 验证码公共基础参数
// 		$verify_params = [
// 			'expire_time' 	=> MyC('common_verify_expire_time'),
// 			'interval_time'	=>	MyC('common_verify_interval_time'),
// 		];
// 		$obj = new \base\Email($verify_params);
// 		$email_params = [
// 			'email'		=>	isset($this->data_request['email']) ? $this->data_request['email'] : '',
// 			'content'	=>	MyLang('email.test_content'),
// 			'title'		=>	MyC('home_site_name').' - '.MyLang('email.test_title'),
// 		];
// 		// 发送
// 		if($obj->SendHtml($email_params))
// 		{
// 			$ret = DataReturn(MyLang('send_success'), 0);
// 		} else {
// 			$ret = DataReturn(MyLang('push_fail').'('.$obj->error.')', -100);
// 		}
// 		return ApiService::ApiDataReturn($ret);
// 	}
}
?>