<div style="color:red; text-align:center">{message}</div>
<div>
	<div>
		{proc_bar}
	</div>
	<div>
		{errors}
	</div>
</div>

<form action="{form_details_action}" method="post">
<input type="hidden" name="p_id" value="{p_id}" />
<input type="hidden" name="activity_id" value="{activity_id}" />
<input type="hidden" name="where2" value="{where2}" />
<input type="hidden" name="sort_mode2" value="{sort_mode2}" />
<input type="hidden" name="find" value="{find}" />
<input type="hidden" name="where" value="{where}" />
<input type="hidden" name="sort_mode" value="{sort_mode}" />
<table style="border: 1px solid black;width:100%; margin-bottom:10px">
	<tr class="th">
		<td colspan="2" style="font-size: 120%; font-weight:bold">
				{lang_Add_or_edit_an_activity} <input type="submit" name="new_activity" value="{lang_new}">
		</td>
	</tr>
	<tr>
	</tr>
	<tr class="row_on">
	  <td width="20%">{lang_Name}</td>
	  <td><input type="text" name="name" value="{name}" /></td>
	</tr>
	<tr class="row_off">
	  <td>{lang_Description}</td>
	  <td><textarea name="description" rows="4" cols="60">{description}</textarea></td>
	</tr>
	<tr class="row_on">  
	  <td>{lang_Type}</td>
	  <td>
	  <select name="type">
	  <!-- BEGIN block_select_type -->
		<option value="{type_value}" {type_selected}>{type_name}</option>
	  <!-- END block_select_type -->
	  </select>
	  {lang_Interactive}:<input type="checkbox" name="is_interactive" {checked_interactive} />
	  {lang_auto_routed}:<input type="checkbox" name="is_autorouted" {checked_autorouted} />
	  </td>
	</tr>

	<tr class="row_off">
	  <td>{lang_Add_transitions}</td>
	  <td>
		<table border="0" width="70%">
			<tr>
				<td>
					{lang_Add_transitions_from}:<br/>
					{add_trans_from}
				</td>
				<td>
					{lang_Add_transitions_to}:<br/>
					{add_trans_to}
				</td>
			</tr>    
		</table>
	  </td>
	</tr>

	<tr class="row_on">
	  <td>{lang_Roles_assigned_to_this_activity}</td>
	  <td>
	  <!-- BEGIN block_activity_roles -->
	  {act_role_name}[<a href="{act_role_href}">x</a>]<br/>
	  <!-- END block_activity_roles -->
	  </td>
	</tr>
	<tr class="row_off">
	  <td>{lang_Add_role}</td>
	  <td>
	  <select name="userole">
	  <option value="">{lang_add_new}</option>
	  <!-- BEGIN block_process_roles -->
	  <option value="{proc_roleId}">{proc_roleName}</option>
	  <!-- END block_process_roles -->
	  </select>
	  <input type="text" name="rolename" /><input type="submit" name="addrole" value="{lang_Add_role_to_process}" />
	  </td>
	</tr>
	<tr class="th">
	  <td>&nbsp;</td>
	  <td><input type="submit" name="save_act" value="{lang_save}" /> </td>
	</tr>

</table>
</form>

<div style="border: 1px solid black;margin-bottom:10px">
<form action="{form_process_activities_action}" method="post">
<div class="th" style="font-weight:bold; font-size:120%; margin-bottom:4px">{lang_Process_activities}</div>
<input type="hidden" name="p_id" value="{p_id}" />
<input type="hidden" name="activity_id" value="{activity_id}" />
<input type="hidden" name="where2" value="{where2}" />
<input type="hidden" name="sort_mode2" value="{sort_mode2}" />
<table width="100%" cellpadding="0" cellspacing="0">
<tr class="th">
	{left_arrow}
	<td style="text-align:center">
		{lang_Type}:
			<select name="filter_type">
			  <option value="">{lang_all}</option>
			  <option value="start">{lang_start}</option>
			  <option value="end" >{lang_end}</option>		  
			  <option value="activity" >{lang_activity}</option>		  
			  <option value="switch" >{lang_switch}</option>		  
			  <option value="split" >{lang_split}</option>		  
			  <option value="join" >{lang_join}</option>		  
			  <option value="standalone" >{lang_standalone}</option>		  
			</select>
		{lang_Interactivity}:
			<select name="filter_interactive">
				<option value="">{lang_all}</option>
				<option value="y">{lang_Interactive}</option>
				<option value="n">{lang_Automatic}</option>
			</select>
		{lang_Routing}:
			<select name="filter_autoroute">
				<option value="">{lang_all}</option>
				<option value="y">{lang_Auto_routed}</option>
				<option value="n">{lang_Manual}</option>
			</select>
		<input size="18" type="text" name="find" value="{find}" />
		<input type="submit" name="filter" value="{lang_Search}" />
	</td>
	{right_arrow}
</tr>
</table>	
</form>

<form action="{form_process_activities_action}" method="post">
<input type="hidden" name="find" value="{find}" />
<input type="hidden" name="where" value="{where}" />
<input type="hidden" name="sort_mode" value="{sort_mode}" />
<input type="hidden" name="where2" value="{where2}" />
<input type="hidden" name="sort_mode2" value="{sort_mode2}" />
<input type="hidden" name="p_id" value="{p_id}" />
<input type="hidden" name="activity_id" value="{activity_id}" />
<div style="position:relative">
<table border="0" width="100%">
<tr class="th" style="font-weight:bold">
	<td align="center">#</td>
	<td>{header_name}</a></td>
	<td align="center">{header_type}</a></td>
	<td align="center">{header_interactive}</a></td>
	<td align="center">{header_route}</a></td>
	<td width="70px">{lang_Action}</td>
</tr>
<!-- BEGIN block_process_activities -->
<tr bgcolor="{color_line}">
	<td style="text-align:right;">
	  {act_flowNum}
	</td>
	<td>
	  <a href="{act_href}">{act_name}</a>
	  {no_roles}
	</td>
	<td style="text-align:center;">
		{act_icon}
	</td>
	<td style="text-align:center;">
	  <input type="checkbox" name="activity_inter[{act_activity_id}]" {act_inter_checked} />
	</td>
    <td style="text-align:center;">
	  <input type="checkbox" name="activity_route[{act_activity_id}]" {act_route_checked} />
	</td>
	<td>
		<a href="{act_href_code}"><img src="{img_code}" alt="{lang_code}" title="{lang_code}" /></a>
		{act_template}
		<input style="position:absolute; right:5px" type="checkbox" name="activities[{act_activity_id}]" />
	</td>
</tr>
<!-- END block_process_activities -->
<tr class="th">
<td colspan="7">
	<input type="submit" name="update_act" value="{lang_update}" />
	<input style="position:absolute; right:5px" type="submit" name="delete_act" value="{lang_Delete_selected}" />
</td>
</tr>
</table>
</div>
</form>	
</div>

<table border="0" width="100%" style="border: 1px solid black;">
	<tr class="th">
		<td colspan="2" style="font-size: 120%; font-weight:bold">
			{lang_Process_transitions}
		</td>
	</tr>
	<tr>
		<td width="50%">
			<form action="{form_list_transitions_action}" method="post">
			<input type="hidden" name="p_id" value="{p_id}" />
			<input type="hidden" name="activity_id" value="{activity_id}" />
			<input type="hidden" name="find" value="{find2}" />
			<input type="hidden" name="where" value="{where2}" />
			<input type="hidden" name="sort_mode" value="{sort_mode}" />
			<input type="hidden" name="where2" value="{where2}" />
			<input type="hidden" name="sort_mode2" value="{sort_mode2}" />
			<table border="0" width="100%">
				<tr class="th">
					<td>
						<span style="font-weight:bold; margin-right:15px">{lang_List_of_transitions}</span>{lang_From}: {filter_trans_from}
					</td>
					<td>
					</td>
				</tr>
				<!-- BEGIN block_transitions_table -->
				<tr bgcolor="{color_line}">
					<td>
						<a href="{trans_href_from}">{trans_actFromName}</a>
						<img src='{trans_arrow}' alt='{lang_To}' />
						<a href="{trans_href_to}">{trans_actToName}</a>
					</td>
					<td>
						<input type="checkbox" name="transition[{trans_actFromId}_{trans_actToId}]" />
					</td>
				</tr>
				<!-- END block_transitions_table -->
				<tr class="th">
					<td colspan="2" style="text-align:right"><input type="submit" name="delete_tran" value="{lang_Delete_selected}" /></td>
				</tr>
				</table>
			</form>		
		</td>
		<td width="50%" valign="top" align="left">
			<form action="{form_list_transitions_action}" method="post">
			<input type="hidden" name="p_id" value="{p_id}" />
			<input type="hidden" name="activity_rd" value="{activity_id}" />
			<input type="hidden" name="find" value="{find2}" />
			<input type="hidden" name="where" value="{where2}" />
			<input type="hidden" name="sort_mode" value="{sort_mode}" />
			<input type="hidden" name="where2" value="{where2}" />
			<input type="hidden" name="sort_mode2" value="{sort_mode2}" />
			<table class="normal">
			<tr class="th" style="font-weight:bold">
				<td colspan="2">
					{lang_Add_a_transition}
				</td>
			</tr>
			<tr class="row_on">
			  <td>
			  {lang_From}:
			  </td>
			  <td>
				{add_a_trans_from}
			  </td>
			</tr>
			<tr class="row_off">
			  <td>
			  {lang_To}: 
			  </td>
			  <td>
				{add_a_trans_to}
			  </td>
			</tr>
			<tr class="th">
			  <td>&nbsp;</td>
			  <td>
				<input type="submit" name="add_trans" value="{lang_add}" />
			  </td>
			</tr>
			</table>	
			</form>
		</td>
	</tr>
</table>	
	
