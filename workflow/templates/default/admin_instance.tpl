<div style="color:red; text-align:center">{message}</div>

<form action="{form_action}" method="post">
<input type="hidden" name="iid" value="{iid}" />
<table style="border: 1px solid black;width:100%;margin-bottom:10px">
	<tr class="th">
		<td colspan="2" style="font-size: 120%; font-weight:bold">
			{instance_process}
		</td>
	</tr>
	<tr class="row_on">
		<td>{lang_Created}</td>
		<td>{inst_started}</td>
	</tr>
	<tr class="row_off">
		<td>{lang_Ended_or_modified}</td>
		<td>{inst_ended}</td>
	</tr>
	<tr class="row_on">
		<td>{lang_Workitems}</td>
		<td><a href="{wi_href}">{wi_wi}</a></td>
	</tr>
	<tr class="row_off">
               <td>{lang_Name}</td>
               <td>
			<input type="text" name="instance_name" value="{instance_name}">
			<input type="hidden" name="instance_previous_name" value="{instance_name}">
	       </td>
       </tr>
	<tr class="row_on">
               <td>{lang_Priority}</td>
               <td>
			<input type="text" name="instance_priority" value="{instance_priority}">
			<input type="hidden" name="instance_previous_priority" value="{instance_priority}">
	       </td>
       </tr>
       <tr class="row_off">
		<td>{lang_Status}</td>
		<td>
		<select name="status">
			<option value="active" {status_active}>{lang_active}</option>
			<option value="exception" {status_exception}>{lang_exception}</option>
			<option value="completed" {status_completed}>{lang_completed}</option>
			<option value="aborted" {status_aborted}>{lang_aborted}</option>
		</select>
		<input type="hidden" name="instance_previous_status" value="{status}">
		</td>
	</tr>
	<tr class="row_on">
		<td>{lang_Owner}</td>
		<td>
			<select name="owner">
			<!-- BEGIN block_select_owner -->
			<option value="{select_owner_value}" {select_owner_selected}>{select_owner_name}</option>
			<!-- END block_select_owner -->
			</select>
		<input type="hidden" name="instance_previous_owner" value="{owner}">
		</td>
	</tr>
	<tr class="row_off">
		<td>{lang_Send_all_activities_to_(experimental)}</td>
		<td>
			<select name="sendto">
			  <option value="">{lang_Don't_move}</option>
			  <!-- BEGIN block_select_sendto -->
			  <option value="{sendto_act_value}">{sendto_act_name}</option>
			  <!-- END block_select_sendto -->
			</select>
		</td>
	</tr>
	<tr class="row_on">
		<td>{lang_Activities}</td>
		<td>
		<!-- BEGIN block_instance_acts -->
			<table>
			<tr class="row_on">
				<td style="text-align:center">{lang_Activity}</td>
				<td style="text-align:center">{lang_Act_status}</td>
				<td style="text-align:center">{lang_User}</td>
			</tr>
			<!-- BEGIN block_instance_acts_table -->
			<tr class="row_off">
				<td>
					{inst_act_name} {inst_act_run}
				</td>
				<td>{inst_act_status}</td>
				<td>
					<input type="hidden" name="previous_acts[{inst_act_id}]" value="{activity_user}">
					<select name="acts[{inst_act_id}]">
					<option value="*" {inst_act_star_selected}>*</option>
					<!-- BEGIN block_instance_acts_table_users -->
					<option value="{inst_act_usr_value}" {inst_act_usr_selected}>{inst_act_usr_name}</option>
					<!-- END block_instance_acts_table_users -->
					</select>
				</td>
			</tr>
			<!-- END block_instance_acts_table -->
			</table>
		<!-- END block_instance_acts -->
		</td>
	</tr>	
	<tr class="th">
		<td><input type="submit" name="refresh" value="{lang_refresh}" /></td>
		<td><input type="submit" name="save" value="{lang_update}" /></td>
	</tr>
</table>
</form>

<form action="{form_action}" method="post">
<input type="hidden" name="iid" value="{iid}" />
<table style="border: 1px solid black;width:100%;margin-bottom:10px">
	<tr class="th">
		<td colspan="2" style="font-size: 120%; font-weight:bold">
			{lang_Instance_properties}
		</td>
	</tr>
	<tr class="th">
		<td>{lang_Property}</td>
		<td>{lang_Value}</td>
	</tr>
	<!-- BEGIN block_properties -->
	<tr bgcolor="{color_line}">
		<td>
		 <a href="{prop_href}"><img border="0" src="{img_trash}" alt="{lang_delete}" title="{lang_delete}" /></a>
		 <b>{prop_key}</b>
		 </td>
		<td>
			{prop_value}
		</td>
	</tr>
	<!-- END block_properties -->
	<tr class="th">
		<td>&nbsp;</td>
		<td><input type="submit" name="saveprops" value="{lang_update}" /></td>
	</tr>
</table>
</form>

<form action="{form_action}" method="post">
<input type="hidden" name="iid" value="{iid}" />
<table style="border: 1px solid black;width:100%;margin-bottom:10px">
	<tr class="th">
		<td colspan="2" style="font-size: 120%; font-weight:bold">
			{lang_Add_property}
		</td>
	</tr>
	<tr class="row_on">
		<td>{lang_Name}</td>
		<td><input type="text" name="name" /></td>
	</tr>
	<tr class="row_off">
		<td>{lang_Value}</td>
		<td><textarea name="value" rows="4" cols="80"></textarea></td>
	</tr>
	<tr class="th">
		<td>&nbsp;</td>
		<td><input type="submit" name="addprop" value="{lang_add}" /></td>
	</tr>
</table>
</form>
