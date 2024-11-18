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
namespace app\service_tenants;

use think\facade\Db;
use app\service\AdminPowerService;
use app\service\PluginsAdminService;

/**
 * 角色服务层
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class ProductCheckService
{
    /**
     * 角色列表
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-06T21:31:53+0800
     * @param    [array]          $params [输入参数]
     */
    public static function RoleList($params = [])
    {
        $where = empty($params['where']) ? [] : $params['where'];
        $field = empty($params['field']) ? '*' : $params['field'];
        $data = Db::name('Role')->field($field)->where($where)->select()->toArray();
        return DataReturn(MyLang('handle_success'), 0, $data);
    }

    public static function AdminSave($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'admin',
                'error_msg'         => MyLang('common_service.admin.save_admin_info_error_tips'),
            ],
            [
                'checked_type'      => 'fun',
                'key_name'          => 'mobile',
                'checked_data'      => 'CheckMobile',
                'is_checked'        => 1,
                'error_msg'         => MyLang('common_service.admin.form_item_mobile_message'),
            ],
            [
                'checked_type'      => 'fun',
                'key_name'          => 'email',
                'checked_data'      => 'CheckEmail',
                'is_checked'        => 1,
                'error_msg'         => MyLang('common_service.admin.form_item_email_message'),
            ],
            [
                'checked_type'      => 'in',
                'key_name'          => 'gender',
                'checked_data'      => [0,1,2],
                'error_msg'         => MyLang('common_service.admin.save_gender_tips'),
            ],
            [
                'checked_type'      => 'in',
                'key_name'          => 'status',
                'checked_data'      => array_column(MyConst('common_tenants_status_list'), 'value'),
                'error_msg'         => MyLang('common_service.admin.save_status_tips'),
            ],
            [
                'checked_type'      => 'unique',
                'key_name'          => 'mobile',
                'checked_data'      => 'Admin',
                'checked_key'       => 'id',
                'is_checked'        => 1,
                'error_msg'         => MyLang('common_service.admin.save_mobile_already_exist_tips'),
            ],
            [
                'checked_type'      => 'unique',
                'key_name'          => 'email',
                'checked_data'      => 'Admin',
                'checked_key'       => 'id',
                'is_checked'        => 1,
                'error_msg'         => MyLang('common_service.admin.save_email_already_exist_tips'),
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }
        return empty($params['id']) ? self::AdminInsert($params) : self::AdminUpdate($params);
    }
    
    public static function AdminInsert($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'username',
                'error_msg'         => MyLang('common_service.admin.form_item_username_placeholder'),
            ],
            [
                'checked_type'      => 'fun',
                'key_name'          => 'username',
                'checked_data'      => 'CheckUserName',
                'error_msg'         => MyLang('common_service.admin.form_item_username_message'),
            ],
            [
                'checked_type'      => 'unique',
                'key_name'          => 'username',
                'checked_data'      => 'Admin',
                'checked_key'       => 'id',
                'error_msg'         => MyLang('common_service.admin.save_admin_already_exist_tips'),
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'login_pwd',
                'error_msg'         => MyLang('common_service.admin.form_item_password_placeholder'),
            ],
            [
                'checked_type'      => 'fun',
                'key_name'          => 'login_pwd',
                'checked_data'      => 'CheckLoginPwd',
                'error_msg'         => MyLang('common_service.admin.form_item_password_message'),
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'role_id',
                'error_msg'         => MyLang('common_service.admin.form_item_role_message'),
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 添加账号
        $salt = GetNumberCode(6);
        $data = [
            'username'      => $params['username'],
            'login_salt'    => $salt,
            'login_pwd'     => LoginPwdEncryption($params['login_pwd'], $salt),
            'mobile'        => empty($params['mobile']) ? '' : $params['mobile'],
            'email'         => empty($params['email']) ? '' : $params['email'],
            'gender'        => intval($params['gender']),
            'status'        => intval($params['status']),
            'role_id'       => intval($params['role_id']),
            'add_time'      => time(),
        ];

        // 添加
        if(Db::name('Admin')->insert($data) > 0)
        {
            return DataReturn(MyLang('insert_success'), 0);
        }
        return DataReturn(MyLang('insert_fail'), -100);
    }
    
    public static function AdminUpdate($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'fun',
                'key_name'          => 'login_pwd',
                'checked_data'      => 'CheckLoginPwd',
                'is_checked'        => 1,
                'error_msg'         => MyLang('common_service.admin.form_item_password_message'),
            ],
        ];
        if($params['id'] != $params['admin']['id'])
        {
            $p[] = [
                'checked_type'      => 'empty',
                'key_name'          => 'role_id',
                'error_msg'         => MyLang('common_service.admin.form_item_role_message'),
            ];
        }
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 是否非法修改超管
        if($params['id'] == 1 && $params['id'] != $params['admin']['id'])
        {
            return DataReturn(MyLang('illegal_operate_tips'), -1);
        }

        // 数据
        $data = [
            'mobile'        => empty($params['mobile']) ? '' : $params['mobile'],
            'email'         => empty($params['email']) ? '' : $params['email'],
            'gender'        => intval($params['gender']),
            'status'        => intval($params['status']),
            'upd_time'      => time(),
        ];

        // 密码
        if(!empty($params['login_pwd']))
        {
            $data['login_salt'] = GetNumberCode(6);
            $data['login_pwd'] = LoginPwdEncryption($params['login_pwd'], $data['login_salt']);
        }
        // 不能修改自身所属角色组
        if($params['id'] != $params['admin']['id'])
        {
            $data['role_id'] = intval($params['role_id']);
        }

        // 更新
        if(Db::name('Admin')->where(['id'=>intval($params['id'])])->update($data))
        {
            // 自己修改密码则重新登录
            if(!empty($params['login_pwd']) && $params['id'] == $params['admin']['id'])
            {
                self::LoginLogout();
            }
            
            return DataReturn(MyLang('edit_success'), 0);
        }
        return DataReturn(MyLang('edit_fail'), -100);
    }
    
    public static function AdminDelete($params = [])
    {
        // 参数是否有误
        if(empty($params['ids']))
        {
            return DataReturn(MyLang('data_id_error_tips'), -1);
        }
        // 是否数组
        if(!is_array($params['ids']))
        {
            $params['ids'] = explode(',', $params['ids']);
        }

        // 是否包含删除超级管理员
        if(in_array(1, $params['ids']))
        {
            return DataReturn(MyLang('common_service.admin.delete_super_admin_not_tips'), -1);
        }
           
        // 删除操作
        if(Db::name('Admin')->where(['id'=>$params['ids']])->delete())
        {
            return DataReturn(MyLang('delete_success'), 0);
        }
        return DataReturn(MyLang('delete_fail'), -100);
    }

}
?>