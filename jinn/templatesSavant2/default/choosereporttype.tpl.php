<form method="post">
   <input type="hidden" name="report_id" value="<?=$_GET['report_id']?>" />
   <?=lang('Select type type of your report.');?>
   &nbsp;
   <select name="report_type_name">
	  <option value="htmlreport">HTML &gt;&gt; HTML</option>
	  <option value="pdmlreport">PDML &gt;&gt; PDF</option>
   </select>
   <br/>
   <input type="submit" value="<?=lang('Next')?>" />
   <input type="button" value="<?=lang('Cancel')?>" onclick="parent.window.location.href='<?=$this->returnlink?>'" />
</form>
