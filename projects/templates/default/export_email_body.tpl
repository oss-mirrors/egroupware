<!-- $Id$ -->

<!-- BEGIN body_html -->

<table border="0" width="100%" cellpadding="2" cellspacing="0">
	<tr bgcolor="{th_bg}">
		<td colspan="4"><b>{lang_main}:&nbsp;{title_main}</b></td>
	</tr>
	<tr bgcolor="{row_off}">
		<td>{lang_number}:</td>
		<td>{number_main}</td>
		<td>{lang_url}:</td>
		<td><a href="http://{url_main}" taget="_blank">{url_main}</a></td>
	</tr>
	<tr bgcolor="{row_off}">
		<td>{lang_coordinator}:</td>
		<td>{coordinator_main}</td>
		<td>{lang_customer}:</td>
		<td>{customer_main}</td>
	</tr>
</table>

<table border="0" width="100%" cellpadding="2" cellspacing="2">
	<tr bgcolor="{th_bg}">
		<td>{sort_title}</td>
		{sort_cols}
	</tr>
	{list}
</table>

<!-- END body_html -->

<!-- BEGIN body_text -->
	{lang_enable_html}
<!-- END body_text -->

<!-- BEGIN pro_sort_cols -->
	<td align="{col_align}">{sort_column}</td>
<!-- END pro_sort_cols -->

<!-- BEGIN projects_list -->

	<tr bgcolor="{tr_color}">
		<td valign="top"><a href="{projects_url}">{title}</a></td>
		{pro_column}
	</tr>

<!-- END projects_list -->

<!-- BEGIN pro_cols -->

		<td align="{col_align}">{column}</td>

<!-- END pro_cols -->
