<!-- BEGIN form -->
<form method="POST" action="{form_action}">

<b>{lang_create_new_ticket}</b>
<hr><p>

{messages}

<form method="POST" action="{form_action}">

<table border="0" width="80%" cellspacing="0" align="center">
	<tr bgcolor="{th_bg}">
		<td colspan="4">&nbsp;</td>
	</tr>

	<tr bgcolor="{row_off}">
		<td align="left">{lang_assignedto}:</td>
		<td align="left">{value_assignedto}</td>
		<td align="left">{lang_billable_hours}:</td>
		<td align="left"><input name="ticket[billable_hours]" value="{value_billable_hours}"></td>
	</tr>

	<tr bgcolor="{row_off}">
		<td align="left">{lang_priority}:</td>
		<td align="left">{value_priority}</td>
		<td align="left">{lang_billable_hours_rate}:</td>
		<td align="left"><input name="ticket[billable_rate]" value="{value_billable_hours_rate}"></td>
	</tr>

<!--
	<tr bgcolor="{row_off}">
		<td align="left">{lang_group}:</td>
		<td align="left">{value_group}</b></td>
		<td align="left">&nbsp;</td>
		<td align="left">&nbsp;</td>
	</tr>
-->

	<tr bgcolor="{row_off}">
		<td align="left">{lang_category}:</td>
		<td align="left">{value_category}</td>
		<td align="left">&nbsp;</td>
		<td align="left">&nbsp;</td>
	</tr>

	<tr bgcolor="{row_off}">
		<td colspan="4" align="center"><hr></td>
	</tr>

	<tr bgcolor="{row_off}">
		<td colspan="4">{lang_subject}:</td>
	</tr>

	<tr bgcolor="{row_off}">
		<td colspan="4"><input name="ticket[subject]" value="{value_subject}"></td>
	</tr>

	<tr bgcolor="{row_off}">
		<td colspan="4">&nbsp;</td>
	</tr>

	<tr bgcolor="{row_off}">
		<td colspan="4">{lang_details}:<br><textarea rows="10" name="ticket[details]" cols="65" wrap="hard">{value_details}</textarea></td>
	</tr>

	<tr>
		<td align="left"><input type="submit" name="submit" value="{lang_submit}"></td>
		<td colspan="2">&nbsp;</td>
		<td align="right"><input type="submit" name="cancel" value="{lang_cancel}"></td>
	</tr>
</table>









<!--
<center>
<table bgcolor="{tts_bgcolor}" cellpadding="3" border="1" width="600">
 <tr>
  <td width="100%" valign="center" align="center"><font color="{tts_textcolor}"><b>{tts_lang_addnewticket}</b></font></td>
 </tr>
 <tr>
  <td width="100%" align="left">
    <table cellspacing="0" cellpadding="5" border="0" width="100%">
     <tr>
      <td width="75%" valign="middle"></td>
     </tr> 
     <tr>
      <td width="15%" valign="middle" align="right"><b>{tts_lang_group}:</b> </td>
      <td width="75%" valign="middle"><select size="1" name="lstCategory">
      {tts_new_lstcategories}
      </select></td>
     </tr>
      <tr>
       <td width="15%" valign="middle" align="right"><b>{tts_lang_assignto}:</b></td>
       <td width="75%" valign="middle">
        <select size="1" name="assignto">
	  {tts_new_lstassigntos}
        </select></td>
      </tr>
      <tr>
       <td width="15%" valign="middle" align="right"><b>{tts_lang_subject}:</b></td>
       <td width="75%" valign="middle"><input type=text size=50 maxlength=80 name="subject" value="{tts_lang_nosubject}"></td>
      </tr>
      <tr>
       <td width="15%" valign="top" align="right"><b>{tts_lang_details}:</b></td>
       <td width="75%"><textarea rows="10" name="txtAdditional" cols="65" wrap="virtual"></textarea></td>
      </tr>
      <tr>
       <td width="15%" valign="middle" align="right"><b>{tts_lang_priority}:</b> </td>
       <td width="75%" valign="middle">
          <table border="0" width="100%" cellspacing="0" cellpadding="0">
              <tr>
               <td width="25%">
                <select name="optPriority">
		    {tts_priority_options}
                </select>
               </td>
              </tr>
          </table>
       </td>
      </tr>
    </table>
    
    <p align="center"><center><input type="submit" value="{tts_lang_addticket}" name="submit">
    <input type="reset" value="{tts_lang_clearform}"></center>
  </td>
 </tr>
</table>
</center>
</div>
</form>
-->
<!-- END form -->
