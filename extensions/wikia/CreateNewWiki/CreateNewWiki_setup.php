<?php

$wgExtensionCredits['specialpage'][] = array(
	'name' => 'CreateNewWiki',
	'descriptionmsg' => 'createnewwiki-desc',
	'author' => array('Hyun Lim')
);

$dir = __DIR__.'/';

// class autoloads mappings
$wgAutoloadClasses['CreateNewWikiObfuscate'] = $dir . 'CreateNewWikiObfuscate.class.php';
$wgAutoloadClasses['CreateWikiLocalJob'] = $dir."CreateWikiLocalJob.php";
$wgAutoloadClasses['CreateWiki'] = $dir."/CreateWiki.php";
$wgAutoloadClasses['AutoCreateWiki'] = $dir."/AutoCreateWiki.php";
$wgAutoloadClasses['CreateNewWikiController'] = $dir . 'CreateNewWikiController.class.php';
$wgAutoloadClasses['SpecialCreateNewWiki'] = $dir . 'SpecialCreateNewWiki.class.php';

// special page mapping
$wgSpecialPages['CreateNewWiki'] = 'SpecialCreateNewWiki';
$wgSpecialPages['CreateWiki'] = 'SpecialCreateNewWiki';

// i18n mapping
$wgExtensionMessagesFiles['CreateNewWiki'] = $dir . 'CreateNewWiki.i18n.php';

// permissions
$wgAvailableRights[] = 'createnewwiki';
$wgGroupPermissions['*']['createnewwiki'] = true;
$wgGroupPermissions['staff']['createnewwiki'] = true;

$wgExtensionFunctions[] = 'CreateNewWikiController::setupCreateNewWiki';
