<!-- $Id$ -->

<!-- BEGIN tts_select_options -->
    <option value="{tts_optionvalue}" {tts_optionselected}>{tts_optionname}</option>
<!-- END tts_select_options -->


<form method="POST" action="{tts_viewticketdetails_link}">
 <input type="hidden" value="{tts_t_id}" name="t_id">
 <input type="hidden" value="{tts_t_user}" name="lstAssignedfrom">
  <div align="center">
   <center>
    <table border=0 width="80%" bgcolor="{tts_th_bg}" cellspacing=0>
     <tr>
       <td width="33%">&nbsp;</td>
       <td width="33%">&nbsp;</td>
       <td width="33%">&nbsp;</td>
     </tr>
     <tr>
       <td colspan="3" align="center"><font size="+2">{tts_lang_viewjobdetails}</font></td>
     </tr>
     <tr>
       <td colspan="3" align="center"><hr noshade></td>
     </tr>
     <tr>
       <td align="center">
         <font size="3">ID: <b>{tts_t_id}</b>
       </td>
       <td align="center">
         {tts_lang_assignedfrom}: <br><b>{tts_t_user}</b>
       </td>
       <td align="center">
         {tts_lang_opendate}: <br><b>{tts_t_opendate}</b>
         <br>
         {tts_lang_closedate}: <br><b>{tts_t_status}</b>
       </td>

     </tr>
     <tr>
       <td colspan="3" align="center"><hr noshade></td>
     </tr>
     <tr>
       <td align="center">
         <b>{tts_lang_priority}:</b>
         <select name="optPriority">
	   {tts_priority_options}
         </select>

       </td>
       <td align="center">
         <b>{tts_lang_group}:</b>
         <select size="1" name="lstCategory">
            {tts_group_options}
         </select>
       </td>
       <td align="center">
         <b>{tts_lang_assignedto}:</b>
         <select size="1" name="lstAssignedto">
           {tts_assignedto_options}
         </select>
       </td>
     </tr>
     <input type="hidden" value="{tts_hidden_detailstring}" name="prevtxtdetail">
	 <tr><td colspan="3" align="left"><br><b>{tts_lang_subject}:</b>{tts_t_subject}<br><br>
	 <tr><td colspan="3" align="left"><B>{tts_lang_details}:</B><BR>{tts_detailstring}</td></tr>
         <tr><td colspan="3" align="left"><BR><BR>{tts_lang_additionalnotes}:<BR></td></tr>
	 <tr><td colspan="3" align="center"><textarea rows="12" name="txtAdditional" cols="70" wrap="physical"></textarea></td>
         <tr><td colspan="3" align="center"><hr noshade></td></tr>
        <tr>
         <td align="center">
	   <input type="radio" value='{tts_leftradiovalue}' name='optUpdateclose' checked>{tts_leftradio}
         </td>
         <td align="center">
           <input type="submit" value='{tts_lang_ok}' name='submit'>
         </td>
         <td align="center">
           <input type="radio" value='{tts_rightradiovalue}' name='optUpdateclose'>{tts_rightradio}
         </td>
       </tr>
       <tr>
         <td colspan="3">&nbsp;</td>
       </tr>
   </table>
</form>
