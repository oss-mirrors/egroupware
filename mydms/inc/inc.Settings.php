<?
if(!is_array($GLOBALS['egw_info']))
{
	$GLOBALS['egw_info']['flags'] = array(
	        'currentapp' => 'mydms',
	        'noheader'   => True,
	        'nonavbar'   => True
	);

	include('../../header.inc.php');
}

class Settings
{
	//IDs of admin-user, guest-user and root-folder (no need to change)
	var $_adminID = 1;
	var $_guestID = 2;
	var $_rootFolderID = 1;
	
	//If you don't want anybody to login as guest, set the following line to false
	var $_enableGuestLogin = true;
	
	//default language (name of a subfolder in folder "languages")
	var $_language = "English";
	
	//users are notified about document-changes that took place within the last $_updateNotifyTime seconds
	var $_updateNotifyTime = 86400; //means 24 hours
	
	//files with one of the following endings can be viewed online
	var $_viewOnlineFileTypes = array(".txt", ".html", ".htm", ".pdf", ".gif", ".png", ".jpg");
	
	//enable/disable converting of files
	var $_enableConverting = false;
	
	//default theme (name of a subfolder in folder "themes")
	var $_theme = "default";
	
	function Settings()
	{
		//path to where mydms is located
		$this->_rootDir = EGW_SERVER_ROOT . '/mydms/';
	
		//where the uploaded files are stored (you better choose a directory that is not accessible through your web-server)
		$this->_contentDir = $GLOBALS['egw_info']['server']['files_dir'] . "/mydms/";
		//if the rootdir unexist or is not a directory, create it
		if(!is_dir($this->_contentDir)) {
			if($ret1 = is_writable($GLOBALS['egw_info']['server']['files_dir']))
				$ret2 = mkdir($this->_contentDir,0755);
			if(!$ret1 && $ret2)
				die('The Root Dir does not exist or Can not be created');
		}
	
		//the same as URL
	//	$this->_httpRoot = $GLOBALS['egw_info']['server']['webserver_url'] . "/mydms/";   // we use the following more professional one
		$this->_httpRoot = $GLOBALS['egw']->link('/mydms/');
	
		//files with one of the following endings will be converted with the given commands
		//for windows users
		$this->_convertFileTypes = array(".doc" => "cscript \"" . $this->_rootDir."op/convert_word.js\" {SOURCE} {TARGET}",
										 ".xls" => "cscript \"".$this->_rootDir."op/convert_excel.js\" {SOURCE} {TARGET}",
										 ".ppt" => "cscript \"".$this->_rootDir."op/convert_pp.js\" {SOURCE} {TARGET}");
		//for linux users
	//	$this->_convertFileTypes = array(".doc" => "mswordview -o {TARGET} {SOURCE}");
	}
}

$settings = new Settings();
$GLOBALS['mydms']->settings = $settings;

?>
