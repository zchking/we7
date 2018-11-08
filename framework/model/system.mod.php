<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

/**
 * 获取包括系统及模块所有的菜单权限
 *
 */
function system_menu_permission_list($role = '') {
	global $_W;
	$system_menu = cache_load(cache_system_key('system_frame', array('uniacid' => $_W['uniacid'])));
	if(empty($system_menu)) {
		cache_build_frame_menu();
		$system_menu = cache_load(cache_system_key('system_frame', array('uniacid' => $_W['uniacid'])));
	}
	//根据不同的角色得到不同的菜单权限
	if ($role == ACCOUNT_MANAGE_NAME_OPERATOR) {
		unset($system_menu['appmarket']);
		unset($system_menu['advertisement']);
		unset($system_menu['system']);
	}
	return $system_menu;
}
/**
 * 获得数据库备份目录下的数据库备份文件数组
 * @return array;
 */
function system_database_backup() {
	$path = IA_ROOT . '/data/backup/';
	load()->func('file');
	$reduction = array();
	if (!is_dir($path)) {
		return array();
	}
	if ($handle = opendir($path)) {
		while (false !== ($bakdir = readdir($handle))) {
			if ($bakdir == '.' || $bakdir == '..') {
				continue;
			}
			$times[] = date("Y-m-d H:i:s", filemtime($path.$bakdir));
			if (preg_match('/^(?P<time>\d{10})_[a-z\d]{8}$/i', $bakdir, $match)) {
				$time = $match['time'];
				if ($handle1= opendir($path . $bakdir)) {
					while (false !== ($filename = readdir($handle1))) {
						if ($filename == '.' || $filename == '..') {
							continue;
						}
						if (preg_match('/^volume-(?P<prefix>[a-z\d]{10})-\d{1,}\.sql$/i', $filename, $match1)) {
							$volume_prefix = $match1['prefix'];
							if (!empty($volume_prefix)) {
								break;
							}
						}
					}
				}
				$volume_list = array();
				for ($i = 1;;) {
					$last = $path . $bakdir . "/volume-{$volume_prefix}-{$i}.sql";
					array_push($volume_list, $last);
					$i++;
					$next = $path . $bakdir . "/volume-{$volume_prefix}-{$i}.sql";
					if (!is_file($next)) {
						break;
					}
				}
				if (is_file($last)) {
					$fp = fopen($last, 'r');
					fseek($fp, -27, SEEK_END);
					$end = fgets($fp);
					fclose($fp);
					if ($end == '----WeEngine MySQL Dump End') {
						$row = array(
							'bakdir' => $bakdir,
							'time' => $time,
							'volume' => $i - 1,
							'volume_list' => $volume_list,
						);
						$reduction[$bakdir] = $row;
						continue;
					}
				}
			}
			rmdirs($path . $bakdir);
		}
		closedir($handle);
	}
	if (!empty($times)) {
		array_multisort($times, SORT_DESC, SORT_STRING, $reduction);
	}
	return $reduction;
}
/**
 * 得到备份文件下一卷文件名
 * @param string $volume_name 卷文件名
 * @return mixed;
 */
function system_database_volume_next($volume_name) {
	$next_volume_name = '';
	if (!empty($volume_name) && preg_match('/^([^\s]*volume-(?P<prefix>[a-z\d]{10})-)(\d{1,})\.sql$/i', $volume_name, $match)) {
		$next_volume_name = $match[1] . ($match[3] + 1) . ".sql";
	}
	return $next_volume_name;
}
/**
 * 还原数据库备份目录下的某个备份目录下的一卷数据
 * @param string $volume_name 卷文件名
 * @return bolean;
 */
function system_database_volume_restore($volume_name) {
	if (empty($volume_name) || !is_file($volume_name)) {
		return false;
	}
	$sql = file_get_contents($volume_name);
	pdo_run($sql);
	return true;
}
/**
 * 删除数据库备份目录下的某个备份数据
 * @param string 备份目录
 * @return boolean;
 */
function system_database_backup_delete($delete_dirname) {
	$path = IA_ROOT . '/data/backup/';
	$dir = $path . $delete_dirname;
	if (empty($delete_dirname) || !is_dir($dir)) {
		return false;
	}
	return rmdirs($dir);
}

/**
 * 系统后台风格中文名字
 */
function system_template_ch_name() {
	$result = array(
		'default' => '白色',
		'black' => '黑色',
		'classical' => '经典',
	);
	return $result;
}

/**
 * 获取系统注册站点信息
 */
function system_site_info() {
	load()->classs('cloudapi');

	$api = new CloudApi();
	$site_info = $api->get('site', 'info');
	return $site_info;
}

/**
 * 第三方统计代码验证
 * @param $statcode
 * @return mixed|null|string|string[]
 */
function system_check_statcode($statcode) {
	$allowed_stats = array(
		'baidu' => array(
			'enabled' => true,
			'reg' => '/(http[s]?\:)?\/\/hm\.baidu\.com\/hm\.js\?/'
		),

		'qq' => array(
			'enabled' => true,
			'reg' => '/(http[s]?\:)?\/\/tajs\.qq\.com/'
		),
	);
	foreach($allowed_stats as $key => $item) {
		$preg = preg_match($item['reg'], $statcode);
		if ($preg && $item['enabled']) {
			return htmlspecialchars_decode($statcode);
		} else {
			return safe_gpc_html(htmlspecialchars_decode($statcode));
		}
	}
}