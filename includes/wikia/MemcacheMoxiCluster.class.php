<?php
/**
 * MemcacheMoxiCluster
 *
 * moxi (http://www.couchbase.com/docs/moxi-manual-1.8/) is a memcache proxy server that maintains a persistent connection
 * to memcached. Here we modify the socket writes to always write to the local unix socket instead of to the memcache
 * server directly.
 *
 * @author Nelson Monterroso <nelson@wikia-inc.com>
 * @see MWMemcached
 */

class MemcacheMoxiCluster extends MemCachedClientforWiki {
	const SOCKET = "unix:///var/run/moxi-server/moxi-server.sock";

	const PROTOCOL = "B"; // B=binary, A=ascii

	function _safe_fwrite( $f, $host, $buf, $len = false ) {
		$buf = self::PROTOCOL . ":{$host} $buf";

		if ( $len !== false ) {
			$len = strlen( $buf );
		}

		return parent::_safe_fwrite( $f, $host, $buf, $len );
	}

	protected function parseHost( $host ) {
		return [ self::SOCKET, -1 ];
	}
}