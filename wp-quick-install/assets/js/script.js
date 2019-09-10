$(document).ready(function() {

	// 偵錯模式
	var $debug		   = $('#debug'),
		$debug_options = $('#debug_options'),
		$debug_display = $debug_options.find('#debug_display');
		$debug_log 	   = $debug_options.find('#debug_log');

	$debug.change(function() {
		if ( $debug.is(':checked') ) {
			$debug.parent().hide().siblings('p').hide();
			$debug_options.slideDown();
			$debug_display.attr('checked', true);
			$debug_log.attr('checked', true);
		}
	});

	$('#debug_display, #debug_log').change(function(){
		if ( ! $debug_display.is(':checked') && ! $debug_log.is(':checked') ) {
			$debug_options.slideUp().siblings().slideDown();
			$debug.removeAttr('checked');
		}
	});

	/*--------------------------*/
	/*	安裝資料夾
	/*--------------------------*/

	if ( typeof data.directory !='undefined' ) {
		$('#directory').val(data.directory);
	}

	/*--------------------------*/
	/*	網站標題
	/*--------------------------*/

	if ( typeof data.title !='undefined' ) {
		$('#weblog_title').val(data.title);
	}

	/*--------------------------*/
	/*	網站介面語言
	/*--------------------------*/

	if ( typeof data.language !='undefined' ) {
		$('#language').val(data.language);
	}

	/*--------------------------*/
	/*	資料庫
	/*--------------------------*/

	if ( typeof data.db !='undefined' ) {

		if ( typeof data.db.dbname !='undefined' ) {
		$('#dbname').val(data.db.dbname);
		}

		if ( typeof data.db.dbhost !='undefined' ) {
			$('#dbhost').val(data.db.dbhost);
		}

		if ( typeof data.db.prefix !='undefined' ) {
			$('#prefix').val(data.db.prefix);
		}

		if ( typeof data.db.uname !='undefined' ) {
			$('#uname').val(data.db.uname);
		}

		if ( typeof data.db.pwd !='undefined' ) {
			$('#pwd').val(data.db.pwd);
		}
	}

	/*--------------------------*/
	/*	網站管理員使用者
	/*--------------------------*/

	if ( typeof data.admin !='undefined' ) {

		if ( typeof data.admin.user_login !='undefined' ) {
			$('#user_login').val(data.admin.user_login);
		}

		if ( typeof data.admin.password !='undefined' ) {
			$('#admin_password').val(data.admin.password);
		}

		if ( typeof data.admin.email !='undefined' ) {
			$('#admin_email').val(data.admin.email);
		}

	}

	/*--------------------------*/
	/*	網站內容
	/*--------------------------*/
	if ( typeof data.content.delete_default !='undefined' ) {
		( parseInt(data.content.delete_default) == 1 ) ? $('#delete_default_content').attr('checked', 'checked') : $('#delete_default_content').removeAttr('checked');
	}

	if ( typeof data.content.theme_preview !='undefined' ) {
		( parseInt(data.content.theme_preview) == 1 ) ? $('#theme_preview_content').attr('checked', 'checked') : $('#theme_preview_content').removeAttr('checked');
	}

	if ( typeof data.content.theme_test !='undefined' ) {
		( parseInt(data.content.theme_test) == 1 ) ? $('#theme_test_content').attr('checked', 'checked') : $('#theme_test_content').removeAttr('checked');
	}

	/*--------------------------*/
	/*	開放搜尋引擎檢索及索引網站
	/*--------------------------*/

	if ( typeof data.seo !='undefined' ) {
		( parseInt(data.seo) == 1 ) ? $('#blog_public').attr('checked', 'checked') : $('#blog_public').removeAttr('checked');
	}

	/*--------------------------*/
	/*	佈景主題
	/*--------------------------*/

	if ( typeof data.themes !='undefined' ) {
		$('#themes').val( data.themes.join(';') );
	}

	if ( typeof data.activate_theme !='undefined' ) {
		( parseInt(data.activate_theme) == 1 ) ? $('#activate_theme').attr('checked', 'checked') : $('#activate_theme').removeAttr('checked');
	}

	if ( typeof data.delete_default_themes !='undefined' ) {
		( parseInt(data.delete_default_themes) == 1 ) ? $('#delete_default_themes').attr('checked', 'checked') : $('#delete_default_themes').removeAttr('checked');
	}

	/*--------------------------*/
	/*	外掛
	/*--------------------------*/

	if ( typeof data.plugins !='undefined' ) {
		$('#plugins').val( data.plugins.join(';') );
	}

	if ( typeof data.plugins_premium !='undefined' ) {
		( parseInt(data.plugins_premium) == 1 ) ? $('#plugins_premium').attr('checked', 'checked') : $('#plugins_premium').removeAttr('checked');
	}

	if ( typeof data.activate_plugins !='undefined' ) {
		( parseInt(data.activate_plugins) == 1 ) ? $('#activate_plugins').attr('checked', 'checked') : $('#activate_plugins').removeAttr('checked');
	}

	/*--------------------------*/
	/*	永久連結
	/*--------------------------*/

	if ( typeof data.permalink_structure !='undefined' ) {
		$('#permalink_structure').val(data.permalink_structure);
	}

	/*--------------------------*/
	/*	媒體
	/*--------------------------*/

	if ( typeof data.uploads !='undefined' ) {

		if ( typeof data.uploads.thumbnail_size_w !='undefined' ) {
			$('#thumbnail_size_w').val(parseInt(data.uploads.thumbnail_size_w));
		}

		if ( typeof data.uploads.thumbnail_size_h !='undefined' ) {
			$('#thumbnail_size_h').val(parseInt(data.uploads.thumbnail_size_h));
		}

		if ( typeof data.uploads.thumbnail_crop !='undefined' ) {
			( parseInt(data.uploads.thumbnail_crop) == 1 ) ? $('#thumbnail_crop').attr('checked', 'checked') : $('#thumbnail_crop').removeAttr('checked');
		}

		if ( typeof data.uploads.medium_size_w !='undefined' ) {
			$('#medium_size_w').val(parseInt(data.uploads.medium_size_w));
		}

		if ( typeof data.uploads.medium_size_h !='undefined' ) {
			$('#medium_size_h').val(parseInt(data.uploads.medium_size_h));
		}

		if ( typeof data.uploads.large_size_w !='undefined' ) {
			$('#large_size_w').val(parseInt(data.uploads.large_size_w));
		}

		if ( typeof data.uploads.large_size_h !='undefined' ) {
			$('#large_size_h').val(parseInt(data.uploads.large_size_h));
		}

		if ( typeof data.uploads.upload_dir !='undefined' ) {
			$('#upload_dir').val(data.uploads.upload_dir);
		}

		if ( typeof data.uploads.uploads_use_yearmonth_folders !='undefined' ) {
			( parseInt(data.uploads.uploads_use_yearmonth_folders) == 1 ) ? $('#uploads_use_yearmonth_folders').attr('checked', 'checked') : $('#uploads_use_yearmonth_folders').removeAttr('checked');
		}

	}

	/*--------------------------*/
	/*	wp-config.php 常數
	/*--------------------------*/

	if ( typeof data.wp_config !='undefined' ) {

		if ( typeof data.wp_config.autosave_interval !='undefined' ) {
			$('#autosave_interval').val(data.wp_config.autosave_interval);
		}

		if ( typeof data.wp_config.post_revisions !='undefined' ) {
			$('#post_revisions').val(data.wp_config.post_revisions);
		}

		if ( typeof data.wp_config.disallow_file_edit !='undefined' ) {
			( parseInt(data.wp_config.disallow_file_edit) == 1 ) ? $('#disallow_file_edit').attr('checked', 'checked') : $('#disallow_file_edit').removeAttr('checked');
		}

		if ( typeof data.wp_config.debug !='undefined' ) {
			if ( parseInt(data.wp_config.debug) == 1 ) {
				$debug.attr('checked', 'checked');
				$debug.parent().hide().siblings('p').hide();
				$debug_options.slideDown();
				$debug_display.attr('checked', true);
				$debug_log.attr('checked', true);
			} else {
				$('#debug').removeAttr('checked');
			}
		}
		
		if ( typeof data.wp_config.wpcom_api_key !='undefined' ) {
			$('#wpcom_api_key').val(data.wp_config.wpcom_api_key);
		}

	}

	var $response  = $('#response');

	$('#submit').click( function() {

		errors = false;

		// We hide errors div
		$('#errors').hide().html('<strong>警告！</strong>');

		$('input.required').each(function(){
			if ( $.trim($(this).val()) == '' ) {
				errors = true;
				$(this).addClass('error');
				$(this).css("border", "1px solid #FF0000");
			} else {
				$(this).removeClass('error');
				$(this).css("border", "1px solid #DFDFDF");
			}
		});

		if ( ! errors ) {

			/*--------------------------*/
			/*  檢查資料庫連線及 WordPress 是否已安裝
			/*  如果沒有錯誤，便會開始安裝
			/*--------------------------*/

			$.ajaxSetup({
				timeout: 0
			});

			$.post(window.location.href + '?action=check_before_upload', $('form').serialize(), function(data) {

				errors = false;
				data = $.parseJSON(data);

				if ( data.db == "error etablishing connection" ) {
					errors = true;
					$('#errors').show().append('<p style="margin-bottom:0px;">&bull; 建立資料庫連線時發生錯誤</p>');
				}

				if ( data.wp == "error directory" ) {
					errors = true;
					$('#errors').show().append('<p style="margin-bottom:0px;">&bull; WordPress 核心程式似乎已完成安裝</p>');
				}

				if ( ! errors ) {
					$('form').fadeOut( 'fast', function() {

						$('.progress').show();

						// 重要步驟
						// 下載 WordPress 核心程式安裝套件
						$response.html("<p>正在下載 WordPress 核心程式安裝套件...</p>");

						$.post(window.location.href + '?action=download_wp', $('form').serialize(), function() {
							unzip_wp();
						});
					});
				} else {
					// 如果發生錯誤
					$('html,body').animate( { scrollTop: $( 'html,body' ).offset().top } , 'slow' );
				}
			});

		} else {
			// 如果發生錯誤
			$('html,body').animate( { scrollTop: $( 'input.error:first' ).offset().top-20 } , 'slow' );
		}
		return false;
	});

	// 解壓縮 WordPress 核心程式安裝套件
	function unzip_wp() {
		$response.html("<p>正在解壓縮檔案...</p>" );
		$('.progress-bar').animate({width: "14.3%"});
		$.post(window.location.href + '?action=unzip_wp', $('form').serialize(), function(data) {
			wp_config();
		});
	}

	// 開始建立 wp-config 檔案
	function wp_config() {
		$response.html("<p>建立 wp-config 檔案...</p>");
		$('.progress-bar').animate({width: "28.6%"});
		$.post(window.location.href + '?action=wp_config', $('form').serialize(), function(data) {
			install_wp();
		});
	}

	// 安裝資料庫
	function install_wp() {
		$response.html("<p>正在安裝資料庫...</p>");
		$('.progress-bar').animate({width: "42.9%"});
		$.post(window.location.href + '/wp-admin/install.php?action=install_wp', $('form').serialize(), function(data) {
			load_content();
		});
	}

	// 設定網站內容
	function load_content() {
		$response.html("<p>正在設定網站內容...</p>");
		$('.progress-bar').animate({width: "57.1%"});
		$.post(window.location.href + '/wp-admin/install.php?action=load_content', $('form').serialize(), function(data) {
			install_themes();
		});
	}

	// 安裝佈景主題
	function install_themes() {
		$response.html("<p>正在安裝佈景主題...</p>");
		$('.progress-bar').animate({width: "71.4%"});
		$.post(window.location.href + '/wp-admin/install.php?action=install_themes', $('form').serialize(), function(data) {
			install_plugins();
		});
	}

	// 安裝外掛
	function install_plugins() {
		$response.html("<p>正在安裝外掛...</p>");
		$('.progress-bar').animate({width: "85.7%"});
		$.post(window.location.href + '?action=install_plugins', $('form').serialize(), function(data) {
			$response.html(data);
			success();
		});
	}

	// 移除壓縮檔
	function success() {
		$response.html("<p>WordPress 網站已成功安裝</p>");
		$('.progress-bar').animate({width: "100%"});
		$response.hide();
		$('.progress').delay(500).hide();
		$.post(window.location.href + '?action=success',$('form').serialize(), function(data) {
			$('#success').show().append(data);
		});
		$.get( 'http://wp-quick-install.com/inc/incr-counter.php' );
	}

});
