<!-- BEGIN form_header -->
   <script language="javascript" type="text/javascript">
function img_popup(img,pop_width,pop_height,attr)
{
   options="width="+pop_width+",height="+pop_height+",location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no";
   parent.window.open("{popuplink}&path="+img+"&attr="+attr, "pop", options);
}
</script>
<form method="post" name="frm" action="{form_action}" enctype="multipart/form-data" {form_attributes}>
{where_string_form}
<!-- END form_header -->

<!-- BEGIN js -->
<script language="JavaScript">
<!--

function onSubmitForm() {

{submit_script}

return true;
}

//-->

</script>
<!-- END js -->

<!-- BEGIN table_header -->
<table align="" cellspacing="2" cellpadding="2" style="background-color:#ffffff;border:solid 1px #cccccc;width:100%;margin:0px 10px 0px 10px;">
<!-- END table_header -->

<!-- BEGIN rows -->
<tr id="TR{fieldname}">
	<td style="width:100px;background-color:#eeeeee" valign="top" nowrap="nowrap">{display_name}&nbsp;{tipmouseover}</td>
	<td bgcolor="#eeeeee">{input}</td>
</tr>
<!-- END rows -->

<!-- BEGIN many_to_many -->
<tr><td bgcolor="{m2mrow_color}" valign="top">{m2mfieldname}</td>
<td bgcolor="{m2mrow_color}">
	<table cellspacing="0" cellpadding="3" border="1">
		<tr>
		   <td valign=top>{sel1_all_from}<br/>
	  			<select onDblClick="{on_dbl_click1}" multiple size="5" name="{sel1_name}">
				{sel1_options}	
				</select>
			</td>
			
			<td align="center" valign="top">{lang_add_remove}<br/><br/>
				<input onClick="{on_dbl_click1}" type="button" value=" &gt;&gt; " name="add">
				<br/>
				<input onClick="{on_dbl_click2}" type="button" value=" &lt;&lt; " name="remove">
			</td>
			
			<td valign="top">{lang_related}<br/>
				<select onDblClick="{on_dbl_click2}" multiple size="5" name="{sel2_name}">
				<!-- does this br belong here --><br/>
				{sel2_options}
				</select>

				<input type="hidden" name="{m2m_rel_string_name}" value="{m2m_rel_string_val}">
				<input type="hidden" name="{m2m_opt_string_name}">
			</td>
		</tr>
	</table>


		<!--{m2minput}-->
	</td>
</tr>
<!-- END many_to_many -->



<!-- BEGIN form_buttons -->
	<table style="background-color:#ffffff;margin:0px 10px 0px 10px;">
		<tr>
		<td><input type="submit" name="reopen" value="{save_button}"></td>
<td><input type="{save_and_add_new_button_submit}" {save_and_add_new_button_onclick} name="add_new" value="{save_and_add_new_button}"></td>
		<td><input type="submit" name="save" value="{save_and_return_button}"></td>
		<td style="visibility:{btn_delete}"><input type="hidden" name="delete"> <input type="submit"  name="delete" value="{delete}"></td>
		<td>{cancel}</td>
		</tr>
	</table>
<!-- END form_buttons -->

<!-- BEGIN form_footer -->
{hiddenfields}
{jsmandatory}

<script language="JavaScript">
{jshidefields}
</script>

</form>
<!-- END form_footer -->




