<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn: pro/framework/model/utility.mod.php : v a80418cf2718 : 2014/09/16 01:07:43 : Gorden $
 */
defined('IN_IA') or exit('Access Denied');

/**
 * 检查验证码是否存在且正确
 * @param int $uniacid 统一公号
 * @param string $receiver 粉丝用户
 * @param string $code 验证码
 * @return boolean
 */
function code_verify($uniacid, $receiver, $code) {
	if (!is_numeric($receiver) || !is_numeric($code)) {
		return false;
	}
	$params = array('uniacid' => intval($uniacid), 'receiver' => $receiver, 'verifycode' => $code, 'createtime >' => (TIMESTRAP - 1800));
	$data = table('account')->getUniVerifycode($params);
	if(empty($data)) {
		return false;
	}
	return true;
}

/**
 * 把远程图片或者本地图片移动到新的位置重命名
 * @param $image_source_url
 * @param $image_destination_url
 * @return bool
 */
function utility_image_rename($image_source_url, $image_destination_url) {
	global $_W;
	load()->func('file');
	$image_source_url = str_replace(array("\0","%00","\r"),'',$image_source_url);
	if (empty($image_source_url) || !parse_path($image_source_url) || !file_is_image($image_source_url)) {
		return false;
	}
	if (!strexists($image_source_url, $_W['siteroot'])) {
		$img_local_path = file_remote_attach_fetch($image_source_url);
		if (is_error($img_local_path)) {
			return false;
		}
		$img_source_path = ATTACHMENT_ROOT . $img_local_path;
	} else {
		$img_local_path = substr($image_source_url, strlen($_W['siteroot']));
		$img_path_params = explode('/', $img_local_path);
		if ($img_path_params[0] != 'attachment') {
			return false;
		}
		$img_source_path = IA_ROOT . '/' . $img_local_path;
	}
	$result = copy($img_source_path, $image_destination_url);
	return $result;
}

/**
 * 检测/更新手机验证码错误次数
 * @param $uniacid
 * @param $receiver
 * @param string $verifycode
 * @return array
 */
function utility_smscode_verify($uniacid, $receiver, $verifycode = '') {
	$table = table('uni_verifycode');
	$verify_info = $table->getByReceiverVerifycode($uniacid, $receiver, $verifycode);

	if (empty($verify_info)) {
		$table->updateFailedCount($receiver);
		return error(-1, '短信验证码不正确');
	} else if ($verify_info['createtime'] + 120 < TIMESTAMP) {
		return error(-2, '短信验证码已过期，请重新获取');
	} else {
		return error(0, '短信验证码正确');
	}

}