<!-- BEGIN poll -->
<form action="{form_action}" method="post">
  <table border="0" align="center" width="100%" style="max-width: 400px;"> 
    {titlebar}
    {votes}
    {show_total}
    {buttons}
  </table>
</form>
<!-- END poll -->

<!-- BEGIN title -->
<tr class="th">
  <td colspan="4"><b>{poll_title}</b></td>
</tr>
<!-- END title -->

<!-- BEGIN vote -->
 <tr class="{tr_class}">
  <td>{option_text}</td>
  <td>{poll_bar}</td>
  <td align="right">{percent}%</td>
  <td align="right">{option_count}</td>
 </tr>
<!-- END vote -->

<!-- BEGIN image -->
<img src="{server_url}/polls/images/pollbar.gif" height="12" width="{scale}">
<!-- END image -->

<!-- BEGIN total -->
 <tr class="th">
  <td colspan="4" align="right">{lang_total}: {sum}</td>
 </tr>
<!-- END total -->

<!-- BEGIN button -->
 <tr>
  <td colspan="4" align="center"><input type="submit" name="submit" value="{button_text}"></td>
 </tr>
<!-- END button -->
