<script  type="text/javascript" >
   var arrMonths = new Array();
   <?php foreach($this->month_arr as $key => $val):?>
   <?php if($val):?>
   arrMonths[<?php echo($key);?>]='<?php echo($val);?>';
   <?php endif?>
   <?php endforeach?>
   function formatDate(field_name)
   {
		 unf = document.getElementById(field_name);
		 formatted = document.getElementById('form_'+field_name);
		 date_arr = unf.value.split('/');
		 year = date_arr[0];
		 month = arrMonths[Number(date_arr[1])];
		 day = date_arr[2];
		 formatted.innerHTML = day + ' ' + month + ' ' + year;

   }
</script>

