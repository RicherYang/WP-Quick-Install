WP Quick Install 1.5.3 台灣繁體中文版
================

WP Quick Install 提供了安裝 WordPress 網站最簡單的方式。

它所提供的輕量指令碼能夠自動下載並安裝 WordPress 核心程式、外掛及佈景主題。

僅需下載 ZIP 壓縮檔並將解壓縮所得的資料夾及全部檔案上傳至網站根目錄，並在網址列輸入 siteurl/wp-quick-install/index.php 即可開始安裝。

變更記錄
================

1.5.3
-----------
* 支援內建的 akismet 外掛及本地化語言套件更新至最新版
* 支援匯入 [Theme Unit Test](https://github.com/WPTRT/theme-unit-test) 提供的網站內容範例
* Contributor: [Richer Yang](https://github.com/RicherYang/WP-Quick-Install) 

1.5.2
-----------
* 支援下載與 [網站介面語言] 設定一致的外掛及佈景主題本地化語言套件 (如果提供的話)
* Contributor: [Richer Yang](https://github.com/RicherYang/WP-Quick-Install) 

1.5.1
-----------
* 支援安裝 WordPress.org 佈景主題目錄中的免費佈景主題
* 修正某些 PHP 錯誤
* Contributor: [Richer Yang](https://github.com/RicherYang/WP-Quick-Install)

1.5.0
-----------
* 修正使用 Apache 2.4 時的錯誤
* 修正 PHP 通知問題
* 將資料庫連線方式變更為 WordPress 核心程式使用的方式
* Contributor: [Richer Yang](https://github.com/RicherYang/WP-Quick-Install) 

1.4.2
-----------
* Delete Tweentyfifteen & Tweentysixteen themes

1.4.1
-----------
* Fix quote issue with WordPress.com API Key

1.4
-----------
* Fix database issue since WordPress 4.1
* You can add your WordPress.com API Key

1.3.3
-----------

* Add SSL compatibility
* Remove SSL function (cause trouble with process installation)

1.3.2
-----------

* Add a script header
* Security improvement

1.3.1
-----------

* Fix error for PHP > 5.5: Strict standards: Only variables should be passed by reference in ../wp-quick-install/index.php on line 10

1.3
-----------

* Possiblity to select WordPress language installation
* Permaling management


1.2.8.1
-----------

* You can now declare articles to be generated via data.ini file
* Fix bug on new articles
* You can now select the revision by articles

1.2.8
-----------

* Media management

1.2.7.2
-----------

* Security : Forbiden access to data.ini from the browser

1.2.7.1
-----------

* noindex nofollow tag.

1.2.7
-----------

* Premium extension by adding archives in plugins folder
* You can enable extension after installation
* Auto supression of Hello Dolly extension
* You can add a theme and enable it
* You can delete Twenty Elever and Twenty Ten

1.2.6
-----------

* Fix a JS bug with data.ini

1.2.5
-----------

* You can delete the default content added by WordPress
* You can add new pages with data.ini
* Data.ini update

1.2.4
-----------

* Two new debug options : *Display errors* (WP_DEBUG_DISPLAY) and *Write errors in a log file* (WP_DEBUG_LOG)

1.2.3
-----------

* SEO Fix bug
* Automatic deletion of licence.txt and readme.html

1.2.2
-----------

* Deletion of all exec() fucntions
* Unzip WordPress and plugins with ZipArchive class
* Using scandir() and rename() to move WordPress files

1.2.1
-----------

* Checking chmod on parent folder
* Adding a link to website and admin if success

1.2
-----------

* You can now pre-configure the form with data.ini


1.1
-----------

* Code Optimisation


1.0
-----------

* Initial Commit
