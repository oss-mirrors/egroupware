<!-- $Id$ -->
      <p><b>&nbsp;&nbsp;&nbsp;{lang_action}</b><br>
      <hr noshade width="98%" align="center" size="1">

      <center>
       <form method="POST" name="activity_form" action="{actionurl}">
        {common_hidden_vars}
        <table width="75%" border="0" cellspacing="0" cellpadding="0">
         <tr>
          <td>{lang_num}:<br><br></td>
          <td>{num}<br><br></td>
         </tr>
         <tr>
          <td>{lang_title}:<br><br></td>
          <td>{title}<br><br></td>
         </tr>
         <tr>
          <td>{lang_activity}:</td>
          <td>
           <select name="activity">
            {activity_list}
           </select>
          </td>
         </tr>
         <tr>
          <td>{lang_date}:</td>
          <td>
            {date_formatorder}
          </td>
         </tr>
         <tr>
          <td>{lang_end_date}:</td>
          <td>
            {end_date_formatorder}
          </td>
         </tr>
         <tr>
          <td>{lang_remark}:</td>
          <td colspan="2">
           <textarea name="remark" rows=4 cols=50 wrap="VIRTUAL">{remark}</textarea>
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
          <td>
           <select name="status">
            {status_list}
           </select>
          </td>
         </tr>
         <tr>
          <td>{lang_employee}:</td>
          <td>
           <select name="employee">
            {employee_list}
           </select>
          </td>
         </tr>
         
         <!-- BEGIN add -->
         </table>
         
         <table width="75%" border="0" cellspacing="0" cellpadding="0">
         <tr valign="bottom">
          <td height="62">
           <input type="submit" name="submit" value="{lang_addsubmitb}">
          </td>
          <td height="62">
           <input type="reset" name="reset" value="{lang_addresetb}">
          </td>
         </tr>
         </table>
         </form>
         </center>
         </html>
         
        <!-- END add -->
        
        <!-- BEGIN edit -->
         <tr>
          <td>{lang_minperae}:</td>
          <td>
           <input type="text" name="minperae" value="{minperae}">
          </td>
         </tr>
         <tr>
          <td>{lang_billperae}:</td>
          <td>
           <input type="text" name="billperae" value="{billperae}">
          </td>
         </tr>
         </table>
         
         <table width="75%" border="0" cellspacing="0" cellpadding="0">
         <tr valign="bottom">
          <td height="62">
           <input type="submit" name="submit" value="{lang_editsubmitb}">
            &nbsp;
           </form>
          </td>
         </tr>
         </table>
	</center>
	</html>
        <!-- END edit -->
