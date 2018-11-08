<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
namespace We7\Table\Rule;

class Keyword extends \We7Table {
	protected $tableName = 'rule_keyword';
	protected $primaryKey = 'id';
	protected $field = array(
		'rid',
		'uniacid',
		'module',
		'content',
		'type',
		'displayorder',
		'status',
	);
	protected $default = array(
		'rid' => 0,
		'uniacid' => 0,
		'module' => '',
		'content' => '',
		'type' => 1,
		'displayorder' => 1,
		'status' => 1,
	);
}