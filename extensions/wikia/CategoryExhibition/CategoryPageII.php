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

		if ( !( $this instanceof CategoryExhibitionPage ) ) {
			$this->mCategoryViewerClass = 'CategoryTreeCategoryViewer';
		}
	}
	
	function openShowCategory() {
		global $wgExtensionsPath, $wgJsMimeType;
		$output = $this->getContext()->getOutput();
		$output->addStyle( AssetsManager::getInstance()->getSassCommonURL('extensions/wikia/CategoryExhibition/css/CategoryExhibition.scss'));
		$output->addStyle( AssetsManager::getInstance()->getSassCommonURL('extensions/wikia/CategoryExhibition/css/CategoryExhibition.IE.scss'), '', 'lte IE 8' );
		$output->addScript( "<script type=\"{$wgJsMimeType}\" src=\"{$wgExtensionsPath}/wikia/CategoryExhibition/js/CategoryExhibition.js\" ></script>\n" );

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
	}
}
