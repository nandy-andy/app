<?php

class MyHome {

	// prefix for our custom data stored in rc_params
	const customDataPrefix = "\x7f\x7f";

	// use JSON to encode data?
	const useJSON = true;

	// name of section edited
	private static $editedSectionName = false;

	/*
	 * Store custom data in rc_params field as JSON encoded table prefixed with extra string
	 *
	 * @see http://www.mediawiki.org/wiki/Logging_table#log_params
	 *
	 * @author Maciej Brencz <macbre@wikia-inc.com>
	 */
	public static function storeInRecentChanges($rc) {
		wfProfileIn(__METHOD__);

		global $wgParser;

		// data be stored in rc_params
		$data = array();

		// summary generated by MW: store auto-summary type
		if (Wikia::isVarSet('AutoSummaryType')) {
			$data['autosummaryType'] = Wikia::getVar('AutoSummaryType');
		}

		switch($rc->getAttribute('rc_type')) {
			// existing article
			case RC_EDIT:
				// rollback: store ID of the revision rollback is made to
				if (Wikia::isVarSet('RollbackedRevId')) {
					$data['rollback'] = true;
					$data['revId'] = Wikia::getVar('RollbackedRevId');
				}

				// edit from view mode
				if (Wikia::isVarSet('EditFromViewMode')) {
					$data['viewMode'] = 1;
				}

				// section edit: store section name and modified summary
				if (self::$editedSectionName !== false) {
					// store section name
					$data['sectionName'] = self::$editedSectionName;

					// edit summary
					$comment = trim($rc->getAttribute('rc_comment'));

					// summary has changed - store modified summary
					if (!preg_match('#^/\*(.*)\*/$#', $comment)) {
						// remove /* Section title */
						$comment = preg_replace('#/\*(.*)\*/#', '', $comment);

						// remove all wikitext
						$comment = trim($wgParser->stripSectionName($comment));

						if ($comment != '') {
							$data['summary'] = $comment;
						}
					}
				}
				break;

			// new article
			case RC_NEW:
				$content = $wgParser->getOutput()->getText();

				// remove [edit] section links
				$content = preg_replace('#<span class="editsection">(.*)</a>]</span>#', '', $content);
				$content = trim(strip_tags($content));

				// store first 150 characters of parsed content
				$data['intro'] = mb_substr($content, 0, 150);
				$data['intro'] = strtr($data['intro'], array('&nbsp;' => ' ', '&amp;' => '&'));

				break;
		}

		// encode data to be stored in rc_params
		if (!empty($data)) {
			$rc->mAttribs['rc_params'] = self::packData($data);
		}

		Wikia::setVar('rc', &$rc);
		Wikia::setVar('rc_data', $data);

		//var_dump($rc); die();

		wfProfileOut(__METHOD__);

		return true;
	}

	/*
	 * Add link to Special:MyHome in Monaco user menu
	 *
	 * @author Maciej Brencz <macbre@wikia-inc.com>
	 */
	public static function addToUserMenu($skin, $tpl, $custom_user_data) {
		wfProfileIn(__METHOD__);

		// don't touch anon users
		global $wgUser;
		if ($wgUser->isAnon()) {
			return true;
		}

		wfLoadExtensionMessages('MyHome');

		$skin->data['userlinks']['myhome'] = array(
			'text' => wfMsg('myhome'),
			'href' => Skin::makeSpecialUrl('MyHome'),
		);

		wfProfileOut(__METHOD__);

		return true;
	}

	/*
	 * Check if it's section edit, then try to get section name
	 *
	 * @see http://www.mediawiki.org/wiki/Manual:Hooks/EditFilter
	 *
	 * @author Maciej Brencz <macbre@wikia-inc.com>
	 */
	public static function getSectionName($editor, $text, $section, &$error) {
		wfProfileIn(__METHOD__);

		global $wgParser;

		// check for section edit
		if (is_numeric($section)) {
			$hasmatch = preg_match( "/^ *([=]{1,6})(.*?)(\\1) *\\n/i", $editor->textbox1, $matches );

			if ( $hasmatch and strlen($matches[2]) > 0 ) {
				// this will be saved in recentchanges table in MyHome::storeInRecentChanges
				self::$editedSectionName = $wgParser->stripSectionName($matches[2]);
			}
		}

		wfProfileOut(__METHOD__);

		return true;
	}

	/*
	 * Return page user is redirected to when title is not specified in URL
	 *
	 * @author Maciej Brencz <macbre@wikia-inc.com>
	 */
	public static function getInitialMainPage($title) {
		wfProfileIn(__METHOD__);

		global $wgUser, $wgTitle;

		// dirty hack to make skin chooser work ($wgTitle is not set at this point yet)
		$wgTitle = Title::newMainPage();

		// do not redirect for skins different then monaco
		if(get_class($wgUser->getSkin()) != 'SkinMonaco') {
			return true;
		}

		// user must be logged in and have redirect enabled
		if ($wgUser->isLoggedIn() && ($wgUser->getOption('myhomedisableredirect') != true) ) {
			$title = Title::newFromText('MyHome', NS_SPECIAL);
		}

		wfProfileOut(__METHOD__);

		return true;
	}

	public static function addNewAccount($user) {
		global $wgOut, $wgUser;

		// do not redirect for skins different then monaco
		if(get_class($wgUser->getSkin()) == 'SkinMonaco') {
			$wgOut->redirect(Skin::makeSpecialUrl('MyHome'));
		}

		return true;
	}

	/*
	 * Store list of images, videos and categories added to an article
	 */
	public static function getInserts($linksUpdate) {

		// get rc_data from current row from recentchanges
		$rc_data = Wikia::getVar('rc_data');

		// nothing has changed yet
		$doUpdate = false;

		// store list of added images and videos
		$imageInserts = Wikia::getVar('imageInserts');
		if(!empty($imageInserts)) {
			foreach($imageInserts as $one) {
				$rc_data['imageInserts'][] = $one['il_to'];
			}
			$doUpdate = true;
		}

		// store list of added categories
		$categoryInserts = Wikia::getVar('categoryInserts');
		if (!empty($categoryInserts)) {
			foreach($categoryInserts as $cat => $page) {
				$rc_data['categoryInserts'][] = $cat;
			}
			$doUpdate = true;
		}

		// update if necessary
		if ($doUpdate) {
			$rc = Wikia::getVar('rc');
			if ( is_object( $rc ) ) {
				$rc_id = $rc->getAttribute('rc_id');

				$dbw = wfGetDB( DB_MASTER );
				$dbw->update('recentchanges', array('rc_params' => MyHome::packData($rc_data)), array('rc_id' => $rc_id));
			}
		}
		return true;
	}

	/*
	 * Return encoded (serialized/jsonized) data with extra prefix which can be stored in rc_params
	 *
	 * @author Maciej Brencz <macbre@wikia-inc.com>
         */
	public static function packData($data) {
		if (self::useJSON) {
			$packed = json_encode($data);
		}
		else {
			$packed = serialize($data);
		}

		// store encoded data with our custom prefix
		return self::customDataPrefix . $packed;
	}

	/*
	 * Return decoded (unserialized/unjsonized) data stored in rc_params
	 *
	 * @author Maciej Brencz <macbre@wikia-inc.com>
         */
	public static function unpackData($field) {
		wfProfileIn(__METHOD__);

		// extra check
		if (!is_string($field) || $field == '') {
			wfProfileOut(__METHOD__);
			return null;
		}

		// try to get our custom prefix
		$prefix = substr($field, 0, strlen(self::customDataPrefix));

		if ($prefix != self::customDataPrefix) {
			wfProfileOut(__METHOD__);
			return null;
		}

		// get encoded data
		$field = substr($field, strlen(self::customDataPrefix));

		// and try to unpack it
		try {
			if (self::useJSON) {
				$data = json_decode($field, true);
			}
			else {
				$data = unserialize($field);
			}
		}
		catch(Exception $e) {
			$data = null;
		}

		wfProfileOut(__METHOD__);

		return $data;
	}

	/*
	 * Add "Disable my redirect to My Home" switch to Special:Preferences (Misc tab)
	 *
	 * @author Maciej Brencz <macbre@wikia-inc.com>
         */
	public static function userToggles($toggles) {
		$toggles[] = 'myhomedisableredirect';
		return true;
	}

	/*
	 * Save default view in user preferences (can be either "watchlist" or "activity")
	 *
	 * @author Maciej Brencz <macbre@wikia-inc.com>
         */
	public static function setDefaultView($defaultView) {

		wfProfileIn(__METHOD__);

		global $wgUser;

		// correct values
		$values = array('activity', 'watchlist');

		if (in_array($defaultView, $values)) {
			$wgUser->setOption('myhomedefaultview', $defaultView);
			$wgUser->saveSettings();

			$dbw = wfGetDB( DB_MASTER );
			$dbw->commit();

			wfProfileOut(__METHOD__);

			return true;
		}

		wfProfileOut(__METHOD__);

		return false;
	}

	/*
	 * Get default view from user preferences (can be either "watchlist" or "activity")
	 *
	 * @author Maciej Brencz <macbre@wikia-inc.com>
         */
	public static function getDefaultView() {

		wfProfileIn(__METHOD__);

		global $wgUser;
		$defaultView = $wgUser->getOption('myhomedefaultview');

		if (empty($defaultView)) {
			$defaultView = 'activity';
		}

		wfProfileOut(__METHOD__);

		return $defaultView;
	}
}
