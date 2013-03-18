<?php
	// get wiki thumbnail and thumbnail tracking
	$images = $resultSet->getHeader( 'images' );
	$helper = new \WikiaHomePageHelper();

	if ( !empty( $images ) ) {
		foreach($images as $k=>$v) {
			$images[$k] = $helper->getImageUrl($v, 180, 120);

		}
		$imageURL = $images[0];
		$thumbTracking = 'class="wiki-thumb-tracking" data-pos="' . $pos . '" data-event="search_click_wiki-thumb"';
	} else {
		// display placeholder image if no thumbnail
		$imageURL = $wgExtensionsPath . '/wikia/Search/images/wiki_image_placeholder.png';
		$thumbTracking = 'class="wiki-thumb-tracking" data-pos="' . $pos . '" data-event="search_click_wiki-no-thumb"';
	}
?>

<?php if($resultSet->getResultsFound() > 1): ?>
    <li class="result">

		<?php
		$trackingData = 'class="result-link" data-pos="' . $pos . '" data-event="search_click_wiki"';
		?>
	    <a href="<?= $resultSet->getHeader('url'); ?>" title="<?= $resultSet->getHeader('title'); ?> <?= $thumbTracking ?>">
            <img src="<?= $imageURL; ?>" alt="<?= $resultSet->getHeader('title'); ?>" class="wikiPromoteThumbnail" />
		</a>
        <div class=" result-description">

            <h1>
                <a href="<?= $resultSet->getHeader('url'); ?>" <?=$trackingData;?> ><?= $resultSet->getHeader
				('wikititle'); ?></a>
            </h1>

            <p class="hub subtle"><?= strtoupper( $resultSet->getHeader( 'hub' ) ); ?></p>
            <p class="description"><?= $resultSet->getHeader('description'); ?></p>

            <ul class="wiki-statistics subtle">
                <li><?= wfMsg( 'wikiasearch2-pages', $wg->Lang->formatNum($resultSet->getHeader('articles_count')) ); ?></li>
                <li><?= wfMsg( 'wikiasearch2-images', $wg->Lang->formatNum($resultSet->getHeader('images_count')) ); ?></li>
                <li><?= wfMsg( 'wikiasearch2-videos', $wg->Lang->formatNum($resultSet->getHeader('videos_count')) ); ?></li>
            </ul>
        </div>
    </li>
<?php else: ?>
	<?= $app->getView( 'WikiaSearch', 'CrossWiki_exactResult', array(
		'resultSet' => $resultSet,
		'pos' => $pos,
		'query' => $query,
		'rank' =>  $resultSet->getHeader('cityRank'),
		'imageURL' => $imageURL,
		'$humbTracking' => $thumbTracking
		)); ?>
<?php endif; ?>