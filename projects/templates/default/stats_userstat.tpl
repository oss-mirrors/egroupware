<p><b>&nbsp;&nbsp;&nbsp;{lang_action}<b><br>
<hr noshade width="98%" align="center" size="1">

      <center>
       <form method="POST" name="projects_form" action="{actionurl}">
        {common_hidden_vars}
        <table width="75%" border="0" cellspacing="1" cellpadding="3">
         <tr>
          <td>{lang_lid}:</td>
          <td>{lid}</td>
         </tr>
         <tr>
          <td>{lang_firstname}:</td>
          <td>{firstname}</td>
         </tr>
         <tr>
          <td>{lang_lastname}:</td>
          <td>{lastname}</td>
         </tr>
	 <tr>
 	  <td>{lang_start_date}</td>
          <td>{start_date_formatorder}</td>
         </tr>
         <tr>
          <td>{lang_end_date}:</td>
          <td>{end_date_formatorder}</td>
         </tr>
   	  <td>{billedonly}:</td>
	  <td><input type=checkbox name="billed" value="billed" {billed}></td>
	 <tr>
 	 </tr>
         </table>
           <table width="75%" border="0" cellspacing="0" cellpadding="0">
           <tr valign="bottom">
             <td height="62">
               <input type="submit" name="submit" value="{lang_calcb}">
             </td>
           </tr>
           </table>
         </form>
         <table width=85% border=0 cellspacing=1 cellpadding=3>
          <tr bgcolor="{th_bg}">
           <td width="10%" align=center bgcolor="{th_bg}" align=center>{hd_project}</td>
           <td width="10%" align=center bgcolor="{th_bg}" align=center>{hd_activity}</td>
           <td width="10%" align=center bgcolor="{th_bg}" align=center>{hd_hours}</td>
          </tr>
  
          <!-- BEGIN stat_list -->
          <tr bgcolor="{tr_color}">
           <td>{e_project}</td>
   	   <td>{e_activity}</td>
           <td align=right>{e_hours}</td>
          </tr>
          <!-- END stat_list -->
 
         </table>
        </center>
       </html>


