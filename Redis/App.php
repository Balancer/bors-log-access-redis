<?php

namespace B2\Log\Access\Redis;

class App extends \B2\App
{
	function fast_routes($r)
	{
		$base = rtrim($this->base_url(), '/');
		$r->addRoute('GET', $base . '/uid/{id:\w+}[/]', ViewUid::class);
		$r->addRoute('GET', $base . '/classes/{id:.+}/', ViewClass::class);
		$r->addRoute('GET', $base . '[/]', \B2\Log\Access\RedisReport::class);
	}
}
