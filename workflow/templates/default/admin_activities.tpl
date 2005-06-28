<div style="color:red; text-align:center">{message}</div>
<div>
	<div>
		{proc_bar}
	</div>
	<div>
		{errors}
	</div>
</div>
<br />
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
				{lang_Add_or_edit_an_activity}
		</td>
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
		</td>
	</tr>
	<tr class="row_off">
         <td>{lang_Properties}</td>
         <td>
               <table cellpading="1" cellspacing="1">
			<tr class="row_off">
				<td colspan="4">{lang_users_can_act_on_activity_before_its_execution_with_form_and_after_by_sending_it_to_the_next_activity}</td>
			</tr>
			<tr class="row_on">
				<td>{img_interactive}</td>
                               	<td>{lang_Interactive}</td>
                               	<td><input type="checkbox" name="is_interactive" {checked_interactive} /></td>
				<td>{lang_interactive_activities_show_form_before_being_executed}</td>
			</tr>
                       	<tr class="row_on">
				<td>{img_transition_auto}</td>
                               	<td>{lang_auto_routed}</td>
                               	<td><input type="checkbox" name="is_autorouted" {checked_autorouted} /></td>
				<td>{lang_autorouted_activities_does_not_need_to_be_sent_by_any_user_after_their_execution}</td>
                       	</tr>
               </table>
         </td>
       </tr>
       <tr class="row_on">
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

	<tr class="row_off">
	  <td>{lang_Roles_assigned_to_this_activity}</td>
	  <td>
	  <!-- BEGIN block_activity_roles -->
	  {act_role_name}[<a href="{act_role_href}">x</a>]<br/>
	  <!-- END block_activity_roles -->
	  </td>
	</tr>
	<tr class="row_on">
	  <td>{lang_Add_role}</td>
	  <td>
	  <select name="userole">
	  <option value="">{lang_add_new}</option>
	  <!-- BEGIN block_process_roles -->
	  <option value="{proc_roleId}">{proc_roleName}</option>
	  <!-- END block_process_roles -->
	  </select>
	  <input type="text" name="rolename" /><input type="submit" name="addrole" value="{lang_Add_new_role}" />
	  </td>
	</tr>
       <tr class="row_off">
         <td>{lang_Default_User}</td>
         <td>
               <select name="default_user">
			<option value="*" {default_user_selected_none}>{lang_None}</option>		
	      		<!-- BEGIN block_select_default_user -->
			<option value="{default_user_value}" {default_user_selected}>{default_user_name}</option>
			<!-- END block_select_default_user -->
		</select>
		<br />
		{lang_the_default_user_will_only_be_set_if_he_is_mapped_to_a_role_on_the_activity}<br />
		{lang_setNextUser_directives_can_override_it}
         </td>
       </tr>

	<tr class="th">
	        <td colspan="2">
			<table width="100%" cellpadding="0" cellspacing="0">
			<tr><td style="font-size: 120%; font-weight:bold">
				<input type="submit" name="save_act" value="{lang_save}" />
			</td><td style="text-align: right;font-size: 120%; font-weight:bold">
				<input type="submit" name="new_activity" value="{lang_new}" />
			</td></tr>
			</table>
		</td>
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
			<select name="filter_type" >
				<option {selected_filter_type_all} value="">{lang_All}</option>
				<!-- BEGIN block_select_filter_type -->
				<option {selected_filter_type} value="{filter_type_name}">{filter_type_name}</option>
				<!-- END block_select_filter_type -->
			</select>
		{lang_Interactivity}:
			<select name="filter_interactive">
				<option {selected_filter_interactive_all} value="">{lang_All}</option>
				<!-- BEGIN block_select_filter_interactive -->
				<option {selected_filter_interactive} value="{filter_interactive_value}">{filter_interactive_name}</option>
				<!-- END block_select_filter_interactive -->
			</select>
		{lang_Routing}:
			<select name="filter_autoroute">
				<option {selected_filter_autoroute_all} value="">{lang_All}</option>
				<!-- BEGIN block_select_filter_autoroute -->
				<option {selected_filter_autoroute} value="{filter_autoroute_value}">{filter_autoroute_name}</option>
				<!-- END block_select_filter_autoroute -->
			</select>
		{lang_Search}:&nbsp;
		<input size="18" type="text" name="find" value="{find}" />
		<input type="submit" name="filter" value="{lang_Filter}" />
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
	<td style="width: 20px;" align="center">#</td>
	<td style="width: 40%;">{header_name}</a></td>
	<td align="center">{header_type}</a></td>
	<td align="center">{header_interactive}</a></td>
	<td align="center">{header_route}</a></td>
	<td align="center">{header_default_user}</a></td>
	<td width="70px">{lang_Action}</td>
</tr>
<!-- BEGIN block_process_activities -->
<tr bgcolor="{color_line}">
	<td style="text-align:center;">
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
        <td style="text-align:center;">
          {act_default_user}
        </td>
	<td>
		<a href="{act_href_edit}"><img src="{img_code}" alt="{lang_edit}" title="{lang_edit}" /></a>
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
			{img_transition}{lang_Process_transitions}
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
					<td align="right">
						{img_transition_delete}
					</td>
					<td style="text-align:right;"><input type="submit" name="delete_tran" value="{lang_Delete_selected}" /></td>
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
			<table class="normal" width="100%">
			<tr class="th" style="font-weight:bold">
				<td colspan="2">
					{lang_Add_a_transition}
				</td>
			</tr>
			<tr class="row_on">
			  <td>
			  {lang_From}:
			  </td>
			  <td width="90%">
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
			  <td align="right">
			  	{img_transition_add}
			  </td>
			  <td>
				<input type="submit" name="add_trans" value="{lang_Add_Transition}" />
			  </td>
			</tr>
			</table>	
			</form>
		</td>
	</tr>
</table>	
	
