<?php
if (!defined('MEDIAWIKI'))
die();
$wgExtensionCredits['parserhook'][] = array(
	'name' => 'Patrolled Magic',
	'author' => '[http://zeldawiki.org/User:Justin Justin Folvarcik]',
	'description' => 'Adds several magic words and a parser function for displaying info about all edits patrolled.',
	'url' => 'http://zeldawiki.org/User_talk:Justin',
);
// Namespace specific one.
$wgHooks['ParserFirstCallInit'][] = 'PatrolledNSMagic_Setup';
// Magic word.
$wgHooks['LanguageGetMagic'][]       = 'PatrolledNSMagic_Magic';

function PatrolledNSMagic_Setup( Parser &$parser ) {

	$parser->setFunctionHook( 'patrolnsmagic', 'PatrolledNSMagic_Render', SFH_NO_HASH );
	return true;
}

function PatrolledNSMagic_Magic( &$magicWords, $langCode ) {
        # Add the magic word
        # The first array element is whether to be case sensitive, in this case (0) it is not case sensitive, 1 would be sensitive
        # All remaining elements are synonyms for our parser function
        $magicWords['patrolnsmagic'] = array( 1, 'PATROLLEDINNAMESPACE' );
        # unless we return true, other parser functions extensions won't get loaded.
        return true;
}
 
function PatrolledNSMagic_Render( $parser, $param1 = '', $param2 = '' ) {
		$i = 0;
		$dbr = wfGetDB(DB_SLAVE);
		if ($param1 == null || !is_numeric($param1))
		return false;
		else{
		if ($param2 == "auto")
		$type = "AND RIGHT(`log_params`, 1) = 1";
		elseif ($param2 == "patrol")
		$type = "AND RIGHT(`log_params`, 1) = 0";
		else
		$type = null;
		$ns = mysql_real_escape_string($param1);
        //TODO: This is evidence of how nooby I was. Needs to be changed to a simple COUNT(*)
		$res = $dbr->query("SELECT COUNT(* FROM ".$dbr->tableName('logging')." WHERE `log_type` = 'patrol' AND `log_namespace` = '{$ns}' {$type}", __METHOD__);
		foreach ($res as $row)
		$i++;
		$output = $i;
        return $output;
}
}

define('PatrolledVar', 'PatrolledVar');
$wgHooks['LanguageGetMagic'][] = 'wfMyWikiWords';
function wfMyWikiWords(&$aWikiWords, $langID) {
  $aWikiWords[PatrolledVar] = array(1, 'TOTALPATROLLED');
  return true;
}
 
$wgHooks['ParserGetVariableValueSwitch'][] = 'wfMyAssignAValue';
function wfMyAssignAValue(&$parser, &$cache, &$magicWordId, &$ret) {
  if (PatrolledVar == $magicWordId) {
  		$i = 0;
		$dbr = wfGetDB(DB_SLAVE);
        //TODO: Change to a COUNT(*) query
		$res = $dbr->query("SELECT * FROM ".$dbr->tableName('logging')." WHERE `log_type` = 'patrol'", __METHOD__);
		foreach ($res as $row)
		$i++;
		$ret = $i;
		}
  return true;
}

$wgHooks['MagicWordwgVariableIDs'][] = 'wfMyDeclareVarIds';
function wfMyDeclareVarIds(&$aCustomVariableIds) {
  $aCustomVariableIds[] = PatrolledVar;
  return true;
}

define('MarkedVar', 'Marked');
$wgHooks['LanguageGetMagic'][] = 'MarkedPatrolled';
function MarkedPatrolled(&$aWikiWords, $langID) {
  $aWikiWords[MarkedVar] = array(1, 'TOTALMARKEDPATROLLED');
  return true;
}
 
$wgHooks['ParserGetVariableValueSwitch'][] = 'MarkedPatrolledValue';
function MarkedPatrolledValue(&$parser, &$cache, &$magicWordId, &$ret) {
  if (MarkedVar == $magicWordId) {
  		$i = 0;
		$dbr = wfGetDB(DB_SLAVE);
        //TODO: COUNT(*)
		$res = $dbr->query("SELECT * FROM ".$dbr->tableName('logging')." WHERE `log_type` = 'patrol' AND RIGHT(`log_params`, 1) = 0", __METHOD__);
		foreach ($res as $row)
		$i++;
		$ret = $i;
		}
  return true;
}

$wgHooks['MagicWordwgVariableIDs'][] = 'MarkedPatrolledIds';
function MarkedPatrolledIds(&$aCustomVariableIds) {
  $aCustomVariableIds[] = MarkedVar;
  return true;
}

define('AutoVar', 'AutoVar');
$wgHooks['LanguageGetMagic'][] = 'AutoPatrolled';
function AutoPatrolled(&$aWikiWords, $langID) {
  $aWikiWords[AutoVar] = array(1, 'TOTALAUTOPATROLLED');
  return true;
}
 
$wgHooks['ParserGetVariableValueSwitch'][] = 'AutoPatrolledValue';
function AutoPatrolledValue(&$parser, &$cache, &$magicWordId, &$ret) {
  if (AutoVar == $magicWordId) {
  		$i = 0;
		$dbr = wfGetDB(DB_SLAVE);
        //TODO: COUNT(*)
		$res = $dbr->query("SELECT * FROM ".$dbr->tableName('logging')." WHERE `log_type` = 'patrol' AND RIGHT(`log_params`, 1) = 1", __METHOD__);
		foreach ($res as $row)
		$i++;
		$ret = $i;
		}
  return true;
}

$wgHooks['MagicWordwgVariableIDs'][] = 'AutoPatrolledIds';
function AutoPatrolledIds(&$aCustomVariableIds) {
  $aCustomVariableIds[] = AutoVar;
  return true;
}

?>