<?php
/**
 * 站点相关操作
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

$dos = array('copyright');
$do = in_array($do, $dos) ? $do : 'copyright';
$_W['page']['title'] = '站点设置 - 工具  - 系统管理';
$settings = $_W['setting']['copyright'];

if(empty($settings) || !is_array($settings)) {
	$settings = array();
} else {
	$settings['slides'] = iunserializer($settings['slides']);
}

if ($do == 'copyright') {
	
	if (checksubmit('submit')) {
		
		
			$data = array(
				'status' => $_GPC['status'],
				'reason' => $_GPC['reason'],
				'icp' => safe_gpc_string($_GPC['icp']),
				'mobile_status' => $settings['mobile_status'],
				'login_type' => $settings['login_type'],
				'log_status' => intval($_GPC['log_status']),
				'develop_status' => intval($_GPC['develop_status']),
				'bind' => $settings['bind'],
				'welcome_link' => $settings['welcome_link'],
			);
		

		$test = setting_save($data, 'copyright');

		

		itoast('更新设置成功！', url('system/site'), 'success');
	}
}

template('system/site');