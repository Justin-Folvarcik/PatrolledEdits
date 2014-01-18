<?php

if (!defined('MEDIAWIKI')) die();
/**
 * An extension that displays patrolled edits.
 *
 *
 * @addtogroup Extensions
 *
 * @author Justin Folvarcik (jfolvarcik@gmail.com)
 * @copyright Copyright © 2014, Justin Folvarcik
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */
$wgExtensionCredits['parserhook'][] = array(
	'name' => 'Patrolled Edits',
	'author' => '[http://zeldawiki.org/User:Justin Justin Folvarcik]',
	'description' => 'Adds a {{#pe}} parser function for displaying the number of edits a user has patrolled.',
	'url' => 'http://zeldawiki.org/User_talk:Justin',
);

# Define a setup function
$wgHooks['ParserFirstCallInit'][] = 'PatrolledEdits_Setup';
# Add a hook to initialise the magic word
$wgHooks['LanguageGetMagic'][]    = 'PatrolledEdits_Magic';

$dir = dirname(__FILE__) . '/';
 
function PatrolledEdits_Setup( &$parser ) {
	# Set a function hook associating the "pe" magic word with our function
	$parser->setFunctionHook( 'pe', 'PatrolledEdits_Render' );
	return true;
}
 
function PatrolledEdits_Magic( &$magicWords, $langCode ) {
        # Add the magic word
        # The first array element is whether to be case sensitive, in this case (0) it is not case sensitive, 1 would be sensitive
        # All remaining elements are synonyms for our parser function
        $magicWords['pe'] = array( 0, 'pe', 'patrollededits' );
        # unless we return true, other parser functions extensions won't get loaded.
        return true;
}
 
function PatrolledEdits_Render( $parser, $param1 = '', $param2 = '', $param3 = '' ) {
        # The parser function itself
        # The input parameters are wikitext with templates expanded
        # The output should be wikitext too
        $dbw = wfGetDB(DB_SLAVE);
        if ($param1==null)
        return false;
        else
        {
        $param1 = mysql_real_escape_string($param1);
		@$id = User::idFromName($param1);
		if (isset($param3) && is_numeric($param3))
		{
			$param3++;
			$ns = mysql_real_escape_string($param3);
		}
		else
		$ns=null;
        //TODO: COUNT(*) queries instead of... this abomination.
		if ($param2 == 'patrol')
		{
		if ($ns == null)
		{
		$res = $dbr->query("SELECT * FROM ".$dbr->tableName('logging')." WHERE `log_user` = '{$id}' AND `log_type` = 'patrol' AND RIGHT(`log_params`, 1) = '0'", 'Database::query');
		}
		else
		{
		$ns--;
		$res = $dbr->query("SELECT * FROM ".$dbr->tableName('logging')." WHERE `log_user` = '{$id}' AND `log_type` = 'patrol' AND RIGHT(`log_params`, 1) = '0' AND `log_namespace` = '{$ns}'", 'Database::query');
		}
		}
		elseif ($param2 == 'auto')
		{
		if ($ns == null)
		$res = $dbr->query("SELECT * FROM ".$dbr->tableName('logging')." WHERE `log_user` = '{$id}' AND `log_type` = 'patrol' AND RIGHT(`log_params`, 1) = '1'", __METHOD__);
		else
		{
		$ns--;
		$res = $dbr->query("SELECT * FROM ".$dbr->tableName('logging')." WHERE `log_user` = '{$id}' AND `log_type` = 'patrol' AND RIGHT(`log_params`, 1) = '1' AND `log_namespace` = '{$ns}'", __METHOD__);
		}
		}
		else
		{
			if ($ns == null)
			{
			$res = $dbr->query("SELECT * FROM ".$dbr->tableName('logging')." WHERE `log_user` = '{$id}' AND `log_type` = 'patrol'", __METHOD__);
			}
			else
			{
			$ns--;
			$res = $dbr->query("SELECT * FROM ".$dbr->tableName('logging')." WHERE `log_user` = '{$id}' AND `log_type` = 'patrol' AND `log_namespace` = '{$ns}'", __METHOD__);
			}
		}
		$i=0;
		foreach ($res as $row)
		$i++;
		$output = $i;
        return $output;
	}
	}
?>