<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
load()->classs('account/wxapp.account');

class WxappWork extends WxappAccount {
	public function getAccessToken() {
		$cachekey = "accesstoken:{$this->account['key']}";
		$cache = cache_load($cachekey);
		if (!empty($cache) && !empty($cache['token']) && $cache['expire'] > TIMESTAMP) {
			$this->account['access_token'] = $cache;
			//return $cache['token'];
		}

		if (empty($this->account['key']) || empty($this->account['secret'])) {
			return error('-1', '未填写小程序的 appid 或 appsecret！');
		}
		
		$url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid={$this->account['key']}&corpsecret={$this->account['secret']}";
		$response = $this->requestApi($url);
		$record = array();
		$record['token'] = $response['access_token'];
		$record['expire'] = TIMESTAMP + $response['expires_in'] - 200;
		
		$this->account['access_token'] = $record;
		cache_write($cachekey, $record);
		return $record['token'];
	}

	public function getOauthInfo($code = '') {
		global $_W, $_GPC;
		if (!empty($_GPC['code'])) {
			$code = $_GPC['code'];
		}
		$token = $this->getAccessToken();
		$url = "https://qyapi.weixin.qq.com/cgi-bin/miniprogram/jscode2session?access_token={$token}&js_code={$code}&grant_type=authorization_code";
		$response = $this->requestApi($url);
		//企业微信里只返回userid
		$response['openid'] = $response['userid'];
		//需要判断当前用户所属的cropid在表中的uniacid
		
		return $response;
	}
}