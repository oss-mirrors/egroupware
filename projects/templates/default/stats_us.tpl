      <p>&nbsp;&nbsp;&nbsp;{lang_action}<br>
      <hr noshade width="98%" align="center" size="1">

      <center>
       <form method="POST" name="user_form" action="{actionurl}">
        {common_hidden_vars}
        <table width="75%" border="0" cellspacing="1" cellpadding="3">
         <tr>
          <td>{lang_num}:</td>
          <td>{num}</td>
         </tr>
         <tr>
          <td>{lang_title}:</td>
          <td>{title}</td>
         </tr>
         <tr>
          <td>{lang_status}:</td>
          <td>{status}</td>
         </tr>
         <tr>
          <td>{lang_budget}:</td>
          <td>{budget}</td>
         </tr>
         <tr>
          <td>{lang_start_date}:</td>
          <td>{start_date_formatorder}</td>
         </tr>
         <tr>
          <td>{lang_end_date}:</td>
          <td>{end_date_formatorder}</td>
         </tr>
         <tr>
          <td>{lang_coordinator}:</td>
          <td>{coordinator}</td>
         </tr>
         <tr>
          <td>{lang_customer}:</td>
          <td>{customer}</td>
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
         </center>
         </html>


