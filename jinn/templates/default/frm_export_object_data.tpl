<!-- BEGIN first_block -->
<h3>{title}&nbsp;:&nbsp;<span style="background-color:#dddddd">&nbsp;{objectname}&nbsp;</span></h3>
<hr/>

<form name="frm" method="post" action="{action}">
<table cellpadding="10">
	<tr>
		<td valign="top">
			{export}<br/>
			<br/>
			<input {source_1_disabled} type="radio" name="source" value="filtered" {source_1_checked}/>&nbsp;{source_1_label}<br/>
			<input {source_2_disabled} type="radio" name="source" value="unfiltered" {source_2_checked}/>&nbsp;{source_2_label}<br/>
			<input {source_3_disabled} type="radio" name="source" value="selected" {source_3_checked}/>&nbsp;{source_3_label}<br/>
			<br/>
			<input type="submit" name="do_csv" value="{submit}"/>
			<input type="button" value="{cancel}" onClick="history.back();"/>
		</td>
		<td style="border:1px solid;">
			<table>
				<tr>
					<td>
						{field_names_row_label}
					</td>
					<td>
						<input type="checkbox" name="field_names_row" value="true" {field_names_row_checked}/>
					</td>
				</tr>
				<tr>
					<td>
						{field_terminator_label}
					</td>
					<td>
						<input type="text" size="1" name="field_terminator" value="{field_terminator}"/>
					</td>
				</tr>
				<tr>
					<td>
						{field_wrapper_label}
					</td>
					<td>
						<input type="text" size="1" name="field_wrapper" value="{field_wrapper}"/>
					</td>
				</tr>
				<tr>
					<td>
						{escape_character_label}
					</td>
					<td>
						<input type="text" size="1" name="escape_character" value="{escape_character}"/>
					</td>
				</tr>
				<tr>
					<td>
						{row_terminator_label}
					</td>
					<td>
						<input type="text" size="1" name="row_terminator" value="{row_terminator}"/>
					</td>
				</tr>
			</table>
			<hr/>
			<table width="100%">
				<tr>
					<td width="21">
						<input type="radio" name="columns" value="all" {all_checked}/>
					</td>
					<td colspan="2">
						{all_columns_label}
					</td>
				</tr>
				<tr>
					<td width="21">
						<input type="radio" name="columns" value="select" {select_checked}/>
					</td>
					<td colspan="2">
						{select_columns_label}
					</td>
				</tr>
<!-- END first_block -->
<!-- BEGIN columns -->
				<tr>
					<td>
					</td>
					<td width="21">
						<input type="checkbox" name="col_{column}" value="{column}" {checked}/>
					</td>
					<td bgcolor="#dddddd">
						{column_label}
					</td>
				</tr>
<!-- END columns -->
<!-- BEGIN second_block -->
			</table>
			<hr/>
			<table>
				<tr>
					<td>
						{load_profile_label}
					</td>
					<td>
						<select onChange="frm.load.value='true'; frm.do_profile.click();" name="load_profile">{profiles}</select>
						<input type="hidden" name="load" value=""/>
					</td>
				</tr>
				<tr>
					<td>
						{save_profile_label}
					</td>
					<td>
						<input type="text" name="save_profile" value="{save_profile}"/>
						<input type="submit" name="do_profile" value="{save_as}"/>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</form>
<!-- END second_block -->
