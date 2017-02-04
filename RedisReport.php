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
		$top_users = [];

		foreach($log as $x)
		{
			$x = json_decode($x, true);

//			dump($x);

//	uid => "192_168_1_3" (11)
//	user_ip => "192.168.1.3" (11)
//	server_uri => "http://dev.hyper16.home.balancer.ru/favicon.ico" (47)
//	referrer => "http://dev.hyper16.home.balancer.ru/" (36)
//	access_time => 1486248567
//	operation_time => 0.02875518798828125
//	user_agent => "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36 OPR/42.0.2393.517" (122)
//	object_class_name => "http://dev.hyper16.home.balancer.ru/favicon.ico" (47)

			$uid = @$x['uid'];
			if(empty($top_users[$uid]))
				$top_users[$uid] = ['operation_time' => 0, 'count' => 0];

			$top_users[$uid]['operation_time'] += $x['operation_time'];
			$top_users[$uid]['count']++;

			if(empty($top_classes[$x['object_class_name']]))
				$top_classes[$x['object_class_name']] = ['operation_time' => 0, 'count' => 0];

			$top_classes[$x['object_class_name']]['operation_time'] += $x['operation_time'];
			$top_classes[$x['object_class_name']]['count']++;
		}

		uasort($top_users, function($a, $b) { return $a['operation_time'] > $b['operation_time'] ? -1 : 1;});
		$top_users = array_slice($top_users, 0, 20);

		uasort($top_classes, function($a, $b) { return $a['operation_time'] > $b['operation_time'] ? -1 : 1;});
		$top_classes = array_slice($top_classes, 0, 20);


//		dump($top_classes);

		return array_merge(parent::body_data(), [
			'top_classes' => $top_classes,
			'top_users' => $top_users,
		]);
	}
}
