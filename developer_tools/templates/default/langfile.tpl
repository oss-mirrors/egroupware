<!-- BEGIN header -->
<form method="post" action="{action_url}">
 <table width="80%" align="center">
  <tr>
   <td>{lang_source}</td>
   <td>
    <select name="sourcelang">
{sourcelangs}
    </select>
   </td>
  </tr>
  <tr>
   <td>{lang_target}</td>
   <td>
    <select name="targetlang">
{targetlangs}
    </select>
   </td>
  </tr>
  <tr>
  <input name="app_name" type="hidden" value="{app_name}">
   <td><input type="Submit" name="submit" value="{lang_submit}"></td>
 </form>
 <form method="post" action="{cancel_link}">
   <td><input type="submit" name="cancel" value="{lang_cancel}"></td>
  </tr>
 </table>
 </form>
 <hr>
 <table width="80%" align="center">
  <tr>
   <th colspan="3" align="center">{lang_application}:&nbsp;{app_name}</th>
  </tr>
  <tr>
   <form method="post" action="{action_url}">
   <th align="left">{lang_appname}</th>
   <th align="left">{lang_message}</th>
   <th align="left">{lang_original}</th>
   <th align="left">{lang_translation}</th>
  </tr>
<!-- END header -->

<!-- BEGIN detail -->
  <tr>
   <td>{transapp}</td>
   <td>{mess_id}</td>
   <td>{source_content}</td>
   <td><input name="translations[{mess_id}]" type="text" size="50" maxlength="255" value="{content}"></td>
  </tr>
<!-- END detail -->

<!-- BEGIN prefooter -->
</table>
<hr>
<table width="80%" align="center">
  <tr valign="top">
<!-- END prefooter -->

<!-- BEGIN srcdownload -->
   <td align="left">{lang_source}</td>
   <td align="center">
     <input name="app_name" type="hidden" value="{app_name}">
     <input name="sourcelang" type="hidden" value="{sourcelang}">
     <input type="submit" name="dlsource" value="{lang_download}">
   </td>
<!-- END srcdownload -->

<!-- BEGIN srcwrite -->
   <td align="center">
     <input type="submit" name="writesource" value="{lang_write}">
   </td>
<!-- END srcwrite -->

<!-- BEGIN tgtdownload -->
   <td align="center">{lang_target}</td>
   <td align="center">
     <input name="app_name" type="hidden" value="{app_name}">
     <input name="targetlang" type="hidden" value="{targetlang}">
     <input type="submit" name="dltarget" value="{lang_download}">
   </td>
<!-- END tgtdownload -->

<!-- BEGIN tgtwrite -->
   <td align="center">
     <input type="submit" name="writetarget" value="{lang_write}">
   </td>
<!-- END tgtwrite -->

<!-- BEGIN footer -->
</table>
<hr>
<table width="80%" align="center">
  <tr valign="top">
   <td align="center">&nbsp;</td>
     <input name="update" type="submit" value="{lang_update}">
   <td align="center"><input type="submit" name="addphrase" value="{lang_addphrase}"></td>
  </form>
  <form method="post" action="{phrase_link}">
   <td align="center">&nbsp;</td>
     <input name="app_name" type="hidden" value="{app_name}">
   <td align="center"><input type="submit" name="addphrase" value="{lang_addphrase}"></td>
  </form>
  <form method="post" action="{revert_url}">
   <td align="center"><input name="revert" type="submit" value="{lang_revert}"></td>
  </form>
  <form method="post" action="{cancel_link}">
   <td align="center"><input type="submit" name="cancel" value="{lang_cancel}"></td>
  </tr>
  </form>
</table>
<!-- END footer -->
