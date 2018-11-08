<?php
/**
 * 退款参数配置
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('payment');
load()->classs('uploadedfile');

$dos = array('save_setting', 'display');
$do = in_array($do, $dos) ? $do : 'display';
permission_check_account_user('profile_payment_refund', true, 'wxapp');
$_W['page']['title'] = '退款参数 - 小程序选项';

if ($do == 'display') {
	$setting = uni_setting_load('payment', $_W['uniacid']);
	$setting = $setting['payment'];
	if (empty($setting['wechat_refund'])) {
		$setting['wechat_refund'] = array('switch' => 0, 'key' => '', 'cert' => '');
	}
	$has_cert = !empty($setting['wechat_refund']['cert']);//是否已设置过证书
	$has_key = !empty($setting['wechat_refund']['key']);// 是否已设置过私钥
	$open_or_close = !empty($setting['wechat_refund']['switch']);// 是否开启

}

if ($do == 'save_setting') {
	$type = $_GPC['type'];
	$is_open = $_GPC['switch'] == '1' ? 1 : 0;
	$setting = uni_setting_load('payment', $_W['uniacid']);
	$pay_setting = $setting['payment'];

	$files = UploadedFile::createFromGlobal();
	$cert = isset($files['cert']) ? $files['cert'] : null;//是否有上传证书
	$private_key = isset($files['key']) ? $files['key'] : null;//是否有上传私钥
	$cert_content = $pay_setting['wechat_refund']['cert'];//是否已设置过证书
	$private_key_content = $pay_setting['wechat_refund']['key'];// 是否已设置过私钥
	$open_or_close = !empty($pay_setting['wechat_refund']['switch']);// 是否开启

//	var_dump($pay_setting);
//	exit;

	/* @var $cert UploadedFile */
	if($cert && $cert->isOk()) { //是否有上传公钥
		$cert_content = $cert->getContent();
//		if (strexists($cert_content, '<?php') || substr($cert_content, 0, 27) != '-----BEGIN CERTIFICATE-----' || substr($cert_content, -24, 23) != '---END CERTIFICATE-----') {
//			itoast('apiclient_cert.pem证书内容不合法，请重新上传');
////			exit('cert');
//		}
		$cert_content = authcode($cert_content, 'ENCODE');
	}
		/* @var $key UploadedFile */
	if($private_key && $private_key->isOk()) { //是否有上传私钥
		$key_content = $private_key->getContent();
//		if (strexists($key_content, '<?php') || substr($key_content, 0, 27) != '-----BEGIN PRIVATE KEY-----' || substr($key_content, -24, 23) != '---END PRIVATE KEY-----') {
//			itoast('apiclient_key.pem证书内容不合法，请重新上传', 'refresh');
//		}
		$private_key_content = authcode($key_content, 'ENCODE');
	}
	if($is_open) {
		if(! $cert_content) {
			itoast('请上传apiclient_cert.pem证书', '', 'info');
		}

		if(! $private_key_content) {
			itoast ('请上传apiclient_key.pem证书', '', 'info');
		}
	}

	$pay_setting['wechat_refund'] = array('cert'=>$cert_content,
		'key'=>$private_key_content, 'switch'=>$is_open, 'version'=>1); //version 1 使用微信的退款refund.php接口

	uni_setting_save('payment', $pay_setting);
	itoast('设置成功', '', 'success');
}

template('wxapp/refund');