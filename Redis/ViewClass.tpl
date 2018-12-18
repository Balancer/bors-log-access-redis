<ul>
<li>Число запросов: {$total_requests}</li>
<li>Общее время исполнения: {$total_time|round} сек. ({round($total_time/$total_requests,3)} сек. на один запрос)</li>
<li>Период: с {$start_time|date:"d.m.y H:i"} ({round((time()-$start_time)/60)} минут)</li>
</ul>

<h2>Загрузка по пользователям</h2>
<table class="{$this->layout()->table_class()}">
<thead>
<tr>
	<th>uid</th>
	<th>всего обращений</th>
	<th>потрачено секунд</th>
	<th>среднее время</th>
	<th>%</th>
	<th>max uri</th>
	<th>referer</th>
	<th>IP</th>
	<th>UA</th>
</tr>
</thead>
<tbody>
{foreach $top_users as $uid => $x}
<tr>
	<td><a href="uid/{$uid}/">{$uid}</a></td>
	<td>{$x['count']}</td>
	<td>{$x['operation_time']|round:2}</td>
	<td>{round($x['operation_time']/$x['count'],3)}</td>
	<td>{round(100*$x['operation_time']/$total_time,1)}</td>
	<td><a href="{$x.uri}">{$x.uri|wordwrap:80:" ":true}</a></td>
	<td>{$x.referer|host_link}</td>
	<td>{join("<br/>\n",array_keys($x.user_ip))}</td>
	<td>{$x.user_ua}</td>
</tr>
{/foreach}
</tbody>
</table>

<h2>Запросы</h2>
<table class="{$this->layout()->table_class()}">
<thead>
<tr>
	<th>time</th>
	<th>uid</th>
	<th>user_ip</th>
	<th>потрачено секунд</th>
	<th>url</th>
	<th>referer</th>
</tr>
</thead>
<tbody>
{foreach $top_requests as $x}
<tr>
	<td>{$x.access_time|date:"H:i:s"}</td>
	<td>{$x.uid}</td>
	<td>{$x.user_ip}</td>
	<td>{$x.operation_time|round:3}</td>
	<td><a href="{$x.server_uri}">{$x.server_uri|wordwrap:80:" ":true}</a></td>
	<td>{$x.referer|host_link}</td>
</tr>
{/foreach}
</tbody>
</table>
