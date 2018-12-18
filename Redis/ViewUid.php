<?php

namespace B2\Log\Access\Redis;

// Need for |geoip_flag in template
require_once BORS_CORE.'/inc/clients/geoip-place.php';

class ViewUid extends \bors_admin_page
{
	function title() { return _('Загрузка системы на ').date('d.m.Y H:i'); }

	function body_data()
	{
		$time_id		= str_replace(",",".",floor(time()/600));

		$cfg_srv = \B2\Cfg::get('predis.servers');

		$client = new \Predis\Client($cfg_srv, [
			'prefix' => 'bors:access_log:',
		]);

		$log = $client->lRange(($time_id-1).':access_log', 0, -1);
		$log = array_merge($log, $client->lRange($time_id.':access_log', 0, -1));

		$top_classes = [];
		$top_requests = [];

		$total_time = 0;
		$total_requests = 0;
		$start_time = time();

		foreach($log as $x)
		{
			$x = json_decode($x, true);

			if($x['uid'] != $this->id())
				continue;

			$total_time += @$x['operation_time'];
			$total_requests++;
			if($x['access_time'] && $x['access_time'] < $start_time)
				$start_time = $x['access_time'];
			if($x['access_time'])
				\B2\Debug::syslog('warning-logging-time', "Undefined access time: ".print_r($x, true));

			$uid = @$x['uid'];

			if(empty($top_classes[$x['object_class_name']]))
				$top_classes[$x['object_class_name']] = ['operation_time' => 0, 'count' => 0];

			$top_classes[$x['object_class_name']]['operation_time'] += $x['operation_time'];
			$top_classes[$x['object_class_name']]['count']++;

			if(@$top_classes[$x['object_class_name']]['uri'] < $x['server_uri'])
				$top_classes[$x['object_class_name']]['uri'] = $x['server_uri'];

			if(@$top_classes[$x['object_class_name']]['referer'] < @$x['referer'])
				$top_classes[$x['object_class_name']]['referer'] = @$x['referer'];


			$top_requests[] = $x;
		}

		uasort($top_classes, function($a, $b) { return $a['operation_time'] > $b['operation_time'] ? -1 : 1;});
		$top_classes = array_slice($top_classes, 0, 50);

		uasort($top_requests, function($a, $b) { return $a['operation_time'] > $b['operation_time'] ? -1 : 1;});
		$top_requests = array_slice($top_requests, 0, 100);

		return array_merge(parent::body_data(), [
			'top_classes' => $top_classes,
			'top_requests' => $top_requests,
			'total_time' => $total_time,
			'total_requests' => $total_requests,
			'start_time' => $start_time,
		]);
	}
}
