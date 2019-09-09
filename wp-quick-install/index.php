<?php
/*
Script Name: WP Quick Install
Author: Jonathan Buttigieg
Contributors: Richer Yang
Contributors: Julio Potier
Script URI: http://wp-quick-install.com
Version: 1.5.2
Licence: GPLv3
Last Update: 2019-07-11
*/

@set_time_limit( 0 );

define( 'WP_API_CORE'				, 'https://api.wordpress.org/core/version-check/1.7/?locale=' );
define( 'WPQI_CACHE_PATH'			, 'cache/' );
define( 'WPQI_CACHE_CORE_PATH'		, WPQI_CACHE_PATH . 'core/' );
define( 'WPQI_CACHE_THEMES_PATH'	, WPQI_CACHE_PATH . 'themes/' );
define( 'WPQI_CACHE_PLUGINS_PATH'	, WPQI_CACHE_PATH . 'plugins/' );
define( 'WPQI_CACHE_TRANSLATIONS_PATH'	, WPQI_CACHE_PATH . 'translations/' );

require( 'inc/functions.php');

// 強制在網址尾端加上 index.php
$request_uri = explode( '/' , trim($_SERVER['REQUEST_URI'], '/') );
if ( empty( $_GET ) && end( $request_uri ) == 'wp-quick-install' ) {
	header( 'Location: index.php' );
	die();
}

// 建立快取目錄
if ( ! is_dir( WPQI_CACHE_PATH ) ) {
	mkdir( WPQI_CACHE_PATH );
}
if ( ! is_dir( WPQI_CACHE_CORE_PATH ) ) {
	mkdir( WPQI_CACHE_CORE_PATH );
}
if ( ! is_dir( WPQI_CACHE_THEMES_PATH ) ) {
	mkdir( WPQI_CACHE_THEMES_PATH );
}
if ( ! is_dir( WPQI_CACHE_PLUGINS_PATH ) ) {
	mkdir( WPQI_CACHE_PLUGINS_PATH );
}
if ( ! is_dir( WPQI_CACHE_TRANSLATIONS_PATH ) ) {
	mkdir( WPQI_CACHE_TRANSLATIONS_PATH );
}

// 驗證是否有預設配置檔
$data = array();
if ( file_exists( 'data.ini') ) {
	$data = json_encode( parse_ini_file( 'data.ini') );
}

// 將 ../ 語法加入目錄
$directory = ! empty( $_POST['directory'] ) ? '../' . $_POST['directory'] . '/' : '../';

if ( isset( $_GET['action'] ) ) {

	switch( $_GET['action'] ) {

		case "check_before_upload" :

			$data = array();

			/*--------------------------*/
			/*	驗證是否能順利與用於建置網站的資料庫進行連線，以及 WordPress 核心程式是否已安裝
			/*--------------------------*/

			// 資料庫測試
			try {
				$dbh = mysqli_init();
				if( !$dbh ) {
					throw new Exception('error etablishing connection');
				}
				$host = $_POST['dbhost'];
				$port = null;
				$socket = null;
				$is_ipv6 = false;

				if ( $host_data = parse_db_host( $_POST['dbhost'] ) ) {
					list( $host, $port, $socket, $is_ipv6 ) = $host_data;
				}

				if ( $is_ipv6 && extension_loaded( 'mysqlnd' ) ) {
					$host = "[$host]";
				}

				if( !@mysqli_real_connect($dbh, $host, $_POST['uname'], $_POST['pwd'], null, $port, $socket) ) {
					throw new Exception('error etablishing connection');
				}
				if( !@mysqli_select_db( $dbh, $_POST['dbname'] ) ) {
					throw new Exception('error etablishing connection');
				}
			}
			catch (Exception $e) {
				$data['db'] = "error etablishing connection";
			}

			// WordPress 測試
			if ( file_exists( $directory . 'wp-config.php') ) {
				$data['wp'] = "error directory";
			}

			// 這個程式傳送回應
			echo json_encode( $data );

			break;

		case "download_wp" :

			// 取得 WordPress 網站介面語言資料
			$language = substr( $_POST['language'], 0, 6 );

			// 取得 WordPress 核心程式資料
			$wp = json_decode( file_get_contents( WP_API_CORE . $language ) )->offers[0];

			// 下載 WordPress 核心程式的最新版本安裝套件
			if ( ! file_exists( WPQI_CACHE_CORE_PATH . 'wordpress-' . $wp->version . '-' . $language  . '.zip') ) {
				file_put_contents( WPQI_CACHE_CORE_PATH . 'wordpress-' . $wp->version . '-' . $language  . '.zip', file_get_contents( $wp->download ) );
			}

			// 下載 WordPress 核心程式的最新版本本地化語言套件
			$translation_url = "https://api.wordpress.org/translations/core/1.0/?version=" . $wp->version;
			$translation_path = WPQI_CACHE_TRANSLATIONS_PATH . 'core-wordpress-' . $wp->version . '-' . $language  . '.zip';
			download_translation($language, $translation_url, $translation_path);

			break;

		case "unzip_wp" :

			// 取得 WordPress 網站介面語言資料
			$language = substr( $_POST['language'], 0, 6 );

			// 取得 WordPress 核心程式資料
			$wp = json_decode( file_get_contents( WP_API_CORE . $language ) )->offers[0];

			/*--------------------------*/
			/*	建立含有檔案的網站資料夾以及 WordPress 資料夾
			/*--------------------------*/

			// 如果有需要，變將 WordPress 資料夾儲存至這個程式建立的子資料夾
			if ( ! empty( $directory ) ) {
				// Let's create the folder
				if ( ! is_dir( $directory ) ) {
					mkdir( $directory );
				}

				// 為目錄設定正確存取權限
				chmod( $directory , 0755 );
			}

			$zip = new ZipArchive;

			// 驗證安裝套件是否可用
			if ( $zip->open( WPQI_CACHE_CORE_PATH . 'wordpress-' . $wp->version . '-' . $language  . '.zip') === true ) {

				// 進行解壓縮
				$zip->extractTo( '.');
				$zip->close();

				// 掃描資料夾
				$files = scandir( 'wordpress');

				// 從目前資料夾其其上層資料夾中移除 "." 及 ".."
				$files = array_diff( $files, array( '.', '..') );

				// 移動檔案及資料夾
				foreach ( $files as $file ) {
					rename(  'wordpress/' . $file, $directory . '/' . $file );
				}

				rmdir( 'wordpress'); // 移除 WordPress 資料夾
				unlink( $directory . '/license.txt'); // 移除 licence.txt 檔案
				unlink( $directory . '/readme.html'); // 移除 readme.html 檔案
				unlink( $directory . '/wp-content/plugins/hello.php'); // 移除 Hello Dolly 外掛
				
				// 解壓縮本地化語言套件
				$zip = new ZipArchive;
				if ( $zip->open( WPQI_CACHE_TRANSLATIONS_PATH . 'core-wordpress-' . $wp->version . '-' . $language  . '.zip' ) === true ) {
					$zip->extractTo( $directory . '/wp-content/languages' );
					$zip->close();
				}
			}

			break;

			case "wp_config" :

				/*--------------------------*/
				/*	開始建立 wp-config 檔案
				/*--------------------------*/

				// 將每一行內容擷取為陣列
				$config_file = file( $directory . 'wp-config-sample.php');

				// 管理安全金鑰
				$secret_keys = explode( "\n", file_get_contents( 'https://api.wordpress.org/secret-key/1.1/salt/') );

				foreach ( $secret_keys as $k => $v ) {
					$secret_keys[$k] = substr( $v, 28, 64 );
				}

				// 寫入變更資料
				$key = 0;
				foreach ( $config_file as &$line ) {

					if ( '$table_prefix  =' == substr( $line, 0, 16 ) ) {
						$line = '$table_prefix  = \'' . sanit( $_POST[ 'prefix' ] ) . "';\r\n";
						continue;
					}

					if ( ! preg_match( '/^define\(\'([A-Z_]+)\',([ ]+)/', $line, $match ) ) {
						continue;
					}

					$constant = $match[1];

					switch ( $constant ) {
						case 'WP_DEBUG'	   :

							// 偵錯模式
							if ( isset($_POST['debug']) && (int) $_POST['debug'] == 1 ) {
								$line = "define('WP_DEBUG', 'true');\r\n";

								// 顯示錯誤
								if ( isset($_POST['debug_display']) && (int) $_POST['debug_display'] == 1 ) {
									$line .= "\r\n\n " . "/** Affichage des erreurs à l'écran */" . "\r\n";
									$line .= "define('WP_DEBUG_DISPLAY', 'true');\r\n";
								}

								// 將錯誤訊息寫入記錄檔
								if ( isset($_POST['debug_log']) && (int) $_POST['debug_log'] == 1 ) {
									$line .= "\r\n\n " . "/** Ecriture des erreurs dans un fichier log */" . "\r\n";
									$line .= "define('WP_DEBUG_LOG', 'true');\r\n";
								}
							}

							// 新增額外常數
							if ( ! empty( $_POST['uploads'] ) ) {
								$line .= "\r\n\n " . "/** Dossier de destination des fichiers uploadés */" . "\r\n";
								$line .= "define('UPLOADS', '" . sanit( $_POST['uploads'] ) . "');";
							}

							if ( (int) $_POST['post_revisions'] >= 0 ) {
								$line .= "\r\n\n " . "/** Désactivation des révisions d'articles */" . "\r\n";
								$line .= "define('WP_POST_REVISIONS', " . (int) $_POST['post_revisions'] . ");";
							}

							if ( isset($_POST['disallow_file_edit']) && (int) $_POST['disallow_file_edit'] == 1 ) {
								$line .= "\r\n\n " . "/** Désactivation de l'éditeur de thème et d'extension */" . "\r\n";
								$line .= "define('DISALLOW_FILE_EDIT', true);";
							}

							if ( (int) $_POST['autosave_interval'] >= 60 ) {
								$line .= "\r\n\n " . "/** Intervalle des sauvegardes automatique */" . "\r\n";
								$line .= "define('AUTOSAVE_INTERVAL', " . (int) $_POST['autosave_interval'] . ");";
							}

							if ( ! empty( $_POST['wpcom_api_key'] ) ) {
								$line .= "\r\n\n " . "/** WordPress.com API Key */" . "\r\n";
								$line .= "define('WPCOM_API_KEY', '" . $_POST['wpcom_api_key'] . "');";
							}

							$line .= "\r\n\n " . "/** On augmente la mémoire limite */" . "\r\n";
							$line .= "define('WP_MEMORY_LIMIT', '96M');" . "\r\n";

							break;
						case 'DB_NAME'     :
							$line = "define('DB_NAME', '" . sanit( $_POST[ 'dbname' ] ) . "');\r\n";
							break;
						case 'DB_USER'     :
							$line = "define('DB_USER', '" . sanit( $_POST['uname'] ) . "');\r\n";
							break;
						case 'DB_PASSWORD' :
							$line = "define('DB_PASSWORD', '" . sanit( $_POST['pwd'] ) . "');\r\n";
							break;
						case 'DB_HOST'     :
							$line = "define('DB_HOST', '" . sanit( $_POST['dbhost'] ) . "');\r\n";
							break;
						case 'AUTH_KEY'         :
						case 'SECURE_AUTH_KEY'  :
						case 'LOGGED_IN_KEY'    :
						case 'NONCE_KEY'        :
						case 'AUTH_SALT'        :
						case 'SECURE_AUTH_SALT' :
						case 'LOGGED_IN_SALT'   :
						case 'NONCE_SALT'       :
							$line = "define('" . $constant . "', '" . $secret_keys[$key++] . "');\r\n";
							break;

						case 'WPLANG' :
							$line = "define('WPLANG', '" . sanit( $_POST['language'] ) . "');\r\n";
							break;
					}
				}
				unset( $line );

				$handle = fopen( $directory . 'wp-config.php', 'w');
				foreach ( $config_file as $line ) {
					fwrite( $handle, $line );
				}
				fclose( $handle );

				// 為 wp-config 檔案設定正確存取權限
				chmod( $directory . 'wp-config.php', 0666 );

				break;

			case "install_wp" :

				/*--------------------------*/
				/*	開始寫入 WordPress 資料庫
				/*--------------------------*/

				define( 'WP_INSTALLING', true );

				/** 載入 WordPress Bootstrap */
				require_once( $directory . 'wp-load.php');

				/** 載入 WordPress 管理後台的 Update API */
				require_once( $directory . 'wp-admin/includes/upgrade.php');

				/** 載入 wpdb */
				require_once( $directory . 'wp-includes/wp-db.php');

				// 安裝 WordPress
				wp_install( $_POST[ 'weblog_title' ], $_POST['user_login'], $_POST['admin_email'], (int) $_POST[ 'blog_public' ], '', $_POST['admin_password'] );

				// 更新 [網站網址] 及 [WordPress 網址] 的設定值
				$protocol = ! is_ssl() ? 'http' : 'https';
                $get = basename( dirname( __FILE__ ) ) . '/index.php/wp-admin/install.php?action=install_wp';
                $dir = str_replace( '../', '', $directory );
                $link = $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                $url = str_replace( $get, $dir, $link );
                $url = trim( $url, '/');

				update_option( 'siteurl', $url );
				update_option( 'home', $url );

				/*--------------------------*/
				/*	移除預設內容
				/*--------------------------*/

				if ( $_POST['default_content'] == '1') {
					wp_delete_post( 1, true ); // We remove the article "Hello World"
					wp_delete_post( 2, true ); // We remove the "Exemple page"
				}

				/*--------------------------*/
				/*	更新 [永久連結] 設定
				/*--------------------------*/
				if ( ! empty( $_POST['permalink_structure'] ) ) {
					update_option( 'permalink_structure', $_POST['permalink_structure'] );
				}

				/*--------------------------*/
				/*	更新 [媒體] 設定
				/*--------------------------*/

				if ( ! empty( $_POST['thumbnail_size_w'] ) || !empty($_POST['thumbnail_size_h'] ) ) {
					update_option( 'thumbnail_size_w', (int) $_POST['thumbnail_size_w'] );
					update_option( 'thumbnail_size_h', (int) $_POST['thumbnail_size_h'] );
					update_option( 'thumbnail_crop', (int) $_POST['thumbnail_crop'] );
				}

				if ( ! empty( $_POST['medium_size_w'] ) || !empty( $_POST['medium_size_h'] ) ) {
					update_option( 'medium_size_w', (int) $_POST['medium_size_w'] );
					update_option( 'medium_size_h', (int) $_POST['medium_size_h'] );
				}

				if ( ! empty( $_POST['large_size_w'] ) || !empty( $_POST['large_size_h'] ) ) {
					update_option( 'large_size_w', (int) $_POST['large_size_w'] );
					update_option( 'large_size_h', (int) $_POST['large_size_h'] );
				}

				 update_option( 'uploads_use_yearmonth_folders', (int) $_POST['uploads_use_yearmonth_folders'] );

				/*--------------------------*/
				/*	新增在 data.ini 檔案中找到的頁面
				/*--------------------------*/

				// 檢查 data.ini 是否存在
				if ( file_exists( 'data.ini') ) {

					// 剖析檔案內容及取得陣列
					$file = parse_ini_file( 'data.ini');

					// 驗證是否至少有一個頁面
					if ( isset($file['posts']) && count( $file['posts'] ) >= 1 ) {

						foreach ( $file['posts'] as $post ) {

							// 取得頁面組態內容
							$pre_config_post = explode( "-", $post );
							$post = array();

							foreach ( $pre_config_post as $config_post ) {

								// 擷取頁面標題
								if ( preg_match( '#title::#', $config_post ) == 1 ) {
									$post['title'] = str_replace( 'title::', '', $config_post );
								}

								// 擷取發佈狀態 (已發佈、草稿等...)
								if ( preg_match( '#status::#', $config_post ) == 1 ) {
									$post['status'] = str_replace( 'status::', '', $config_post );
								}

								// 擷取內容類型 (文章、頁面或自訂內容類型...)
								if ( preg_match( '#type::#', $config_post ) == 1 ) {
									$post['type'] = str_replace( 'type::', '', $config_post );
								}

								// 擷取內容
								if ( preg_match( '#content::#', $config_post ) == 1 ) {
									$post['content'] = str_replace( 'content::', '', $config_post );
								}

								// 擷取代稱
								if ( preg_match( '#slug::#', $config_post ) == 1 ) {
									$post['slug'] = str_replace( 'slug::', '', $config_post );
								}

								// 擷取上層項目標題
								if ( preg_match( '#parent::#', $config_post ) == 1 ) {
									$post['parent'] = str_replace( 'parent::', '', $config_post );
								}

							} // foreach 迴圈

							if ( isset( $post['title'] ) && !empty( $post['title'] ) ) {

								$parent = get_page_by_title( trim( $post['parent'] ) );
 								$parent = $parent ? $parent->ID : 0;

								// 開始建立頁面
								$args = array(
									'post_title' 		=> trim( $post['title'] ),
									'post_name'			=> $post['slug'],
									'post_content'		=> trim( $post['content'] ),
									'post_status' 		=> $post['status'],
									'post_type' 		=> $post['type'],
									'post_parent'		=> $parent,
									'post_author'		=> 1,
									'post_date' 		=> date('Y-m-d H:i:s'),
									'post_date_gmt' 	=> gmdate('Y-m-d H:i:s'),
									'comment_status' 	=> 'closed',
									'ping_status'		=> 'closed'
								);
								wp_insert_post( $args );

							}

						}
					}
				}

				break;

			case "install_themes" :

				/** 載入 WordPress Bootstrap */
				require_once( $directory . 'wp-load.php');

				/** 載入 WordPress 管理後台的 Update API */
				require_once( $directory . 'wp-admin/includes/upgrade.php');

				/*--------------------------*/
				/*	安裝佈景主題
				/*--------------------------*/

				$themes_dir = $directory . 'wp-content/themes/';
				$translations_dir = $directory . 'wp-content/languages/themes/';

				$activate_theme = '';

				// 驗證 theme.zip 是否存在
				if ( file_exists( 'theme.zip' ) ) {

					$zip = new ZipArchive;

					// 驗證 theme.zip 檔案是否可用
					if ( $zip->open( 'theme.zip') === true ) {

						// 擷取資料夾名稱
						$stat = $zip->statIndex( 0 );
						$theme_name = str_replace('/', '' , $stat['name']);

						// 將 theme.zip 解壓縮至 themes 資料夾
						$zip->extractTo( $themes_dir );
						$zip->close();

						// 啟用佈景主題
						if ( $_POST['activate_theme'] == 1 ) {
							$activate_theme = $theme_name;
						}
					}
				}

				// 佈景主題安裝來源 
				if ( ! empty( $_POST['themes'] ) ) {

					// 取得 WordPress 網站介面語言資料
					$language = substr( $_POST['language'], 0, 6 );

					$themes = explode( ";", $_POST['themes'] );
					$themes = array_map( 'trim' , $themes );

					foreach ( $themes as $theme ) {

						// 擷取佈景主題 XML 檔案，便能取得清單以便下載
					    $theme_repo = file_get_contents( "https://api.wordpress.org/themes/info/1.1/?action=theme_information&request[slug]=$theme" );

					    if ( $theme_repo && $theme = json_decode( $theme_repo ) ) {

							$theme_path = WPQI_CACHE_THEMES_PATH . $theme->slug . '-' . $theme->version . '.zip';

							if ( ! file_exists( $theme_path ) ) {
								// 下載佈景主題的最新版本安裝套件
								if ( $download_link = file_get_contents( $theme->download_link ) ) {
 									file_put_contents( $theme_path, $download_link );
 								}
							}

					    	// 解壓縮佈景主題安裝套件
					    	$zip = new ZipArchive;
							if ( $zip->open( $theme_path ) === true ) {
								$zip->extractTo( $themes_dir );
								$zip->close();
								
								// 記錄要啟用的佈景主題名稱
								if ( $_POST['activate_theme'] == 1 && $activate_theme == '' ) {
									$activate_theme = $theme->slug;
								}
							}

							// 下載佈景主題的最新版本本地化語言套件
							$translation_url = "https://api.wordpress.org/translations/themes/1.0/?slug=" . $theme->slug . "&version=" . $theme->version;
							$translation_path = WPQI_CACHE_TRANSLATIONS_PATH . 'theme-' . $theme->slug . '-' . $theme->version . '-' . $language  . '.zip';
							download_translation($language, $translation_url, $translation_path);

							if( file_exists( $translation_path ) ) {
								// 解壓縮本地化語言套件
								$zip = new ZipArchive;
								if ( $zip->open( $translation_path ) === true ) {
									$zip->extractTo( $translations_dir );
									$zip->close();
								}
							}
					    }
					}
				}

				// 啟用佈景主題
				if( $activate_theme != '' ) {
					switch_theme( $activate_theme, $activate_theme );
					
					// 移除 Tweenty 系列佈景主題
					if ( $_POST['delete_default_themes'] == 1 ) {
						delete_theme( 'twentynineteen' );
						delete_theme( 'twentyseventeen' );
						delete_theme( 'twentysixteen' );
						delete_theme( 'twentyfifteen' );
						delete_theme( 'twentyfourteen' );
						delete_theme( 'twentythirteen' );
						delete_theme( 'twentytwelve' );
						delete_theme( 'twentyeleven' );
						delete_theme( 'twentyten' );
					}
				}

				// 刪除 _MACOSX 資料夾 (macOS 特有的程式碼錯誤)
				delete_theme( '__MACOSX' );

			break;

			case "install_plugins" :

				/*--------------------------*/
				/*	擷取外掛資料夾
				/*--------------------------*/

				if ( ! empty( $_POST['plugins'] ) ) {

					// 取得 WordPress 網站介面語言資料
					$language = substr( $_POST['language'], 0, 6 );

					$plugins     = explode( ";", $_POST['plugins'] );
					$plugins     = array_map( 'trim' , $plugins );
					$plugins_dir = $directory . 'wp-content/plugins/';
					$translations_dir = $directory . 'wp-content/languages/plugins/';

					foreach ( $plugins as $plugin ) {

						// 擷取外掛 XML 檔案，便能取得清單以便下載
					    $plugin_repo = file_get_contents( "https://api.wordpress.org/plugins/info/1.0/$plugin.json" );

					    if ( $plugin_repo && $plugin = json_decode( $plugin_repo ) ) {

							$plugin_path = WPQI_CACHE_PLUGINS_PATH . $plugin->slug . '-' . $plugin->version . '.zip';

							if ( ! file_exists( $plugin_path ) ) {
								// 下載外掛的最新版本安裝套件
								if ( $download_link = file_get_contents( $plugin->download_link ) ) {
 									file_put_contents( $plugin_path, $download_link );
 								}
							}

					    	// 解壓縮外掛安裝套件
					    	$zip = new ZipArchive;
							if ( $zip->open( $plugin_path ) === true ) {
								$zip->extractTo( $plugins_dir );
								$zip->close();
							}

							// 下載外掛的最新版本本地化語言套件
							$translation_url = "https://api.wordpress.org/translations/plugins/1.0/?slug=" . $plugin->slug . "&version=" . $plugin->version;
							$translation_path = WPQI_CACHE_TRANSLATIONS_PATH . 'plugin-' . $plugin->slug . '-' . $plugin->version . '-' . $language  . '.zip';
							download_translation($language, $translation_url, $translation_path);

							if( file_exists( $translation_path ) ) {
								// 解壓縮本地化語言套件
								$zip = new ZipArchive;
								if ( $zip->open( $translation_path ) === true ) {
									$zip->extractTo( $translations_dir );
									$zip->close();
								}
							}
					    }
					}
				}

				if ( isset($_POST['plugins_premium']) && (int) $_POST['plugins_premium'] == 1 ) {

					// 掃描資料夾
					$plugins = scandir( 'plugins');

					// 移除目前資料夾及其上層資料夾對應的 "." 及 ".."
					$plugins = array_diff( $plugins, array( '.', '..') );

					// 移動壓縮檔並解壓縮
					foreach ( $plugins as $plugin ) {

						// 驗證是否擷取到 WP Quick Install 中的 plugins 資料夾有儲存外掛安裝套件
						if ( preg_match( '#(.*).zip$#', $plugin ) == 1 ) {

							$zip = new ZipArchive;

							// 驗證安裝套件是否可用
							if ( $zip->open( 'plugins/' . $plugin ) === true ) {

								// 將 plugins 資料夾中的壓縮檔解壓縮
								$zip->extractTo( $plugins_dir );
								$zip->close();

							}
						}
					}
				}

				/*--------------------------*/
				/*	啟用外掛
				/*--------------------------*/

				if ( isset($_POST['activate_plugins']) && (int) $_POST['activate_plugins'] == 1 ) {

					/** 載入 WordPress Bootstrap */
					require_once( $directory . 'wp-load.php');

					/** 載入 WordPress 的 Plugin API */
					require_once( $directory . 'wp-admin/includes/plugin.php');

					// 啟用外掛
					activate_plugins( array_keys( get_plugins() ) );
				}

			break;

			case "success" :

				/*--------------------------*/
				/*	如果成功完成，便將連結新增至管理後台及網站
				/*--------------------------*/

				/** 載入 WordPress Bootstrap */
				require_once( $directory . 'wp-load.php');

				/** 載入 WordPress 管理後台的 Update API */
				require_once( $directory . 'wp-admin/includes/upgrade.php');

				/*--------------------------*/
				/*	更新永久連結
				/*--------------------------*/
				if ( ! empty( $_POST['permalink_structure'] ) ) {
					file_put_contents( $directory . '.htaccess' , null );
					flush_rewrite_rules();
				}

				echo '<div id="errors" class="alert alert-danger"><p style="margin:0;"><strong>' . _('警告') . '</strong>: 請務必記得刪除 wp-quick-install 資料夾。</p></div>';

				// Link to the admin
				echo '<a href="' . admin_url() . '" class="button" style="margin-right:5px;" target="_blank">'. _('登入') . '</a>';
				echo '<a href="' . home_url() . '" class="button" target="_blank">' . _('前往網站') . '</a>';

				break;
	}
}
else { ?>
<!DOCTYPE html>
<html xmlns="https://www.w3.org/1999/xhtml" lang="en">
	<head>
		<meta charset="utf-8" />
		<title>WP Quick Install</title>
		<!-- 要求 Google 不要檢索及索引這個頁面 -->
		<meta name="robots" content="noindex, nofollow">
		<!-- CSS 檔案 -->
		<link rel="stylesheet" href="//fonts.googleapis.com/css?family=Open+Sans%3A300italic%2C400italic%2C600italic%2C300%2C400%2C600&#038;subset=latin%2Clatin-ext&#038;ver=3.9.1" />
		<link rel="stylesheet" href="assets/css/style.min.css" />
		<link rel="stylesheet" href="assets/css/buttons.min.css" />
		<link rel="stylesheet" href="assets/css/bootstrap.min.css" />
	</head>
	<body class="wp-core-ui">
	<h1 id="logo"><a href="http://wp-quick-install.com">WordPress</a></h1>
		<?php
		$parent_dir = realpath( dirname ( dirname( __FILE__ ) ) );
		if ( is_writable( $parent_dir ) ) { ?>

			<div id="response"></div>
			<div class="progress" style="display:none;">
				<div class="progress-bar progress-bar-striped active" style="width: 0%;"></div>
			</div>
			<div id="success" style="display:none; margin: 10px 0;">
				<h1 style="margin: 0"><?php echo _('大功告成') ;?></h1>
				<p><?php echo _('WordPress 已完成安裝。') ;?></p>
			</div>
			<form method="post" action="">

				<div id="errors" class="alert alert-danger" style="display:none;">
					<strong><?php echo _('警告');?></strong>
				</div>

				<h1><?php echo _('警告');?></h1>
				<p><?php echo _('這個檔案必須位於 <code>wp-quick-install</code> 資料夾中，而不能儲存於網站根目錄。');?></p>

				<h1><?php echo _('資料庫資訊');?></h1>
				<p><?php echo _('安裝人員應於下方輸入資料庫連線詳細資料。如果不清楚以下欄位代表的意義，請洽詢網站主機服務商。'); ?></p>

				<table class="form-table">
					<tr>
						<th scope="row"><label for="dbname"><?php echo _('資料庫名稱');?></label></th>
						<td><input name="dbname" id="dbname" type="text" size="18" value="wordpress" class="required" /></td>
						<td><?php echo _('用於建置網站的資料庫名稱。'); ?></td>
					</tr>
					<tr>
						<th scope="row"><label for="uname"><?php echo _('資料庫使用者名稱');?></label></th>
						<td><input name="uname" id="uname" type="text" size="18" value="username" class="required" /></td>
						<td><?php echo _('用於建置網站的 MySQL 資料庫使用者名稱'); ?></td>
					</tr>
					<tr>
						<th scope="row"><label for="pwd"><?php echo _('資料庫密碼');?></label></th>
						<td><input name="pwd" id="pwd" type="text" size="18" value="password" /></td>
						<td><?php echo _('...以及 MySQL 資料庫密碼。');?></td>
					</tr>
					<tr>
						<th scope="row"><label for="dbhost"><?php echo _('資料庫主機名稱'); ?></label></th>
						<td><input name="dbhost" id="dbhost" type="text" size="18" value="localhost" class="required" /></td>
						<td><?php echo _('如果因故無法使用 <code>localhost</code> 進行連線，請要求網站主機服務商提供正確對應資訊。'); ?></td>
					</tr>
					<tr>
						<th scope="row"><label for="prefix"><?php echo _('資料表前置詞'); ?></label></th>
						<td><input name="prefix" id="prefix" type="text" value="wp_" size="18" class="required" /></td>
						<td><?php echo _('如需在同一個資料庫中安裝多個 WordPress，請修改這個欄位中的預設設定。'); ?></td>
					</tr>
					<tr>
						<th scope="row"><label for="default_content"><?php echo _('網站預設內容');?></label></th>
						<td><label><input type="checkbox" name="default_content" id="default_content" value="1" checked="checked" /> <?php echo _('刪除網站預設內容')?></label></td>
						<td><?php echo _('啟用這項設定後，便會在 WordPress 網站建置完成後刪除如文章、頁面、留言及連結等預設內容。');?></td>
					</tr>
				</table>

				<h1><?php echo _('安裝必要資訊');?></h1>
				<p><?php echo _('感謝提供以下安裝必要資訊，這些設定在安裝完畢後可以進入管理後台變更。');?></p>

				<table class="form-table">
					<tr>
						<th scope="row"><label for="language"><?php echo _('網站介面語言');?></label></th>
						<td>
							<select id="language" name="language">
								<option value="en_US">英文 (美國)</option>
								<?php

								// 取得網站介面可用語言完整清單
								$languages = json_decode( file_get_contents( 'https://api.wordpress.org/translations/core/1.0/?version=5.0' ) )->translations;

								foreach ( $languages as $language ) {
									echo '<option value="' . $language->language . '">' . $language->native_name . '</option>';
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="directory"><?php echo _('安裝資料夾');?></label>
							<p><?php echo _('欄位留空便會安裝於網站根目錄');?></p>
						</th>
						<td><input name="directory" type="text" id="directory" size="25" value="" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="weblog_title"><?php echo _('網站標題');?></label></th>
						<td><input name="weblog_title" type="text" id="weblog_title" size="25" value="" class="required" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="user_login"><?php echo _('使用者名稱');?></label></th>
						<td>
							<input name="user_login" type="text" id="user_login" size="25" value="" class="required" />
							<p><?php echo _('使用者名稱只能使用數字、英文字母、空白、底線、連字號、句號及 @ 符號。');?></p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="admin_password"><?php echo _('密碼');?></label>
							<p><?php echo _('欄位留空則會自動產生密碼');?></p>
						</th>
						<td>
							<input name="admin_password" type="password" id="admin_password" size="25" value="" />
							<p><?php echo _('提示: 建議密碼應該至少要有 12 個字元，並在密碼中同時使用大小寫字母、數字及 ! \" ? $ % ^ &amp; ) 等特殊符號，便能讓密碼更安全。');?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="admin_email"><?php echo _('電子郵件地址');?></label></th>
						<td>
							<input name="admin_email" type="text" id="admin_email" size="25" value="" class="required" />
							<p><?php echo _('繼續操作前，請再次確認填寫的電子郵件地址。');?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="blog_public"><?php echo _('搜尋引擎可見度');?></label></th>
						<td><label><input type="checkbox" id="blog_public" name="blog_public" value="1" checked="checked" /> <?php echo _('開放搜尋引擎索引這個網站');?></label></td>
					</tr>
				</table>

				<h1><?php echo _('佈景主題設定');?></h1>
				<p><?php echo _('請為網站專屬佈景主題進行預先設定。');?></p>
				<div class="alert alert-info">
					<p style="margin:0px; padding:0px;"><?php echo _('WP Quick Install 會自動安裝儲存於 wp-quick-install 資料夾中命名為 theme.zip 的佈景主題檔案。');?></p>
				</div>
				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="themes"><?php echo _('免費佈景主題');?></label>
							<p><?php echo _('請輸入 WordPress.org 佈景主題目錄中正確的佈景主題代稱，例如<br>https://tw.wordpress.org/themes/<strong>twentynineteen</strong>');?></p>
						</th>
						<td>
							<input name="themes" type="text" id="themes" size="50" value="" />
							<p><?php echo _('如需安裝多個免費外掛，請使用分號 <strong>;</strong> 分隔多個佈景主題代稱。');?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="activate_theme"><?php echo _('自動啟用');?></label></th>
						<td>
							<label><input type="checkbox" id="activate_theme" name="activate_theme" value="1" /> <?php echo _('WordPress 安裝完畢後，啟用預先安裝的佈景主題');?></label>
							<p><?php echo _('如果 theme.zip 存在，便會啟用這個檔案代表的佈景主題，不然便會啟用從佈景主題目錄第一個下載安裝的免費佈景主題。');?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="delete_default_themes"><?php echo _('預設佈景主題');?></label></th>
						<td><label><input type="checkbox" id="delete_default_themes" name="delete_default_themes" value="1" /> <?php echo _('刪除預設 Twenty 系列佈景主題');?></label></td>
					</tr>
				</table>

				<h1><?php echo _('外掛設定');?></h1>
				<p><?php echo _('請在下方設定要在安裝 WordPress 過程中額外安裝的外掛。');?></p>
				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="plugins"><?php echo _('免費外掛');?></label>
							<p><?php echo _('請輸入 WordPress.org 外掛目錄中正確的外掛代稱，例如<br>https://tw.wordpress.org/plugins/<strong>health-check</strong>');?></p>
						</th>
						<td>
							<input name="plugins" type="text" id="plugins" size="50" value="health-check;" />
							<p><?php echo _('如需安裝多個免費外掛，請使用分號 <strong>;</strong> 分隔多個外掛代稱。');?></p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="plugins"><?php echo _('付費外掛');?></label>
							<p><?php echo _('請將要安裝的付費外掛 ZIP 壓縮檔儲存於 <strong>wp-quick-install</strong> 資料夾中的 <strong>plugins</strong> 子資料夾');?></p>
						</th>
						<td><label><input type="checkbox" id="plugins_premium" name="plugins_premium" value="1" /> <?php echo _('WordPress 安裝完畢後自動安裝付費外掛');?></label></td>
					</tr>
					<tr>
						<th scope="row"><label for="plugins"><?php echo _('自動啟用');?></label></th>
						<td><label><input type="checkbox" name="activate_plugins" id="activate_plugins" value="1" /> <?php echo _('WordPress 安裝完畢後自動啟用外掛');?></label></td>
					</tr>
				</table>

				<h1><?php echo _('永久連結設定');?></h1>
				<p><?php echo sprintf( _('WordPress 預設使用問號連接著一串數字的網址 (例如 ?p=123)，但是 WordPress 提供網站管理員為永久連結及彙整建立自訂網址結構的設定。自訂網址結構能為網站連結增進可讀性、可用性及向前相容性 (與更新版本具備相容性)。〈<a href="%s" target=_blank>使用永久連結</a>〉線上說明中提供了可用於永久連結結構的標籤說明。'), 'https://wordpress.org/support/article/using-permalinks/'); ?></p>

				<table class="form-table">
					<tr>
						<th scope="row"><label for="permalink_structure"><?php echo _('自訂結構');?></label></th>
						<td><code>https://<?php echo $_SERVER['SERVER_NAME']; ?></code><input name="permalink_structure" type="text" id="permalink_structure" size="25" value="/%postname%/" /></td>
					</tr>
				</table>

				<h1><?php echo _('媒體設定');?></h1>
				<p><?php echo _('下方所列出的尺寸，決定了將圖片新增至 [媒體庫] 後會產生的各式圖片最大尺寸 (單位為像素)。');?></p>

				<table class="form-table">
					<tr>
						<th scope="row"><?php echo _('縮圖尺寸');?></th>
						<td>
							<label for="thumbnail_size_w"><?php echo _('寬度: ');?></label>
							<input name="thumbnail_size_w" type="number" id="thumbnail_size_w" min="0" step="10" value="0" size="6" />
							<label for="thumbnail_size_h"><?php echo _('高度: ');?></label>
							<input name="thumbnail_size_h" type="number" id="thumbnail_size_h" min="0" step="10" value="0" size="5" /><br>
							<label for="thumbnail_crop" class="small-text"><input name="thumbnail_crop" type="checkbox" id="thumbnail_crop" value="1" checked="checked" /><?php echo _('將縮圖裁剪至與上方設定完全相符的尺寸');?></label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo _('中型尺寸');?></th>
						<td>
							<label for="medium_size_w"><?php echo _('寬度:');?></label>
							<input name="medium_size_w" type="number" id="medium_size_w" min="0" step="10" value="0" size="6" />
							<label for="medium_size_h"><?php echo _('高度: ');?></label>
							<input name="medium_size_h" type="number" id="medium_size_h" min="0" step="10" value="0" size="6" />
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo _('大型尺寸');?></th>
						<td>
							<label for="large_size_w"><?php echo _('寬度: ');?></label>
							<input name="large_size_w" type="number" id="large_size_w" min="0" step="10" value="0" size="6" />
							<label for="large_size_h"><?php echo _('高度: ');?></label>
							<input name="large_size_h" type="number" id="large_size_h" min="0" step="10" value="0" size="6" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="upload_dir"><?php echo _('儲存上傳檔案的資料夾');?></label>
							<p><?php echo _('媒體檔案預設會儲存於 <strong>wp-content/uploads</strong> 資料夾');?></p>
						</th>
						<td>
							<input type="text" id="upload_dir" name="upload_dir" size="25" value="" /><br>
							<label for="uploads_use_yearmonth_folders" class="small-text"><input name="uploads_use_yearmonth_folders" type="checkbox" id="uploads_use_yearmonth_folders" value="1" checked="checked" /><?php echo _('為上傳的檔案建立以<strong>年份</strong>及<strong>月份</strong>命名的資料夾')?></label>
						</td>
					</tr>
				</table>

				<h1><?php echo _('wp-config.php 設定');?></h1>
				<p><?php echo _('請設定要加入 <strong>wp-config.php</strong> 的額外常數。');?></p>

				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="post_revisions"><?php echo _('內容修訂版本數量');?></label>
							<p><?php echo _('依照預設，內容修訂版本數量沒有上限');?></p>
						</th>
						<td><input name="post_revisions" id="post_revisions" type="number" min="0" value="0" size="6" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="plugins"><?php echo _('編輯器');?></label></th>
						<td><label><input type="checkbox" id="disallow_file_edit" name="disallow_file_edit" value="1" checked='checked' /><?php echo _('停用佈景主題及外掛編輯器');?></label></td>
					</tr>
					<tr>
						<th scope="row">
							<label for="autosave_interval"><?php echo _('自動儲存間隔時間');?></label>
							<p><?php echo _('依照預設，自動儲存的間隔時間為 60 秒');?></p>
						</th>
						<td><input name="autosave_interval" id="autosave_interval" type="number" min="60" step="60" size="6" value="7200" /> <?php echo _('秒');?></td>
					</tr>
					<tr>
						<th scope="row"><label for="debug"><?php echo _('偵錯模式');?></label></th>
						<td>
							<label><input type="checkbox" name="debug" id="debug" value="1" /> <?php echo _('啟用 WordPress 偵錯模式'); ?></label>
							<p><?php echo _('啟用這項設定後，WordPress 便會顯示執行錯誤訊息。'); ?></p>
							<div id="debug_options" style="display:none;">
								<label><input type="checkbox" name="debug_display" id="debug_display" value="1" /> <?php echo _('啟用 WP Debug');?></label><br>
								<label><input type="checkbox" name="debug_log" id="debug_log" value="1" /> <?php echo _('將錯誤訊息寫入記錄檔案 <strong>(wp-content/debug.log)</strong> 中');?></label>
							</div>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="wpcom_api_key"><?php echo _('WordPress.com API 金鑰');?></label></th>
						<td><input name="wpcom_api_key" id="wpcom_api_key" type="text" size="25" value="" /></td>
					</tr>
				</table>
				<p class="step"><span id="submit" class="button button-large"><?php echo _('安裝 WordPress');?></span></p>

			</form>

			<script src="assets/js/jquery-1.8.3.min.js"></script>
			<script>var data = <?php echo $data; ?>;</script>
			<script src="assets/js/script.js"></script>
		<?php
		} else { ?>

			<div class="alert alert-error" style="margin-bottom: 0px;">
				<strong><?php echo _('警告！');?></strong>
				<p style="margin-bottom:0px;"><?php echo _('指定位置的檔案權限並未正確設定，該位置為 ') . basename( $parent_dir ) . _('。感謝設定正確的檔案權限。') ;?></p>
			</div>

		<?php
		}
		?>
	</body>
</html>
<?php
}
