<!-- start jinn main menu-->


<table border="0" cellspacing="0" align="center" width="80%">
	<tr bgcolor="{th_bg}">
	<form method="POST" action="{main_form_action}">
	<input type="hidden" name="action" value="">
        <input type="hidden" name="form" value="main_menu">
	<input type="hidden" name="filter" value="none">
	<input type="hidden" name="qfield" value="">
	<input type="hidden" name="start" value="">
	<input type="hidden" name="order" value="">
	<input type="hidden" name="sort" value="">
	<input type="hidden" name="query" value="">

		<td align="center">{select_site}<br>
			<select name="site_id" onChange="this.form.submit()">
			{site_options}
			</select>
	 	</td>
		<td align="center">
			{select_object}<br>
			<select name="site_object_id" onChange="this.form.submit()">
			{site_objects}
			</select>
		</td>
	</tr></form>
</table>
	<br>

<!-- end jinn main menu-->
