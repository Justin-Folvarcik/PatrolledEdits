<?php

if (!defined('MEDIAWIKI')) die();
/**
 * An extension that displays patrolled edits.
 *
 *
 * @addtogroup Extensions
 *
 * @author Justin Folvarcik (jfolvarcik@gmail.com)
 * @copyright Copyright 2014, Justin Folvarcik
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */
$wgExtensionCredits['parserhook'][] = array(
    'path' => __FILE__,
    'name' => 'Patrolled Edits',
    'author' => '[http://zeldawiki.org/User:Justin Justin Folvarcik]',
    'description' => 'Adds a {{#pe}} parser function for displaying the number of edits a user has patrolled.',
    'url' => 'http://zeldawiki.org/User_talk:Justin',
);

// Define a setup function
$wgHooks['ParserFirstCallInit'][] = 'PatrolledEdits_Setup';
// Add a hook to initialise the magic word
$wgHooks['LanguageGetMagic'][]    = 'PatrolledEdits_Magic';

/**
 * Setup function for the parser hook
 * Associates {{#pe}} with the function
 * @param Parser $parser
 * @return bool
 */
function PatrolledEdits_Setup( Parser &$parser ) {
    $parser->setFunctionHook( 'pe', 'PatrolledEdits_Render' );
    return true;
}

/**
 * Add the magic word and define some settings for it
 * @param $magicWords
 * @return bool
 */
function PatrolledEdits_Magic( &$magicWords ) {
    // The first array element is whether to be case sensitive, in this case (0) it is not case sensitive, 1 would be sensitive
    // All remaining elements are synonyms for the magic word
    $magicWords['pe'] = array( 0, 'pe', 'patrollededits' );
    // Unless this returns true, extension loading for the parser hooks will end here
    return true;
}

function PatrolledEdits_Render( $parser, $user = '', $type = '', $namespace = '' ) {
    // The parser function itself
    // The input parameters are wikitext with templates expanded
    // The output should be wikitext too

    // If they didn't define a user, we can't do anything. Bail out now.
    if ($user==null)
        return false;
    // First, grab an instance of a DB object
    $dbr = wfGetDB(DB_SLAVE);
    // Store the table name as a constant
    define('PatrolledEdits_log', $dbr->tableName('logging'));
    // Start building the base query
    $id = User::idFromName($user);
    $query = "SELECT COUNT(*) as count FROM " . PatrolledEdits_log . " WHERE `log_user` = '{$id}' AND `log_type` = 'patrol' ";
    // Escape the input just to be safe, and then get the user's ID
    $user = mysql_real_escape_string($user);
    // If they've declared a specific namespace, escape it and append it to the query
    if ($namespace && is_integer($namespace))
    {
        $ns = mysql_real_escape_string($namespace);
        $query .= "AND `log_namespace` = {$ns} ";
    }
    if ($type == 'patrol')
    {
        $query .= "AND LEFT(RIGHT(`log_params`, 3), 1) = '0' ";
    }
    elseif ($type == 'auto')
    {
        $query .= "AND LEFT(RIGHT(`log_params`, 3), 1) = '1'";
    }

    $result = $dbr->query($query, __METHOD__);
    return $result->current()->count;
}
