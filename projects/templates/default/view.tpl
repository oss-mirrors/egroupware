<!-- $Id$ -->
      <p><b>&nbsp;&nbsp;&nbsp;{lang_action}</b><br>
      <hr noshade width="98%" align="center" size="1">

      <center>
        <table width="85%" border="0" cellspacing="1" cellpadding="3">
          <tr>
          <td><b>{lang_num}:</b></td>
          <td>{num}</td>
         </tr>
	<tr>
	<td height="2">&nbsp;</td>
	</tr>
         <tr>
          <td><b>{lang_title}:</b></td>
          <td>{title}</td>
         </tr>
        <tr>
        <td height="2">&nbsp;</td>
        </tr>
         <tr>
         <td><b>{lang_descr}:</b></td> 
	 <td>{descrval}</td>
         </tr>
        <tr>
        <td height="2">&nbsp;</td>
        </tr>
         <tr>
	 <td><b>{lang_customer}:</b></td>
	<td>{name}</td>
	 </tr>
        <tr>
        <td height="2">&nbsp;</td>
        </tr>
	 <tr>
	 <td><b>{lang_coordinator}:</b></td>
	 <td>{coordinator}</td>
	 </tr>
        <tr>
        <td height="2">&nbsp;</td>
        </tr>
	 <tr>
          <td><b>{lang_status}:</b></td>
          <td>{status}</td>
         </tr>
        <tr>
        <td height="2">&nbsp;</td>
        </tr>
         <tr>
          <td><b>{lang_budget}:</b></td>
          <td>{currency}&nbsp;{budget}</td>
         </tr>
        <tr>
        <td height="2">&nbsp;</td>
        </tr>
         <tr>
          <td><b>{lang_start_date}:</b></td>
          <td>{sdate}</td>
         </tr>
        <tr>
        <td height="2">&nbsp;</td>
         </tr>
         <tr>
          <td><b>{lang_end_date}:</b></td>
          <td>{edate}</td>
         </tr>
        <tr>
        <td height="2">&nbsp;</td>
         </tr>
         <tr>
          <td><b>{lang_bookable_activities}:</b></td>
          <td><select name="ba_activities[]" multiple>{ba_activities_list}</select></td>
         </tr>
        <tr>
        <td height="2">&nbsp;</td>
         </tr>
         <tr>
          <td><b>{lang_billable_activities}:</b></td>
          <td><select name="bill_activities[]" multiple>{bill_activities_list}</select></td>
         </tr>
         </table>
         
        <!-- BEGIN done -->
         
         <table width="75%" border="0" cellspacing="2" cellpadding="2">
         <tr>
          <td height="50">
           	<form method="POST" action="{done_action}">
                {hidden_vars}
                <input type="submit" name="submit" value="{lang_done}">
            </form></td>
          </tr>
         </table>
	</center>

        <!-- END done -->


