<?php

namespace B2\Log\Access\Redis;

// Need for |geoip_flag in template
require_once BORS_CORE.'/inc/clients/geoip-place.php';

class ViewClass extends \bors_admin_page
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

		$top_users = [];
		$top_requests = [];

		$total_time = 0;
		$total_requests = 0;
		$start_time = time();

		$class = base64_decode($this->id());

		foreach($log as $x)
		{
			$x = json_decode($x, true);

			if($x['object_class_name'] != $class)
				continue;

//	uid => "192_168_1_3" (11)
//	user_ip => "192.168.1.3" (11)
//	server_uri => "http://dev.hyper16.home.balancer.ru/favicon.ico" (47)
//	referer => "http://dev.hyper16.home.balancer.ru/" (36)
//	access_time => 1486248567
//	operation_time => 0.02875518798828125
//	user_agent => "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36 OPR/42.0.2393.517" (122)
//	object_class_name => "http://dev.hyper16.home.balancer.ru/favicon.ico" (47)

			$total_time += @$x['operation_time'];
			$total_requests++;
			if($x['access_time'] && $x['access_time'] < $start_time)
				$start_time = $x['access_time'];
			if($x['access_time'])
				\B2\Debug::syslog('warning-logging-time', "Undefined access time: ".print_r($x, true));

			$uid = @$x['uid'];
			if(empty($top_users[$uid]))
				$top_users[$uid] = ['operation_time' => 0, 'count' => 0];

			$top_users[$uid]['operation_time'] += $x['operation_time'];
			$top_users[$uid]['user_ip'][$x['user_ip']] = 1;
			$top_users[$uid]['user_ua'] = @$x['user_agent'];
			if(@$top_users[$uid]['uri'] < $x['server_uri'])
			{
				$top_users[$uid]['uri'] = $x['server_uri'];
				$top_users[$uid]['referer'] = @$x['referer'];
			}
			$top_users[$uid]['count']++;

			$top_requests[] = $x;
		}

		uasort($top_users, function($a, $b) { return $a['operation_time'] > $b['operation_time'] ? -1 : 1;});
		$top_users = array_slice($top_users, 0, 50);

		uasort($top_requests, function($a, $b) { return $a['operation_time'] > $b['operation_time'] ? -1 : 1;});
		$top_requests = array_slice($top_requests, 0, 100);

		return array_merge(parent::body_data(), [
			'top_users' => $top_users,
			'top_requests' => $top_requests,
			'total_time' => $total_time,
			'total_requests' => $total_requests,
			'start_time' => $start_time,
		]);
	}
}
