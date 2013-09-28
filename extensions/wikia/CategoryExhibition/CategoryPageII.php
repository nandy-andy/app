<?php
/**
 * Special handling for category description pages
 * Modelled after ImagePage.php
 *
 */

if( !defined( 'MEDIAWIKI' ) )
	die( 1 );

/**
 */
class CategoryPageII extends CategoryPage {

	// VOLDEV-16: Load CategoryTree extension if we're in list mode
	public function __construct( Title $title ) {
		parent::__construct( $title );

		$this->app = F::app();
		if ( !( $this instanceof CategoryExhibitionPage ) &&
			$this->app->wg->EnableCategoryTreeExt === true ) {
			$this->mCategoryViewerClass = 'CategoryTreeCategoryViewer';
		}
	}
	
	function openShowCategory() {
		wfProfileIn( __METHOD__ );

		$output = $this->getContext()->getOutput();
		$output->addStyle( AssetsManager::getInstance()->getSassCommonURL('extensions/wikia/CategoryExhibition/css/CategoryExhibition.scss'));
		$output->addStyle( AssetsManager::getInstance()->getSassCommonURL('extensions/wikia/CategoryExhibition/css/CategoryExhibition.IE.scss'), '', 'lte IE 8' );
		$output->addScript( "<script type=\"{$this->app->wg->JsMimeType}\" src=\"{$this->app->wg->ExtensionsPath}/wikia/CategoryExhibition/js/CategoryExhibition.js\" ></script>\n" );

		$title = $this->getTitle();
		$categoryExhibitionSection = new CategoryExhibitionSection( $title );
		$categoryExhibitionSection->setSortTypeFromParam();
		$categoryExhibitionSection->setDisplayTypeFromParam();
		$oTmpl = new EasyTemplate( dirname( __FILE__ ) . "/templates/" );
		$oTmpl->set_vars(
			array(
				'path' => $title->getFullURL(),
				'current' => $categoryExhibitionSection->getSortType(),
				'sortTypes' => $categoryExhibitionSection->getSortTypes(),
				'displayType' => $categoryExhibitionSection->getDisplayType(),
			)
		);
		$output->addHTML( $oTmpl->render( "form" ) );

		wfProfileOut( __METHOD__ );
	}
}
