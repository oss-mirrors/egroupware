<!-- begin class_prefs_blocks.tpl -->

&nbsp; <!-- == block sep == --> &nbsp; 

<!-- BEGIN B_tr_blank -->
<tr>
	<td colspan="2" bgcolor="{back_color}">
		&nbsp;<br>
	</td>
</tr>
<!-- END B_tr_blank -->

&nbsp; <!-- == block sep == --> &nbsp;

<!-- BEGIN B_tr_sec_title -->
<tr>
	<td colspan="2" bgcolor="{th_bg}" valign="middle">
		<strong>{section_title}</strong>
	</td>
</tr>
<!-- END B_tr_sec_title -->

&nbsp; <!-- == block sep == --> &nbsp; 

<!-- BEGIN B_tr_textarea -->
<tr>
	<td align="left" width="{left_col_width}" bgcolor="{back_color}">
		{lang_blurb}
	</td>
	<td align="center" valign="middle" width="{right_col_width}" bgcolor="{back_color}">
		<textarea name="{pref_id}" rows="6" cols="50">{pref_value}</textarea>
	</td>
</tr>
<!-- END B_tr_textarea -->

&nbsp; <!-- == block sep == --> &nbsp; 

<!-- BEGIN B_tr_textbox -->
<tr>
	<td align="left" width="{left_col_width}" bgcolor="{back_color}">
		{lang_blurb}
	</td>
	<td align="center" valign="middle" width="{right_col_width}" bgcolor="{back_color}">
		<input type="text" name="{pref_id}" value="{pref_value}">
	</td>
</tr>
<!-- END B_tr_textbox -->

&nbsp; <!-- == block sep == --> &nbsp; 

<!-- BEGIN B_tr_passwordbox -->
<tr>
	<td align="left" width="{left_col_width}" bgcolor="{back_color}">
		{lang_blurb}
	</td>
	<td align="center" valign="middle" width="{right_col_width}" bgcolor="{back_color}">
		<input type="password" name="{pref_id}" value="{pref_value}">
	</td>
</tr>
<!-- END B_tr_passwordbox -->

&nbsp; <!-- == block sep == --> &nbsp; 

<!-- BEGIN B_tr_combobox -->
<tr>
	<td align="left" width="{left_col_width}" bgcolor="{back_color}">
		{lang_blurb}
	</td>
	<td align="center" valign="middle" width="{right_col_width}" bgcolor="{back_color}">
		<select name="{pref_id}">
			{pref_value}
		</select>
	</td>
</tr>
<!-- END B_tr_combobox -->

&nbsp; <!-- == block sep == --> &nbsp; 

<!-- BEGIN B_tr_checkbox -->
	<td align="left" width="{left_col_width}" bgcolor="{back_color}">
		{lang_blurb}
	</td>
	<td align="center" valign="middle" width="{right_col_width}" bgcolor="{back_color}">
		<input type="checkbox" name="{pref_id}" value="{checked_flag}" {pref_value}>
	</td>
</tr>
<!-- END B_tr_checkbox -->

&nbsp; <!-- == block sep == --> &nbsp; 

<!-- BEGIN B_submit_btn_only -->
<tr>
	<td colspan="2" align="center">
		<input type="submit" name="{btn_submit_name}" value="{btn_submit_value}">
	</td>
</tr>
<!-- END B_submit_btn_only -->

&nbsp; <!-- == block sep == --> &nbsp; 

<!-- BEGIN B_submit_and_delete_btns -->
<tr>
	<td align="center">
		<input type="submit" name="{btn_submit_name}" value="{btn_submit_value}">
	</td>
	<td align="center">
		<input type="submit" name="{btn_delete_name}" value="{btn_delete_value}">
	</td>
</tr>
<!-- END B_submit_and_delete_btns -->

&nbsp; <!-- == block sep == --> &nbsp; 

<!-- end class_prefs_blocks.tpl -->
