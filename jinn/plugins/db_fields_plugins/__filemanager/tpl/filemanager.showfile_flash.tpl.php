<script language="JavaScript" type="text/JavaScript" src="<?=$this->flash_js?>"></script>
<script language="JavaScript" type="text/JavaScript">
   if(flashcompattest()==true)
   {
		 writeFlash(<?=$this->file_width?>,<?=$this->file_height?>,'<?=$this->file_url ?>','<?=$this->name?>');
   }
   else
   {
		 document.write('<img id="<?=$this->name?>" src="<?=$this->flash_icon?>" alt="<?=lang('flash')?>" />');
   }
</script>
<br/>
<a id="<?=$this->linkid?>" href="<?=$this->filelink?>"><span id="<?=$this->span_id?>"><?=$this->file_name?></span></a>


