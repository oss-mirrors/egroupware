<!-- $Id$ -->
      <p><b>&nbsp;&nbsp;&nbsp;{lang_action}</b><br>
      <hr noshade width="98%" align="center" size="1">

      <center>
	{error}{message}
        <table width="75%" border="0" cellspacing="0" cellpadding="0">
       <form method="POST" action="{actionurl}">
        {hidden_vars}
         <tr>
          <td>{lang_project}:</td>
	<td><select name="filter"><option value="">{lang_select_project}</option>{project_list}</select></td>
         </tr>
         <tr>
          <td>{lang_activity}:</td>
          <td><select name="activity">{activity_list}</select></td>
         </tr>
         <tr>
          <td height="35">{lang_minperae}</td>
          <td height="35">{minperae}</td>
         </tr>
         <tr>
          <td height="35">{lang_billperae}&nbsp;{currency}</td>
          <td height="35">{billperae}</td>
         </tr>
         <tr>
          <td>{lang_descr}:</td>
          <td><input type="text" name="hours_descr" size="50" value="{hours_descr}"></td>
         </tr>
         <tr>
          <td>{lang_remark}:</td>
          <td colspan="2"><textarea name="remark" rows="5" cols="50" wrap="VIRTUAL">{remark}</textarea></td>
         </tr>
         <tr>
          <td height="35"><b>{lang_work_date}</b></td>
          <td>&nbsp;</td>
         </tr>
         <tr>
          <td>{lang_start_date}:</td>
          <td>{start_date_select}</td>
         </tr>
         <tr>
          <td>{lang_end_date}:</td>
          <td>{end_date_select}</td>
         </tr>
         <tr>
          <td height="35"><b>{lang_work_time}</b></td>
          <td>&nbsp;</td>
         </tr>
         <tr>
          <td>{lang_start_time}:</td>
          <td>
           <input type="text" name="st_hours" value="{st_hours}" size=3 maxlength=2>
           <input type="text" name="st_minutes" value="{st_minutes}" size=3 maxlength=2>
	</td>
         </tr>
         <tr>
          <td>{lang_end_time}:</td>
          <td>
           <input type="text" name="et_hours" value="{et_hours}" size=3 maxlength=2>
           <input type="text" name="et_minutes" value="{et_minutes}" size=3 maxlength=2>
	    </td>
         </tr>
         <tr>
          <td>{lang_time}:</td>
          <td>
           <input type="text" name="hours" value="{hours}" size=3 maxlength=2>
           <input type="text" name="minutes" value="{minutes}" size=3 maxlength=2>
          </td>
         </tr>
         <tr>
          <td>{lang_status}:</td>
          <td><select name="status">{status_list}</select></td>
         </tr>
         <tr>
          <td>{lang_employee}:</td>
          <td><select name="employee">{employee_list}</select></td>
         </tr>
         </table>

         <!-- BEGIN add -->
         
	<table width="75%" border="0" cellspacing="0" cellpadding="0">
	<tr valign="bottom">
	{hidden_vars}
	<td height="62"><input type="submit" name="submit" value="{lang_add}"></td>
	<td height="62"><input type="reset" name="reset" value="{lang_reset}"></form></td>
	<td><form method="POST" action="{doneurl}"> 
	    {hidden_vars}
            <input type="submit" name="done" value="{lang_done}"></form></td>
         </tr>
         </table>
         </center>
         
        <!-- END add -->
        
        <!-- BEGIN edit -->

         <table width="75%" border="0" cellspacing="0" cellpadding="0">
         <tr valign="bottom">
	    {hidden_vars}
          <td height="62"><input type="submit" name="submit" value="{lang_edit}">
           </form></td>
	<td height="62">
	    <form method="POST" action="{deleteurl}">
	    {hidden_vars}
	    <input type="submit" name="delete" value="{lang_delete}"></form></td>
	<td height="62">
	    <form method="POST" action="{doneurl}">
	    {hidden_vars}
            <input type="submit" name="done" value="{lang_done}"></form></td>
         </tr>
         </table>
	</center>

        <!-- END edit -->
