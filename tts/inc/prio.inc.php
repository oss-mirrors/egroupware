<?PHP

function prioname ($prio) {
    /* this just returns the name (remark) for the given prio level
     * MSc 060127
     */
    static $prioarr = array ( 0 => 'emergency',
			      1 => 'critical',
			      2 => 'high',
			      3 => 'low',
			      4 => 'wish' );
    
    return $prioarr[$prio];
}

function generate_priowarn () {
    /* generates JavaScript for hooking into a change of the prio-dropdown,
     * to alert() whenever the user is trying a too high prio
     * We use short messages here which should be expanded by lang()
     * MSc 060130
     */

    static $warns = array ( 0 => 'alert for emergency prio setting',
			    1 => 'alert for critical prio setting',
			    2 => 'alert for high prio setting'
	    );	// non-set level receive no warning

?>
<script type="text/javascript">
  function generate_priowarn (prio) {
<?PHP

    foreach ($warns as $level => $message) {
	printf ("if (prio == %d) {\n  alert('%s');\n} else ",
	       $level, lang($message));
    }

?>
{
     return (true);
}
  }
</script>

<?PHP
} // end function generate_priowarn

    
?>
