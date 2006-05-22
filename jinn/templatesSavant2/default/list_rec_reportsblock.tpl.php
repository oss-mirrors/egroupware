<br>
<table  cellpadding="0" cellspacing="0" style="border:solid 1px #cccccc">
<tr>
	<td align="center" style="padding-left:20px;">
		<form action="<?=$this->report_url?>" method="post" name='report_actie'>Report's&nbsp;
		<select name='report' >
		<?=$this->listoptions?>
		</select>
		<input type='button'  class="egwbutton" value='<?=$this->lang_merge?>' onClick="parent.window.open('<?=$this->report_url?>&report_id='+document.report_actie.report.value +'&selvalues=' +returnSelectedCheckbox(), 'pop', 'width=800,height=600,location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no')">
		<?=$this->r_edit_button?>
		<input type='button' class="egwbutton" value='<?=$this->lang_new_report?>'onClick="window.open('<?=$this->add_report_url?>', 'pop', 'width=800,height=600,location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no')">
		<?=$this->r_new_from_button?>
		</form>	
	</td>
</tr>
</table>
