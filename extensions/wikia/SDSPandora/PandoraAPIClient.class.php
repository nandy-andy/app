<?php
/**
 * @author Jacek 'mech' Wozniak
 */

/*
 curl -v -H 'Accept: application/json'
	-X POST
	-d '{
	   "type":"callofduty:Character-v ",
	   "schema:name":"test video",
	   "schema:url":"http://sds.wikia.com/videos/123798"
	 }'
    http://localhost:9292/api/v0.1/structured-data
*/
class PandoraAPIClient extends WikiaObject {

	protected $baseUrl = null;
	protected $apiPath = null;

	public function __construct($baseUrl, $apiPath) {
		parent::__construct();
		$this->baseUrl = $baseUrl;
		$this->apiPath = $apiPath;
	}

	/**
	 * Generate object address
	 * @param $objectShortId - object id that's unique in the collection, for articles this should be articleId
	 * @param null $collection - optional, collection name, defaults to current wiki
	 * @return string sds object address
	 */
	public function getObjectUrl( $objectShortId, $collection = null ) {
		if ( !$collection ) $collection = $this->wg->DBname;
		return $this->baseUrl . $this->apiPath . rawurlencode( strtolower( $collection ) ) . '/' . rawurlencode( strtolower( $objectShortId ) );
	}

	/**
	 * Generate collection address
	 * @param null $collection - collection name, defaults to current wiki
	 * @return string sds collection address
	 */
	public function getCollectionUrl( $collection = null ) {
		if ( !$collection ) $collection = $this->wg->DBname;
		return $this->baseUrl . $this->apiPath . rawurlencode( strtolower( $collection ) );
	}

	/**
	 * Make a call to SDS to fetch an object.
	 * @param $url - sds object address (from object's id or generated with  getObjectUrl method)
	 * @return PandoraResponse
	 */
	public function getObject( $url ) {
		return $this->call( $url );
	}

	/**
	 * Make a call to SDS to fetch an object.
	 * @param $url - sds object address (from object's id or generated with  getObjectUrl method)
	 * @return SDS object JSON representation
	 */
	public function getObjectAsJson( $url ) {
		$response = $this->call( $url );
		if ( !$response->isOK() ) {
			throw new WikiaException('Invalid status ' . $response->getMessage() . ' for url ' . $url);
		}
		return $response->asJson();
	}

	/**
	 * Make a call to SDS to remove an object.
	 * @param $url - sds object address (from object's id or generated with  getObjectUrl method)
	 * @return PandoraResponse
	 */
	public function deleteObject( $url ) {
		return $this->call( $url, true, 'DELETE' );
	}

	/**
	 * Make a call to SDS to modify an object.
	 * @param $url - sds object address (from object's id or generated with  getObjectUrl method)
	 * @return PandoraResponse
	 */
	public function saveObject( $url, $body ) {
		return $this->call( $url, true, 'PUT', $body );
	}

	/**
	 * Make a call to SDS to create a new object.
	 * @param $url - sds collection address generated by calling getCollectionUrl, usually it's current wiki collection
	 * @return PandoraResponse
	 */
	public function createObject( $url, $body ) {
		return $this->call( $url, true, 'POST', $body );
	}

	protected function call( $url, $nocache = true, $method = null, $body = null ) {
		$options = array( 'method' => ( $method ) ? $method : 'GET' );
		//don't use wgHTTPProxy on devboxes, as cross-devbox calls will return 403
		if ( !empty( $this->app->wg->develEnvironment ) ) $options['noProxy'] = true;
		$httpRequest = MwHttpRequest::factory( $url,  $options );
		if ( $body ) $httpRequest->setData( $body );
		if ( $nocache ) {
			$httpRequest->setHeader( 'Cache-Control', 'no-cache' );
			$httpRequest->setHeader( 'If-Modified-Since', 'Sat, 29 Oct 1994 19:43:31 GMT' ); // probably not needed
		}
		$httpRequest->setHeader( 'Accept', 'application/ld+json' );
		$status = $httpRequest->execute();
		$response = $httpRequest->getContent();
		return new PandoraResponse( $status, $response );
	}

}