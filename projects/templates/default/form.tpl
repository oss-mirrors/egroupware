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

<script language="JavaScript">
	self.name="third_Window";
	function e_accounts_popup()
	{
		Window3=window.open('{e_accounts_link}',"Search","width=800,height=600,toolbar=no,scrollbars=yes,resizable=yes");
	}
</script>

<script language="JavaScript">
	var oldNumberInputValue;

	function changeProjectIDInput($_selectBox)
	{
		$numberInput = eval(document.getElementById("id_number"));
		if($_selectBox.checked == true)
		{
			$numberInput.disabled = true;
			$oldNumberInputValue = $numberInput.value;
			$numberInput.value = '';
		}
		else
		{
			$numberInput.disabled = false;
			$numberInput.value = $oldNumberInputValue;
		}
	}
</script>
{app_header}

<center>
<p>{message}</p>
<form method="POST" name="app_form" action="{action_url}">
<table width="100%" border="0" cellspacing="2" cellpadding="2">

<!-- BEGIN main -->

	<tr bgcolor="{th_bg}">
		<td width="100%" colspan="7"><b>{lang_main}</b>:&nbsp;<a href="{main_url}">{pro_main}</a></td>
	</tr>
	<tr bgcolor="{th_bg}">
		<td><b>{lang_pbudget}</b>:&nbsp;{currency}</td>
		<td>{lang_main}:</td>
		<td>{budget_main}</td>
		<td>{lang_sum_jobs}:</td>
		<td>{pbudget_jobs}</td>
		<td>{lang_available}:</td>
		<td>{apbudget}</td>
	</tr>
	<tr bgcolor="{th_bg}">
		<td><b>{lang_ptime}</b>:&nbsp;{lang_hours}</td>
		<td>{lang_main}:</td>
		<td>{ptime_main}</td>
		<td>{lang_sum_jobs}:</td>
		<td>{ptime_jobs}</td>
		<td>{lang_available}:</td>
		<td>{atime}</td>
	</tr>
</table>
<table width="100%" border="0" cellspacing="2" cellpadding="2">
	<tr bgcolor="{row_on}">
		<td>{lang_parent}:</td>
		<td colspan="3">{parent_select}</td>
	</tr>

<!-- END main -->

	<tr bgcolor="{row_off}">
		<td width="20%">{lang_investment_nr}:</td>
		<td width="30%"><input type="text" name="values[investment_nr]" value="{investment_nr}" size="30"></td>
		<td width="20%">{lang_previous}:</td>
		<td width="30%"><select name="values[previous]"><option value="">{lang_none}</option>{previous_select}</select></td>
	</tr>
	<tr bgcolor="{row_on}">
		<td>{lang_number}:</td>
		<td><input type="text" name="values[number]" value="{number}" size="30" id="id_number"></td>
		<td>{lang_choose}</td>
		<td>{choose}</td>
	</tr>
	<tr bgcolor="{row_off}">
		<td>{lang_title}:</td>
		<td><input type="text" name="values[title]" size="30" value="{title}"></td>
		<td>{lang_category}:</td>
		<td>{cat}</td>
	</tr>
	<tr bgcolor="{row_on}">
		<td valign="top">{lang_descr}:</td>
		<td colspan="3"><textarea name="values[descr]" rows="4" cols="50" wrap="VIRTUAL">{descr}</textarea></td>
	</tr>

	<tr bgcolor="{row_off}">
		<td>{lang_start_date_planned}:</td>
		<td>{pstart_date_select}</td>
		<td>{lang_date_due_planned}:</td>
		<td>{pend_date_select}</td>
	</tr>

	<tr bgcolor="{row_on}">
		<td>{lang_start_date}:</td>
		<td>{start_date_select}</td>
		<td>{lang_date_due}:</td>
		<td>{end_date_select}</td>
	</tr>

	<tr bgcolor="{row_off}">
		<td>{lang_status}:</td>
		<td><select name="values[status]">{status_list}</select></td>
		<td valign="top">{lang_access}:</td>
		<td>{access}</td>
	</tr>

	<tr bgcolor="{row_on}">
		<td>{lang_priority}:</td>
		<td><select name="values[priority]">{priority_list}</select></td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>

	<tr bgcolor="{row_off}">
		<td>{lang_url}:</td>
		<td>http://<input type="text" name="values[url]" size="30" value="{url}"></td>
		<td>{lang_reference}:</td>
		<td>http://<input type="text" name="values[reference]" size="30" value="{reference}"></td>
	</tr>

	<tr height="15">
		<td>&nbsp;</td>
	</tr>

	<tr bgcolor="{row_on}">
		<td>{lang_customer}:</td>
		<td>
			<input type="hidden" name="abid" value="{abid}">
			<input type="text" name="name" size="30" value="{name}" onClick="abook();" readonly></td>
		</td>
		<td>{lang_customer_nr}:</td>
		<td><input type="text" name="values[customer_nr]" size="30" value="{customer_nr}"></td>
	</tr>

	<tr bgcolor="{row_off}">
		<td>{lang_coordinator}:</td>
		<td colspan="3">

<!-- BEGIN clist -->

		<select name="accountid">{coordinator_list}</select>

<!-- END clist -->

<!-- BEGIN cfield -->

		<input type="hidden" name="accountid" value="{accountid}">
		<input type="text" name="accountname" size="20" value="{accountname}" onClick="accounts_popup();" readonly>

<!-- END cfield -->

		</td>
	</tr>

	<tr bgcolor="{row_on}">
		<td valign="top">{lang_employees}:</td>
		<td colspan="2">

<!-- BEGIN elist -->

		<select name="employees[]" multiple>{employee_list}</select>

<!-- END elist -->

<!-- BEGIN efield -->

			<table border="0" cellpadding="0" cellspacing="2">
				<tr>
					<td>
						<select name="employees[]" multiple>{employee_list}</select>
					</td>
					<td valign="top"><input type="button" value="{lang_open_popup}" onClick="e_accounts_popup();"></td>
				</tr>
			</table>

<!-- END efield -->

		</td>
		<td align="right" valign="top">{edit_roles_events_button}</td>
	</tr>

<!--begin rolefield1

	<tr bgcolor="{row_off}">
		<td valign="top">{lang_roles}:</td>
		<td colspan="2">
			<table width="100%" border="0" cellspacing="2" cellpadding="2">

end rolefield1
begin rolelist

				<tr>
					<td width="50%">{emp_name}</td>
					<td width="50%">{role_name}</td>
				</tr>

end rolelist

begin rolefield2
				</table>
		</td>
		<td valign="top" align="right"><input type="submit" name="roles" value="{lang_edit_roles}"></td>
	</tr>

end rolefield2-->

	<tr height="15">
		<td>&nbsp;</td>
	</tr>

	<tr bgcolor="{row_on}">
		<td>{lang_ptime}:&nbsp;{lang_hours}</td>
		<td colspan="3"><input type="text" name="values[ptime]" value="{ptime}">&nbsp;[hh]</td>
	</tr>
	<tr bgcolor="{row_off}">
		<td>{lang_budget}:&nbsp;{currency}</td>
		<td><input type="text" name="values[budget]" value="{budget}">&nbsp;[{currency}.c]</td>
		<td>{lang_extra_budget}:&nbsp;{currency}</td>
		<td><input type="text" name="values[e_budget]" value="{e_budget}">&nbsp;[{currency}.c]</td>
	</tr>

<!-- BEGIN accounting_act -->

	<tr bgcolor="{row_on}">
		<td>{lang_bookable_activities}:</td>
		<td colspan="3"><select name="book_activities[]" multiple>{book_activities_list}</select></td>
	</tr>
	<tr bgcolor="{row_off}">
		<td>{lang_billable_activities}:</td>
		<td colspan="3"><select name="bill_activities[]" multiple>{bill_activities_list}</select></td>
	</tr>

<!-- END accounting_act -->

<!-- BEGIN accounting_own -->

	<tr bgcolor="{row_on}">
		<td valign="top">{lang_accounting}:</td>
		<td valign="top"><select name="values[accounting]">
				<option value="">{lang_select_factor}</option>
				<option value="employee" {acc_employee_selected}>{lang_factor_employee}</option>
				<option value="project" {acc_project_selected}>{lang_factor_project}</option>
			</select>
		</td>
		<td valign="top">{lang_accounting_factor_for_project}:&nbsp;{currency}</td>
		<td>
			<table border="0" cellspacing="0" cellpadding="1">
				<tr>
					<td><input type="radio" name="values[radio_acc_factor]" value="hour" {acc_factor_hour}>{lang_per_hour}</td>
					<td><input type="text" name="values[project_accounting_factor]" value="{project_accounting_factor}"></td>
				</tr>
				<tr>
					<td><input type="radio" name="values[radio_acc_factor]" value="day" {acc_factor_day}>{lang_per_day}</td>
					<td><input type="text" name="values[project_accounting_factor_d]" value="{project_accounting_factor_d}"></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr bgcolor="{row_off}">
		<td>{lang_non_billable}:</td>
		<td colspan="3"><input type="checkbox" name="values[billable]" value="True" {acc_billable_checked}></td>

	</tr>

<!-- END accounting_own -->

	<tr bgcolor="{row_on}">
		<td valign="top">{lang_invoicing_method}:</td>
		<td><textarea name="values[inv_method]" rows="4" cols="30" wrap="VIRTUAL">{inv_method}</textarea></td>
		<td valign="top" align="center">
			<table border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td>{lang_discount}:</td>
					<td><input type="radio" name="values[discount_type]" value="percent" {dt_percent}>{lang_percent}&nbsp;[%.%]</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td><input type="radio" name="values[discount_type]" value="amount" {dt_amount}>{lang_amount}&nbsp;[{currency}.c]</td>
				</tr>
			</table>
		</td>
		<td valign="top"><input type="text" name="values[discount]" value="{discount}"></td>
	</tr>
	<tr height="15">
		<td>&nbsp;</td>
	</tr>
	<tr bgcolor="{row_off}">
		<td valign="top">{lang_result}:</td>
		<td colspan="3"><textarea name="values[result]" rows="4" cols="50" wrap="VIRTUAL">{result}</textarea></td>
	</tr>
	<tr bgcolor="{row_on}">
		<td valign="top">{lang_test}:</td>
		<td colspan="3"><textarea name="values[test]" rows="4" cols="50" wrap="VIRTUAL">{test}</textarea></td>
	</tr>
	<tr bgcolor="{row_off}">
		<td valign="top">{lang_quality}:</td>
		<td colspan="3"><textarea name="values[quality]" rows="4" cols="50" wrap="VIRTUAL">{quality}</textarea></td>
	</tr>

	<tr height="15">
		<td>&nbsp;</td>
	</tr>

<!-- begin msfield1

	<tr bgcolor="{row_off}">
		<td valign="top">{lang_milestones}:</td>
		<td colspan="2">
			<table width="100%" border="0" cellspacing="2" cellpadding="2">

-- end msfield1 --
-- begin mslist --

				<tr>
					<td width="50%"><a href="{ms_edit_url}">{s_title}</a></td>
					<td width="50%">{s_edateout}</td>
				</tr>

-- end mslist --

-- begin msfield2 --
				</table>
		</td>
		<td valign="top" align="right"><input type="submit" name="mstone" value="{lang_add_mstone}"></td>
	</tr>
end msfield2 -->
	<tr>
		<td align="right" colspan="4">{edit_mstones_button}</td>
	</tr>
	<tr valign="bottom" height="50" width="100%">
		<td width="25%"><input type="hidden" name="values[old_status]" value="{old_status}">
			<input type="hidden" name="values[old_parent]" value="{old_parent}">
			<input type="hidden" name="values[old_edate]" value="{old_edate}">
			<input type="hidden" name="values[old_coordinator]" value="{old_coordinator}">
			<input type="submit" name="save" value="{lang_save}"></td>
		<td width="25%"><input type="submit" name="apply" value="{lang_apply}"></td>
		<td width="25%" align="right">{delete_button}</td>
		<td width="25%" align="right"><input type="submit" name="cancel" value="{lang_cancel}"></td>
	</tr>
</table>
</form>
</center>
