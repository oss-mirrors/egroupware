<script>
   function startC()
   {
		 var movieurl = get_source_movie();
		 if(movieurl=='' || !movieurl)
		 {
			   alert('<?php echo lang('Please first upload a source movie to convert')?>');
		 }
		 else
		 {
			   xajax_doXMLHTTP("jinn.ajaxjinn.plg_forw",'flvconvertclient.addToQueue',movieurl);
		 }
   }

   function restartC()
   {
//		 xajax_doXMLHTTP("jinn.ajaxjinn.plg_forw",'flvconvertclient.helloWorld2','Pim Snel');
   }

   function get_source_movie()
   {
		 var srcfields=document.getElementsByName('<?php echo $this->sourcefield?>');
		 var srcfmfields=document.getElementsByName('<?php echo $this->fmsourcefield?>');

		 if(srcfields.length < 1 && srcfmfields.length < 1) 
		 {
			   alert('<?php echo lang('Configured source field is not correct')?>');
			   return;
		 }

		 if(srcfmfields.length > 0 && !srcfmfields[0].value=='' &&  srcfmfields[0].value!='delete')
		 {
			   return srcfmfields[0].value;
		 }
		 if(srcfields.length > 0 && !srcfields[0].value=='' && srcfmfields[0].value!='delete')
		 {
			   return srcfields[0].value;
		 }
   }
</script>
<div style="color:#aaaaaa;text-align:center;vertical-align:middle;width:360px;height:240px;border: inset 2px #cccccc;background-color:black;">
   <?php echo $this->videoPreview?><?php echo lang('No FLV available yet.')?>
</div>

<input type="button" value="<?php echo lang('Start conversion')?>" onclick="startC();" />
<input type="button" value="<?php echo lang('Reset conversion')?>" onclick="restartC();" />
<input type="button" value="<?php echo lang('Remove FLV')?>" onclick="startC();" />
