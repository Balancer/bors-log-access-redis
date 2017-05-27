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
	<th>referrer</th>
</tr>
</thead>
<tbody>
{foreach $top_users as $uid => $x}
<tr>
	<td>{$uid}</td>
	<td>{$x['count']}</td>
	<td>{$x['operation_time']|round:2}</td>
	<td>{round($x['operation_time']/$x['count'],3)}</td>
	<td>..</td>
	<td><a href="{$x.uri}">{$x.uri|wordwrap:80:" ":true}</a></td>
	<td>{$x.referrer|host_link}</td>
</tr>
{/foreach}
</tbody>
</table>

<h2>Загрузка по классам</h2>
<table class="{$this->layout()->table_class()}">
<thead>
<tr>
	<th>class name</th>
	<th>всего обращений</th>
	<th>потрачено секунд</th>
	<th>среднее время</th>
	<th>%</th>
	<th>max uri</th>
	<th>referrer</th>
</tr>
</thead>
<tbody>
{foreach $top_classes as $class_name => $x}
<tr>
	<td>{$class_name}</td>
	<td>{$x['count']}</td>
	<td>{$x['operation_time']|round:2}</td>
	<td>{round($x['operation_time']/$x['count'],3)}</td>
	<td>..</td>
	<td><a href="{$x.uri}">{$x.uri|wordwrap:80:" ":true}</a></td>
	<td>{$x.referrer|host_link}</td>
</tr>
{/foreach}
</tbody>
</table>

<h2>Запросы</h2>
<table class="{$this->layout()->table_class()}">
<thead>
<tr>
	<th>time</th>
	<th>class name</th>
	<th>uid</th>
	<th>user_ip</th>
	<th>потрачено секунд</th>
	<th>url</th>
	<th>referrer</th>
</tr>
</thead>
<tbody>
{foreach $top_requests as $x}
<tr>
	<td>{$x.access_time|date:"H:i:s"}</td>
	<td>{$x.object_class_name}</td>
	<td>{$x.user_ip}</td>
	<td>{$x.uid}</td>
	<td>{$x.operation_time|round:3}</td>
	<td><a href="{$x.server_uri}">{$x.server_uri|wordwrap:80:" ":true}</a></td>
	<td>{$x.referrer|host_link}</td>
</tr>
{/foreach}
</tbody>
</table>
