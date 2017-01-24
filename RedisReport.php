<?php

namespace B2\Log\Access;

// Need for |geoip_flag in template
require_once BORS_CORE.'/inc/clients/geoip-place.php';

class RedisReport extends \bors_admin_page
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

//		dump($client->lRange(($time_id).':access_log', 0, -1));
//		$log = json_decode($client->lRange(($time_id-1).':access_log', 0, -1), true);
//		$log = array_merge($log, json_decode($client->lRange($time_id.':access_log', 0, -1), true));


		$top_classes = [];
		foreach($log as $x)
		{
			$x = json_decode($x, true);
			if(empty($top_classes[$x['object_class_name']]))
				$top_classes[$x['object_class_name']] = ['operation_time' => 0, 'count' => 0];

			$top_classes[$x['object_class_name']]['operation_time'] += $x['operation_time'];
			$top_classes[$x['object_class_name']]['count']++;
		}

		uasort($top_classes, function($a, $b) { return $a['operation_time'] - $b['operation_time'];});

		dump($top_classes);


		return array_merge(parent::body_data(), [
			'top_classes' => $top_classes,
		]);
	}
}
