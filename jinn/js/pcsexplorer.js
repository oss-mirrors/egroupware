<!--
// --------------------------------------------------------------------------------
// PhpConcept Library (PCL) Trace 1.0
// --------------------------------------------------------------------------------
// License GNU/GPL - Vincent Blavet - Janvier 2001
// http://www.phpconcept.net & http://phpconcept.free.fr
// --------------------------------------------------------------------------------
// Français :
//   La description de l'usage de la librairie PCL Trace 1.0 n'est pas encore
//   disponible. Celle-ci n'est pour le moment distribuée qu'avec l'application
//   et la librairie PhpZip.
//   Une version indépendante sera bientot disponible sur http://www.phpconcept.net
//
// English :
//   The PCL Trace 1.0 library description is not available yet. This library is
//   released only with PhpZip application and library.
//   An independant release will be soon available on http://www.phpconcept.net
//
// --------------------------------------------------------------------------------
//
//   * Avertissement :
//
//   Cette librairie a été créée de façon non professionnelle.
//   Son usage est au risque et péril de celui qui l'utilise, en aucun cas l'auteur
//   de ce code ne pourra être tenu pour responsable des éventuels dégats qu'il pourrait
//   engendrer.
//   Il est entendu cependant que l'auteur a réalisé ce code par plaisir et n'y a
//   caché aucun virus, ni malveillance.
//   Cette libairie est distribuée sous la license GNU/GPL (http://www.gnu.org)
//
//   * Auteur :
//
//   Ce code a été écrit par Vincent Blavet (vincent@blavet.net) sur son temps
//   de loisir.
//
// --------------------------------------------------------------------------------
// --------------------------------------------------------------------------------
// PhpConcept Script - Explorer
// --------------------------------------------------------------------------------
// License GNU/GPL - Vincent Blavet - July 2001
// http://www.phpconcept.net
// --------------------------------------------------------------------------------
// Overview :
//   PcsExplorer is a Javascript/PHP script that compose a file or directory
//   selection window.
//
// Description :
//   PcsExplorer is composed of a Javascript file 'pcsexplorer.js', a PHP file
//   'pcsexplorer.php3' and a folder 'images'.
//   The PHP calling script must include the javascript file, and call PcsExplorer
//   like this (sample) :
//     <script language="javascript" src="pcsexplorer.js"></script>
//     <FORM  name="formulaire" method="POST">
//     File : <INPUT TYPE="TEXT" size=50 name="file"><br>
//     <INPUT TYPE="button" name="brows_button" value="Browse" onClick='PcjsOpenExplorer("path/pcsexplorer.php3", "forms.formulaire.file.value", "type=file", "calling_dir=<? echo dirname($PATH_INFO); ?>", "start_dir=../..")'>
//     </FORM>
//   The arguments took by PcjsOpenExplorer() are described in the function header
//   bellow.
//   The arguments took by the PcsExplorer PHP script are described in
//   pcsexplorer.php3 file.
// --------------------------------------------------------------------------------

// ----- Global variable
var v_win=0;

// --------------------------------------------------------------------------------
// Function : PcjsOpenExplorer(p_url, p_target, ... p_properties ...)
// Description :
// Parameters :
//   p_url : The URL where sit the pcsexplorer.php3 PHP script. Should be
//           an absolute value ("http://www.mysite.com/pcsexplorer.php3") or
//           a relative value from the calling PHP script ("../pcsexplorer.php3")
//   p_target : The javascript value target. For example for a form with name
//              'my_form', with a TEXT INPUT, with name 'my_text', it should be
//              "forms.my_form.my_text.value". Any javascript object contained
//              in object "document" can be used.
//   p_properties : A variable list of optional properties. Each element of this
//                  must be a string, with the name of the property, equal the
//                  value ("property_name=my_value").
//                  Warning : No space " " is allowed defore and after the "=".
//                  The following properties are defined :
//                    type=file|dir : PcsExplorer explore files & dir,
//                                    or only dir.
//                    filter=my_filter : my_filter is a regular expression
//                                       (future feature).
//                    position=absolute|relative : absolute means absolute from
//                                                 site home dir, relative means
//                                                 relative to the calling script
//                                                 position.
//                    calling_dir=<dirname> : the absolute path from site root
//                                            of the calling PHP script.
//                    start_dir=<dirname> : the relative path from the calling PHP
//                                          script where to start the exploration.
// --------------------------------------------------------------------------------
function PcjsOpenExplorer(p_url, p_target /*, p_properties, p_properties, ... */)
{
	// ----- Check the number of arguments
	if (arguments.length < 2)
	{
		alert("Invalid number of arguments for PcjsOpenExplorer()");
		return false;
	}

	// ----- Compose the basic called URL
	var v_url = p_url+"?a_target="+escape(p_target);

	// ----- Look for optional properties
	for (i=2; i<arguments.length; i++)
	{
		// ----- Extract the property name and property value
		var v_item = arguments[i].split("=", 2);

		// ----- Complete the URL
		v_url = v_url+"&a_"+escape(v_item[0])+"="+escape(v_item[1]);
	}

	// ----- Set & calculate window size
	var v_width=340;
	var v_height=400;
	var v_left = (screen.width-v_width)/2;
	var v_top = (screen.height-v_height)/2;

	// ----- Set window properties
	var v_settings = 'width='+v_width+',height='+v_height+',top='+v_top+',left='+v_left+',scrollbars=no,location=no,directories=no,status=no,menubar=no,toolbar=no,resizable=yes';

	// ----- Open window
	v_win = window.open(v_url,"PhpConceptExplorer",v_settings);

	// ----- Give focus to window
	v_win.focus();
}
// --------------------------------------------------------------------------------

// -->

