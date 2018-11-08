<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
namespace We7\Table\Qrcode;

class Qrcode extends \We7Table {
	protected $tableName = 'qrcode';
	protected $primaryKey = 'id';
	protected $field = array(
		'uniacid',
		'acid',
		'type',
		'extra',
		'qrcid',
		'scene_str',
		'name',
		'keyword',
		'model',
		'ticket',
		'url',
		'expire',
		'subnum',
		'createtime',
		'status',
	);
	protected $default = array(
		'uniacid' => '',
		'acid' => 0,
		'type' => scene,
		'extra' => 0,
		'qrcid' => 0,
		'scene_str' => '',
		'name' => '',
		'keyword' => '',
		'model' => 0,
		'ticket' => '',
		'url' => '',
		'expire' => 0,
		'subnum' => 0,
		'createtime' => 0,
		'status' => 0,
	);

	public function searchTime($start_time, $end_time) {
		$this->query->where('createtime >=', $start_time)->where('createtime <=', $end_time);
		return $this;
	}

	public function searchKeyword($keyword) {
		$this->query->where('name LIKE', "%{$keyword}%");
		return $this;
	}

	public function qrcodeStaticList($status) {
		global $_W;
		$this->query->from('qrcode_stat')->where('uniacid', $_W['uniacid'])->where('acid', $_W['acid']);
		if (!empty($status)) {
			$this->query->groupby('qid');
			$this->query->groupby('openid');
			$this->query->groupby('type');
		}
		$this->query->orderby('createtime', 'DESC');
		return $this->query->getall();
	}

	public function qrcodeCount($status) {
		global $_W;
		$this->query->from('qrcode_stat')->select('count(*) as count')->where('uniacid', $_W['uniacid'])->where('acid', $_W['acid']);
		if (!empty($status)) {
			$this->query->groupby('qid');
			$this->query->groupby('openid');
			$this->query->groupby('type');
		}
		$count = $this->query->getall();
		if ($status) {
			return count($count);
		}
		return $count[0]['count'];
	}
}