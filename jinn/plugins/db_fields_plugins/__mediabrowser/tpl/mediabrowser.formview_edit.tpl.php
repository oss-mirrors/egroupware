<script>
   function onBrowseMedia(field) 
   {
		 childWindow=open("jinn/plugins/db_fields_plugins/__mediabrowser/popup.mediabrowser.php?site_id=<?=$this->site_id?>&config2base64=<?=$this->config2base64?>","console","resizable=yes,scrollbars=yes,width=800,height=540");
   }
</script>
<input type="button" onclick="onBrowseMedia('')" value="<?=lang('open media browser')?>"/>
