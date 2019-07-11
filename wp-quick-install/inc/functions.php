<?php

if ( ! function_exists( '_' ) ) {
	function _( $str ) {
		echo $str;
	}
}

function sanit( $str ) {
	return addcslashes( str_replace( array( ';', "\n" ), '', $str ), '\\' );
}

function parse_db_host( $host ) {
	$port    = null;
	$socket  = null;
	$is_ipv6 = false;

	// First peel off the socket parameter from the right, if it exists.
	$socket_pos = strpos( $host, ':/' );
	if ( $socket_pos !== false ) {
		$socket = substr( $host, $socket_pos + 1 );
		$host   = substr( $host, 0, $socket_pos );
	}

	// We need to check for an IPv6 address first.
	// An IPv6 address will always contain at least two colons.
	if ( substr_count( $host, ':' ) > 1 ) {
		$pattern = '#^(?:\[)?(?P<host>[0-9a-fA-F:]+)(?:\]:(?P<port>[\d]+))?#';
		$is_ipv6 = true;
	} else {
		// We seem to be dealing with an IPv4 address.
		$pattern = '#^(?P<host>[^:/]*)(?::(?P<port>[\d]+))?#';
	}

	$matches = array();
	$result  = preg_match( $pattern, $host, $matches );

	if ( 1 !== $result ) {
		// Couldn't parse the address, bail.
		return false;
	}

	$host = '';
	foreach ( array( 'host', 'port' ) as $component ) {
		if ( ! empty( $matches[ $component ] ) ) {
			$$component = $matches[ $component ];
		}
	}

	return array( $host, $port, $socket, $is_ipv6 );
}

function download_translation($language, $translation_url, $translation_path) {
	// 擷取本地化資訊，便能取得清單以便下載
	$translations_repo = file_get_contents( $translation_url );

	if ( $translations_repo && $translations = json_decode( $translations_repo ) ) {

		// 取得本地化套件資訊
		$translations_index = array_column($translations->translations, 'language');
		$translations_index = array_search($language, $translations_index);
		if( $translations_index !== false ) {
			$translation = $translations->translations[$translations_index];

			// 確認本地化檔案是否存在
			if ( file_exists( $translation_path ) ) {

				// 取得本地化檔案更新時間
				$filemtime = filemtime( $translation_path );

				// 確認本地化檔案是否需要更新
				if( strtotime( $translation->updated ) > $filemtime ) {
					$download_file = true;
				} else {
					$download_file = false;
				}
			} else {
				$download_file = true;
			}

			if( $download_file ) {
				// 下載最新版本地化檔案
				if ( $download_link = file_get_contents( $translation->package ) ) {
					file_put_contents( $translation_path, $download_link );
				}
			}
		}
	}
}
