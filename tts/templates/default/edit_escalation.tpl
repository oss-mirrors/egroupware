
<!-- BEGIN options_select -->
    <option value="{optionvalue}" {optionselected}>{optionname}</option>
<!-- END options_select -->


<!-- BEGIN form -->
<br>

{messages}

<form method="POST" action="{form_action}">

<table border="0" width="95%" cellspacing="0" align="center">
	<tr class="th">
		<td colspan="2">&nbsp;</td>
	</tr>
           

	<tr class="row_off">
		<td>{lang_group_name}:</td>
		<td><select name="escalation[ticket_group]">{options_account_id}</select></b></td>
	</tr>

	<tr class="row_on">
            <td>{lang_priority_between}:</td>
            <td>
                <select name="escalation[ticket_priority_1]">{options_priority_1}</select></b>
                &nbsp;..&nbsp;
	        <select name="escalation[ticket_priority_2]">{options_priority_2}</select></b>

            </td>
        </tr>



	<tr class="row_on">
            <td>{lang_time_1}:</td>
            <td><input name="escalation[time_1]" value="{value_time_1}"></td>
        </tr>

	<tr class="row_off">
        <td>{lang_time_2}:</td>
        <td><input name="escalation[time_2]" value="{value_time_2}"></td>
    </tr>

   	<tr class="row_on">
        <td>{lang_time_3}:</td>
        <td><input name="escalation[time_3]" value="{value_time_3}"></td>
    </tr>

    <tr class="row_off">
        <td colspan="2"><input type="checkbox" name="escalation[email_1]" {value_email_1}>{lang_email_1}</td>
    </tr>

    <tr class="row_on">
        <td colspan="2"><input type="checkbox" name="escalation[email_2]" {value_email_2}>{lang_email_2}</td>
    </tr>


	<tr height="40">
		<td>
			<input type="submit" name="save" value="{lang_save}"> &nbsp;
			<input type="submit" name="cancel" value="{lang_cancel}">
		</td>
	</tr>
</table>
<br>


<!-- END form -->
