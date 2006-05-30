<p>
<p>
<form name="formimport" action="<?=$this->form_action?>" method="post" enctype="multipart/form-data">
   <table align="center">
	  <tr>
		 <td><?=lang('Select JiNN site file')?></td>
		 <td><input size="30" readonly name="importfile" type="text" value="<?=$this->loaded_file?>"></td>
	  </tr>
	  <tr>
		 <td><?=lang('Replace existing Site with the same name')?></td>
		 <td><input name="replace_existing" type="checkbox" <?=$this->checked?>></td>
	  </tr>
	  <tr>
		 <td colspan="2"><input class="egwbutton" type="submit" name="incompatibility_ok" value="<?=lang('import anyway')?>"><input onClick="location='<?=$this->cancel_redirect?>'" class="egwbutton"   type="button" value="<?=lang('cancel')?>"></td>
	  </tr>
   </table>
</form>
