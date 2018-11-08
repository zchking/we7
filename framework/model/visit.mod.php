<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */
defined('IN_IA') or exit('Access Denied');


/**
 * 更新今日访问信息
 * @param string $type 更新类型：web:后端、app:手机端、api:微信api
 * @param string $module_name 模块名
 * @return boolean
 */
function visit_update_today($type, $module_name = '') {
	global $_W;
	$module_name = trim($module_name);
	$type = trim($type);
	if (empty($type) || !in_array($type, array('app', 'web', 'api'))) {
		return false;
	}
	if ($type == 'app' && empty($module_name)) {
		return false;
	}

	$today = date('Ymd');
	$params = array('date' => $today, 'uniacid' => $_W['uniacid'], 'module' => $module_name, 'type' => $type);
	$today_exist = table('stat_visit')->visitList($params, 'one');
	if (empty($today_exist)) {
		$insert_data = array(
			'uniacid' => $_W['uniacid'],
			'module' => $module_name,
			'type' => $type,
			'date' => $today,
			'count' => 1
		);
		pdo_insert('stat_visit', $insert_data);
	} else {
		$data = array('count' => $today_exist['count'] + 1);
		pdo_update('stat_visit' , $data, array('id' => $today_exist['id']));
	}

	return true;
}

/**
 * 访问uniacid或者module的记录或者自己设置置顶的功能($displayorder=true)
 * @param $system_stat_visit
 * @param bool $displayorder
 * @return bool
 */
function visit_system_update($system_stat_visit, $displayorder = false) {
	global $_W;
	load()->model('user');
	load()->model('account');
	if (user_is_founder($_W['uid'])) {
		return true;
	}

	if (empty($system_stat_visit['uniacid']) && empty($system_stat_visit['modulename'])) {
		return true;
	}
	if (empty($system_stat_visit['uid'])) {
		return true;
	}

	$condition['uid'] = $_W['uid'];
	if (!empty($system_stat_visit['uniacid'])) {
		$own_uniacid = uni_owned($_W['uid'], false);
		$uniacids = !empty($own_uniacid) ? array_keys($own_uniacid) : array();
		if (empty($uniacids) || !in_array($system_stat_visit['uniacid'], $uniacids)) {
			return true;
		}
		$condition['uniacid'] = $system_stat_visit['uniacid'];
	}

	if (!empty($system_stat_visit['modulename'])) {
		$user_modules = user_modules($_W['uid']);
		$modules = !empty($user_modules) ? array_keys($user_modules) : array();
		if (empty($modules) || !in_array($system_stat_visit['modulename'], $modules)) {
			return true;
		}
		$condition['modulename'] = $system_stat_visit['modulename'];
	}
	$system_stat_info = pdo_get('system_stat_visit', $condition);

	if (empty($system_stat_info['createtime'])) {
		$system_stat_visit['createtime'] = TIMESTAMP;
	}

	if (empty($system_stat_visit['updatetime'])) {
		$system_stat_visit['updatetime'] = TIMESTAMP;
	}

	if (!empty($displayorder)) {
		$system_stat_max_order = pdo_fetchcolumn("SELECT MAX(displayorder) FROM " . tablename('system_stat_visit') . " WHERE uid = :uid", array(':uid' => $_W['uid']));
		$system_stat_visit['displayorder'] = ++$system_stat_max_order;
	}

	if (empty($system_stat_info)) {
		pdo_insert('system_stat_visit', $system_stat_visit);
	} else {
		$system_stat_visit['updatetime'] = TIMESTAMP;
		pdo_update('system_stat_visit', $system_stat_visit, array('id' => $system_stat_info['id']));
	}
	return true;
}


/**
 * 根据uid删除用户没有权限的访问统计模块
 * @param $uid
 * @return bool
 */
function visit_system_delete($uid) {
	load()->model('user');
	$user_modules = user_modules($uid);
	$modules = !empty($user_modules) ? array_keys($user_modules) : array();

	$old_modules = table('system_stat_visit')->getVistedModule($uid);
	if (empty($old_modules)) {
		return true;
	}

	$old_modules = array_column($old_modules, 'modulename');
	$delete_modules = array_diff($old_modules, $modules);

	if (!empty($modules)) {
		table('system_stat_visit')->deleteVisitRecord($uid, $delete_modules);
		return true;
	}
	table('system_stat_visit')->deleteVisitRecord($uid);
	return true;
}