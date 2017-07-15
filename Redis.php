<?php

namespace B2\Log\Access;

class Redis
{
	static function sum_time()
	{
		$time_id		= str_replace(",",".",floor(time()/600));

		$is_bot = bors()->client()->is_bot();
		$is_crawler = bors()->client()->is_crawler();

		$ip = @$_SERVER['REMOTE_ADDR'];
		$uid = $is_bot ? $is_bot : $ip;

		$uid = preg_replace('/[^\w-]+/', '_', $uid);

		if(!$uid)
			$uid = 'empty_uid';

		$cfg_srv = \B2\Cfg::get('predis.servers');

		$client = new \Predis\Client($cfg_srv, [
			'prefix' => 'bors:access_log:',
		]);

		$sum_time = $client->get($time_id.':sum_time:'.$uid);
		$sum_time += $client->get(($time_id-1).':sum_time:'.$uid);

		return $sum_time;
	}

/*
	Need to save
	1. Access time per UID
	2. Access time per class
	3. Slowest urls
	4. Slowest pairs class / UID
*/

	static function register($params)
	{
		extract($params);

		$time_id		= str_replace(",",".",floor(time()/600));

		$is_bot = bors()->client()->is_bot();
		$is_crawler = bors()->client()->is_crawler();

		$ip = @$_SERVER['REMOTE_ADDR'];
		$uid = $is_bot ? $is_bot : $ip;

		$uid = preg_replace('/[^\w-]+/', '_', $uid);

		if(!$uid)
			$uid = 'empty_uid';

		$id = $time_id.':sum_time:'.$uid;

		$cfg_srv = \B2\Cfg::get('predis.servers');

		$client = new \Predis\Client($cfg_srv, [
			'prefix' => 'bors:access_log:',
		]);
//		$t = $client->get($id) + $time;
//		$client->set($id, $t);
		$client->incrByFloat($id, str_replace(',', '.', $operation_time));
		$client->expire($id, 600);

		$id = $time_id.':access_log';

		$data = [
			'uid' => $uid,
			'user_ip' => @$_SERVER['REMOTE_ADDR'],
			'user_id' => bors()->user_id(),
			'server_uri' => $uri,
			'referrer' => @$_SERVER['HTTP_REFERER'],
			'access_time' => $access_time,
			'operation_time' => $operation_time,
			'user_agent' => @$_SERVER['HTTP_USER_AGENT'],
			'is_bot' => $is_bot ? $is_bot : NULL,
			'is_crawler' => $is_crawler,
		];

		if(empty($object) || !is_object($object))
		{
			$data['object_class_name'] = $uri;
		}
		else
		{
			$data['object_class_name'] = $object->class_name();
			$data['object_id'] = $object->id();
			$data['has_bors'] = 1;
			$data['has_bors_url'] = 1;
			$data['object_url'] = ($u=$object->url()) ? $u : $uri;
		}

		$data = array_filter($data);

		$client->rPush($id, json_encode($data));
		$client->expire($id, 600);
	}

	static function all_users()
	{
		$time_id		= str_replace(",",".",floor(time()/600));

		$cfg_srv = \B2\Cfg::get('predis.servers');

		$client = new \Predis\Client($cfg_srv, [
			'prefix' => 'bors:access_log:',
		]);

		$log = $client->lRange(($time_id-1).':access_log', 0, -1);
		$log = array_merge($log, $client->lRange($time_id.':access_log', 0, -1));

		$users = [];
		foreach($log as $x)
		{
			$x = json_decode($x);
			if(!empty($x->user_id))
				$users[$x->user_id] = $x;
		}

		return $users;
	}
}
