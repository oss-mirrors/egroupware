<!-- $Id $ -->
<!-- BEGIN tts_select_options -->
    <option value="{tts_optionvalue}" {tts_optionselected}>{tts_optionname}</option>
<!-- END tts_select_options -->

<form method=POST action="{tts_newticket_link}">
<div align="center">
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
<!-- END tts_new_form -->

<!-- BEGIN tts_new_lstcategory -->
            <option value= "{tts_account_lid}" {tts_categoryselected}>{tts_account_name}</option>
<!-- END tts_new_lstcategory -->

<!-- BEGIN tts_new_lstassignto -->
          <option value= "{tts_account_lid}" {tts_assignedtoselected}>{tts_account_name}</option>
<!-- END tts_new_lstassignto -->
