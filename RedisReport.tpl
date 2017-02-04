<h2>Загрузка по пользователям</h2>
<table class="{$this->layout()->table_class()}">
<thead>
<tr>
	<th>uid</th>
	<th>max uri</th>
	<th>referer</th>
	<th>всего обращений</th>
	<th>потрачено секунд</th>
	<th>среднее время</th>
	<th>%</th>
</tr>
</thead>
<tbody>
{foreach $top_users as $uid => $x}
<tr>
	<td>{$uid}</td>
	<td><a href="{$x.uri}">{$x.uri|wordwrap:80:" ":true}</a></td>
	<td>{$x.referer|host_link}</td>
	<td>{$x['count']}</td>
	<td>{$x['operation_time']|round:2}</td>
	<td>{round($x['operation_time']/$x['count'],3)}</td>
	<td>..</td>
</tr>
{/foreach}
</tbody>
</table>

<h2>Загрузка по классам</h2>
<table class="{$this->layout()->table_class()}">
<thead>
<tr>
	<th>class name</th>
	<th>max uri</th>
	<th>referer</th>
	<th>всего обращений</th>
	<th>потрачено секунд</th>
	<th>среднее время</th>
	<th>%</th>
</tr>
</thead>
<tbody>
{foreach $top_classes as $class_name => $x}
<tr>
	<td>{$class_name}</td>
	<td><a href="{$x.uri}">{$x.uri|wordwrap:80:" ":true}</a></td>
	<td>{$x.referer|host_link}</td>
	<td>{$x['count']}</td>
	<td>{$x['operation_time']|round:2}</td>
	<td>{round($x['operation_time']/$x['count'],3)}</td>
	<td>..</td>
</tr>
{/foreach}
</tbody>
</table>
