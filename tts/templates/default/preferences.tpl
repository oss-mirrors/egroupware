<!-- $Id$ -->
<!-- BEGIN preferences.tpl -->
<!-- BEGIN tts_select_options -->
    <option value="{tts_optionvalue}" {tts_optionselected}>{tts_optionname}</option>
<!-- END tts_select_options -->

<p><b>{lang_ttsprefs}:</b><hr><p>
   
  <form method="POST" action="{action_url}">
   <table border="0" align="center" cellspacing="1" cellpadding="1">
    <tr bgcolor="#EEEEEE">
     <td>{lang_show_new_updated}</td>
     <td><input type="checkbox" name="mainscreen_show_new_updated" {show_new_updated}></td>
    </tr>
    <tr bgcolor="#EEEEEE">
     <td>{lang_defaultgroup}</td>
     <td>
      <select size="1" name="groupdefault">
           {tts_groupoptions}
      </select>
    </td>
    </tr>
    <tr bgcolor="#EEEEEE">
     <td>{lang_defaultassignto}</td>
     <td>
       <select size="1" name="assigntodefault">
           {tts_assigntooptions}
       </select>
    </td>
    </tr>
    <tr bgcolor="#EEEEEE">
     <td>{lang_defaultpriority}</td>
     <td>
       <select size="1" name="prioritydefault">
           {tts_priorityoptions}
       </select>
     </td>
    </tr>
    <tr bgcolor="#EEEEEE">
     <td>{lang_refreshinterval}</td>
     <td>
        <input name="refreshinterval" value="{refreshinterval}" size="60"></input>
     </td>
    </tr>
    <tr colspan="2" >
     <td colspan="5" align="center">
      <input type="submit" name="submit" value="{lang_submit}">
     </td>
    </tr>
   </table>
   </form>
