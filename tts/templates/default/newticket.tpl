<!-- $Id$ -->

<form method=POST action="{tts_newticket_link}">
<div align="center">
<center>
<table bgcolor="{tts_bgcolor}" cellpadding="3" border="1" width="600">
 <tr>
  <td width="100%" valign="center" align="center"><font color="{tts_textcolor}"><b>{tts_addnewticket}</b></font></td>
 </tr>
 <tr>
  <td width="100%" align="left">
    <table cellspacing="0" cellpadding="5" border="0" width="100%">
     <tr>
      <td width="75%" valign="middle"></td>
     </tr> 
     <tr>
      <td width="15%" valign="middle" align="right"><b><?php echo lang("Group"); ?>:</b> </td>
      <td width="75%" valign="middle"><select size="1" name="lstCategory">{tts_new_lstcategories}</select></td>
     </tr>
      <tr>
       <td width="15%" valign="middle" align="right"><b>{tts_assignto}:</b></td>
       <td width="75%" valign="middle"><select size="1" name="assignto">{tts_new_lstassignto}</select></td>
      </tr>
      <tr>
       <td width="15%" valign="middle" align="right"><b>{tts_subject}:</b></td>
       <td width="75%" valign="middle"><input type=text size=50 maxlength=80 name="subject" value="{tts_nosubject}"></td>
      </tr>
      <tr>
       <td width="15%" valign="top" align="right"><b>{tts_details}:</b></td>
       <td width="75%"><textarea rows="10" name="txtAdditional" cols="65" wrap="virtual"></textarea></td>
      </tr>
      <tr>
       <td width="15%" valign="middle" align="right"><b>{tts_priority}:</b> </td>
       <td width="75%" valign="middle">
          <table border="0" width="100%" cellspacing="0" cellpadding="0">
              <tr>
               <td width="25%">
                <select name="optPriority">
                 <option value="1">1 - Lowest</option>
                 <option value="2">2</option>
                 <option value="3">3</option>
                 <option value="4">4</option>
                 <option value="5" selected>5 - Medium</option>
                 <option value="6">6</option>
                 <option value="7">7</option>
                 <option value="8">8</option>
                 <option value="9">9</option>
                 <option value="10">10 - Highest</option>
                </select>
               </td>
              </tr>
          </table>
       </td>
      </tr>
    </table>
    
    <p align="center"><center><input type="submit" value="{tts_addticket}" name="submit">
    <input type="reset" value="{tts_clearform}"></center>
  </td>
 </tr>
</table>
</center>
</div>
</form>
<!-- END tts_new_form -->


<!-- BEGIN tts_new_lstcategories -->
  <option value= "{tts_account_lid}" {tts_categoryselected}>{tts_account_name}</option>
<!-- END tts_new_lstcategories -->


<!-- BEGIN tts_new_lstassignto -->
  <option value= "{tts_account_lid}" {tts_assignedtoselected}>{tts_account_name}</option>
<!-- END tts_new_lstassignto -->
  
  
