<!-- BEGIN form -->
<p><b>{title}</b>
<hr><br>

  <center>{messages}</center>

  <form method="POST" action="{action_url}">
   <table border="0" width="65%" align="center">
    <tr bgcolor="{th_bg}">
     <td colspan="2">{lang_header}</td>
    </tr>
    <tr bgcolor="{row_on}">
     <td>{lang_display}</td>
     <td>{input_display}</td>
    </tr>
    <tr bgcolor="{row_off}">
     <td>{lang_base_url}</td>
     <td>{input_base_url}</td>
    </tr>
    <tr bgcolor="{row_on}">
     <td>{lang_news_file}</td>
     <td>{input_news_file}</td>
    </tr>
    <tr bgcolor="{row_off}">
     <td>{lang_minutes}</td>
     <td>{input_minutes}</td>
    </tr>
    <tr bgcolor="{row_on}">
     <td>{lang_listings}</td>
     <td>{input_listings}</td>
    </tr>
    <tr bgcolor="{row_off}">
     <td>{lang_type}</td>
     <td>{input_type}</td>
    </tr>
    <tr bgcolor="{row_on}">
     <td colspan="2" align="right"><input type="submit" name="submit" value="{lang_button}"></td>
    </tr>
   </table>
  </form>
<!-- END form -->
