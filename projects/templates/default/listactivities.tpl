<!-- $Id$ -->

{app_header}

<p><b>&nbsp;&nbsp;&nbsp;{lang_header}</b><br>
<hr noshade width="98%" align="center" size="1">
<center>
<table border="0" width="85%" cellpadding="2" cellspacing="2">
	<tr>
		<td colspan="3" align="left">
			<table border="0" width="100%">
				<tr>
				{left}
					<td align="center">{lang_showing}</td>
				{right}
				</tr>
			</table>
		</td>
	</tr>
	<tr colspan="3">
		<td width="33%" align="left">
				<form method="POST" name="cat" action="{cat_action}">
				{lang_category}&nbsp;&nbsp;<select name="cat_id" onChange="this.form.submit();">
				<option value="">{lang_none}</option>
				{categories_list}
				</select>
				<noscript><input type="submit" name="cats" value="{lang_select}"></noscript>
				</form>
		</td>
		<td width="33%" align="center">&nbsp;</td>
		<td width="33%" align="right"><form method="POST" name="query" action="{search_action}">{search_list}</form></td>
	</tr>
</table>
{pref_message}
<table border="0" width="85%" cellpadding="2" cellspacing="2">
	<tr bgcolor="{th_bg}">
		<td width="8%" bgcolor="{th_bg}">{sort_num}</td>
		<td width="30%" bgcolor="{th_bg}">{sort_descr}</td>
		<td width="10%" bgcolor="{th_bg}" align="right">{currency}&nbsp;{sort_billperae}</td>
		<td width="10%" bgcolor="{th_bg}" align="right">{sort_minperae}</td>
		<td width="8%" bgcolor="{th_bg}" align="center">{lang_edit}</td>
		<td width="8%" bgcolor="{th_bg}" align="center">{lang_delete}</td>
	</tr>

<!-- BEGIN activities_list -->

	<tr bgcolor="{tr_color}">
		<td>{num}</td>
		<td>{descr}</td>
		<td align="right">{billperae}</td>
		<td align="right">{minperae}</td>
		<td align="center"><a href="{edit}">{lang_edit}</a></td>
		<td align="center"><a href="{delete}">{lang_delete}</a></td>
	</tr>

<!-- END activities_list -->

</table>
<table border="0" cellpadding="2" cellspacing="2">
	<tr>
		<td><form method="POST" action="{add_url}">
			<input type="submit" name="Add" value="{lang_add}"></form></td>
		<td><form method="POST" action="{project_url}"> 
			<input type="submit" name="pro" value="{lang_projects}"></form></td>
	</tr>
</table>
</center>
