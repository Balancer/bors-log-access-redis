<?php

namespace B2\Log\Access;

class Redis
{
	static function check()
	{
		$is_bot = bors()->client()->is_bot();

		$ip = $_SERVER['REMOTE_ADDR'];
		$sid = $is_bot ? $is_bot : $ip;

		$access_log_mem_name = 'bors:access_log:'.$sid;


	}

/*
	Need to save
	1. Access time per UID
	2. Access time per class
	3. Slowest urls
	4. Slowest pairs class / UID
*/

	static function register($uri, $object, $operation_time)
	{
		$time_id		= floor(time()/600);

		$is_bot = bors()->client()->is_bot();
		$is_crawler = bors()->client()->is_crawler();

		$ip = @$_SERVER['REMOTE_ADDR'];
		$uid = $is_bot ? $is_bot : $ip;

		$uid = preg_replace('/[^\w-]+/', '_', $uid);

		if(!$uid)
			$uid = 'empty_uid';

		$id = $time_id.':sum_time:'.$uid;

		if($cfg_srv = \B2\Cfg::get('redis.servers'))
			dump($cfg_srv);

		$client = new \Predis\Client($cfg_srv, [
			'prefix' => 'bors:access_log:',
		]);
//		$t = $client->get($id) + $time;
//		$client->set($id, $t);
		$client->incrBy($id, $operation_time);
		$client->expire($id, 600);

		$id = $time_id.':access_log';

		$data = [
			'user_ip' => @$_SERVER['REMOTE_ADDR'],
			'user_id' => bors()->user_id(),
			'server_uri' => $uri,
			'referrer' => @$_SERVER['HTTP_REFERER'],
			'access_time' => round($GLOBALS['stat']['start_microtime']),
			'operation_time' =>  str_replace(',', '.', $operation_time),
			'user_agent' => @$_SERVER['HTTP_USER_AGENT'],
			'is_bot' => $is_bot ? $is_bot : NULL,
			'is_crawler' => $is_crawler,
		];

		if(empty($object) || !is_object($object))
		{
			$data['object_class_name'] = @$_SERVER['REQUEST_URI'];
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
}
