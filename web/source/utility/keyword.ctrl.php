<?php
/**
 * 自动回复公共组建（关键字）
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
error_reporting(0);
if (!in_array($do, array('keyword'))) {
	exit('Access Denied');
}

if($do == 'keyword') {
	$type = trim($_GPC['type']);

	$condition = array('uniacid' => $_W['uniacid'], 'status' => 1);
	if ($type != 'all') {
		$condition = array('uniacid' => $_W['uniacid'], 'status' => 1, 'module' => $type);
	}

	$pindex = max(1, intval($_GPC['page']));
	$psize = 24;

	$rule_keyword = pdo_getslice('rule_keyword', $condition, array($pindex, $psize), $total, array(), 'id');
	$result = array(
		'items' => $rule_keyword,
		'pager' => pagination($total, $pindex, $psize, '', array('before' => '2', 'after' => '3', 'ajaxcallback'=>'null')),
	);
	iajax(0, $result);
}