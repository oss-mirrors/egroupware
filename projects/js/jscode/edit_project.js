var tab = new Tabs(3,'activetab','inactivetab','tab','tabcontent','','','tabpage');

function initAll()
{
	tab.init();
}

function updateAccountingForm(_selectBox)
{
	$radioHour = document.getElementById("radio_acc_factor_hour");
	$radioDay = document.getElementById("radio_acc_factor_day");
	$inputHour = document.getElementById("input_acc_factor_hour");
	$inputDay = document.getElementById("input_acc_factor_day");
	
	if(_selectBox.value == 'project')
	{
		$radioHour.disabled = false;
		$radioDay.disabled = false;
		$inputHour.disabled = false;
		$inputDay.disabled = false;
	}
	else
	{
		$radioHour.disabled = true;		
		$radioDay.disabled = true;		
		$inputHour.disabled = true;
		$inputDay.disabled = true;
	}
}

var oldNumberInputValue;

function changeProjectIDInput($_selectBox)
{
	$numberInput = eval(document.getElementById("id_number"));
	if($_selectBox.checked == true)
	{
		$numberInput.disabled = true;
		$oldNumberInputValue = $numberInput.value;
		$numberInput.value = '';
	}
	else
	{
		$numberInput.disabled = false;
		$numberInput.value = $oldNumberInputValue;
	}
}