<?php if(!$GLOBALS['egw_info']['flags']['tinyMCE_JiNN']):?>
<?php $GLOBALS['egw_info']['flags']['tinyMCE_JiNN']=true?>
<script language="javascript" type="text/javascript" src="<?=$GLOBALS[egw_info][server][webserver_url]?>/jinn/plugins/db_fields_plugins/__tinymce/js/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<?php endif?>
<script language="javascript" type="text/javascript">
   tinyMCE.init({
		 mode : "exact",
		 language: "<?=$GLOBALS['egw_info']['user']['preferences']['common']['lang']?>",
		 plugin_insertdate_dateFormat : "<?=str_replace(array('Y','m','M','d'),array('%Y','%m','%b','%d'),$GLOBALS['egw_info']['user']['preferences']['common']['dateformat'])?>",
		 plugin_insertdate_timeFormat : "<?=($GLOBALS['egw_info']['user']['preferences']['common']['timeformat'] == 12 ? '%I:%M %p' : '%H:%M')?>",
		 elements : "<?=$this->name?>",
		 strict_loading_mode : true,
		 <?=$init_options?>
   });
   tinyMCE.onLoad();
</script>
<textarea id="<?=$this->name?>" name="<?=$this->name?>" style="<?=$this->style?>">
   <?=htmlspecialchars($this->content)?>
</textarea>
