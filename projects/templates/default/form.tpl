<!-- $Id$ -->
<script language="JavaScript">
	self.name="first_Window";
	function abook()
	{
		Window1=window.open('{addressbook_link}',"Search","width=800,height=600,toolbar=no,scrollbars=yes,resizable=yes");
	}
</script>
<script language="JavaScript">
	self.name="second_Window";
	function accounts_popup()
	{
		Window2=window.open('{accounts_link}',"Search","width=800,height=600,toolbar=no,scrollbars=yes,resizable=yes");
	}
</script>
{app_header}

<center>
<form method="POST" name="app_form" action="{action_url}">
{pref_message}<br>{message}
<table width="98%" border="0" cellspacing="2" cellpadding="2">
	<tr>
		<td width="35%" colspan"2">{lang_parent}</td>
		<td colspan"2">{pro_parent}</td>
	</tr>
	<tr>
		<td colspan"2">{lang_investment_nr}</td>
		<td colspan"2">{investment_nr}</td>
	</tr>
	<tr>
		<td colspan"2">{lang_choose}</td>
		<td colspan"2">{choose}</td>
	</tr>
	<tr>
		<td colspan"2">{lang_number}:</td>
		<td colspan"2"><input type="text" name="values[number]" value="{number}" size="25" maxlength="20"></td>
	</tr>
	<tr>
		<td colspan"2">{lang_title}:</font></td>
		<td colspan"2"><input type="text" name="values[title]" size="50" value="{title}"></td>
	</tr>
	<tr>
		<td valign="top" colspan"2">{lang_descr}:</td>
		<td colspan"2"><textarea name="values[descr]" rows="4" cols="50" wrap="VIRTUAL">{descr}</textarea></td>
	</tr>
	<tr>
		<td colspan"2">{lang_category}:</td>
		<td colspan"2">{cat}</td>
	</tr>
	<tr>
		<td colspan"2">{lang_customer}:</td>
		<td colspan"2">
			<table>
				<tr>
					<td><input type="hidden" name="abid" value="{abid}">
					<input type="text" name="name" size="50" value="{name}" readonly></td>
					<td><input type="button" value="{lang_open_popup}" onClick="abook();"></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan"2">{lang_coordinator}:</td>
		<td colspan"2">

<!-- BEGIN clist -->

		<select name="accountid">{coordinator_list}</select>

<!-- END clist -->

<!-- BEGIN cfield -->

			<table>
				<tr>
					<td>
						<input type="hidden" name="accountid" value="{accountid}">
						<input type="text" name="accountname" size="50" value="{accountname}" readonly>
					</td>
					<td><input type="button" value="{lang_open_popup}" onClick="accounts_popup();"></td>
				</tr>
			</table>

<!-- END cfield -->

		</td>
	</tr>
	<tr>
		<td colspan"2">{lang_status}:</td>
		<td colspan"2"><select name="values[status]">{status_list}</select></td>
	</tr>
	<tr>
		<td colspan"2">{lang_access}:</td>
		<td colspan"2">{access}</td>
	</tr>
	<tr height="15">
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td colspan"2">{lang_budget_main}</td>
		<td colspan"2">{budget_main}</td>
	</tr>
	<tr>
		<td colspan"2">{lang_budget}:&nbsp;{currency}</td>
		<td colspan"2"><input type="text" name="values[budget]" value="{budget}"></td>
	</tr>
	<tr>
		<td colspan"2">{lang_pcosts}&nbsp;{month}:&nbsp;{currency}</td>
		<td colspan"2">{pcosts}</td>
	</tr>
	<tr height="15">
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td colspan"2">{lang_ptime_main}</td>
		<td colspan"2">{ptime_main}</td>
	</tr>
	<tr>
		<td colspan"2">{lang_ptime}:&nbsp;{lang_hours}</td>
		<td colspan"2"><input type="text" name="values[ptime]" value="{phours}"></td>
	</tr>
	<tr height="15">
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td colspan"2">{lang_start_date}:</td>
		<td colspan"2">{start_date_select}</td>
	</tr>
	<tr>
		<td colspan"2">{lang_end_date}:</td>
		<td colspan"2">{end_date_select}</td>
	</tr>
	<tr height="15">
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td colspan"2">{lang_bookable_activities}:</td>
		<td colspan"2"><select name="book_activities[]" multiple>{book_activities_list}</select></td>
	</tr>
	<tr>
		<td colspan"2">{lang_billable_activities}:</td>
		<td colspan"2"><select name="bill_activities[]" multiple>{bill_activities_list}</select></td>
	</tr>
	<tr valign="bottom" height="50">
		<td><input type="submit" name="save" value="{lang_save}"></td>
		<td><input type="submit" name="apply" value="{lang_apply}"></td>
		<td>{delete}</td>
		<td><input type="submit" name="cancel" value="{lang_cancel}"></form></td>
	</tr>
</table>
</form>
</center>
