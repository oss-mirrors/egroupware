<?

class Settings
{
	//IDs of admin-user, guest-user and root-folder (no need to change)
        //changed by dawnlinux @ realss.com to solve the problem of 
        // global category can only be created by user 1
	//var $_adminID = 1;
  	var $_adminID = 115;
	var $_guestID = 2;
	var $_rootFolderID = 1;
	
	//If you don't want anybody to login as guest, set the following line to false
	var $_enableGuestLogin = true;
	
	//path to where mydms is located
	var $_rootDir = "/var/www/gtz.realss.com/htdocs/mydms/";
	
	//the same as URL
	var $_httpRoot = "/mydms/";
	
	//where the uploaded files are stored (you better choose a directory that is not accessible through your web-server)
	var $_contentDir = "/var/www/gtz.realss.com/document_root/mydms/";
	
	//default language (name of a subfolder in folder "languages")
	var $_language = "English";
	
	//users are notified about document-changes that took place within the last $_updateNotifyTime seconds
	var $_updateNotifyTime = 86400; //means 24 hours
	
	//files with one of the following endings can be viewed online
	var $_viewOnlineFileTypes = array(".txt", ".html", ".htm", ".pdf", ".gif", ".png", ".jpg");
	
	//enable/disable converting of files
	var $_enableConverting = true;
	
	//default theme (name of a subfolder in folder "themes")
	var $_theme = "default";
	
	// -------------------------------- Database-Setup --------------------------------------------
	
	//Path to adodb
	var $_ADOdbPath = "/var/www/gtz.realss.com/htdocs/mydms/adodb/";
	
	//DB-Driver used by adodb (see adodb-readme)
	var $_dbDriver = "mysql";
	
	//DB-Server
	var $_dbHostname = "localhost";
	
	//database where the tables for mydms are stored (optional - see adodb-readme)
	var $_dbDatabase = "gtz_realss_com_mydms";
	
	//username for database-access
	var $_dbUser = "gtz_mydms";
	
	//password for database-access
	var $_dbPass = "gtz.realss.com.mydms";
	
	function Settings()
	{
		
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

$GLOBALS['phpgw_info']['flags'] = array(
        'currentapp' => 'mydms',
        'noheader'   => True,
        'nonavbar'   => True
);

include('../../header.inc.php');

?>
