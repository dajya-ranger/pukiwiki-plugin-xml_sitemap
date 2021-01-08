<?php

/**
 * sitemap.inc.php
 *
 * PukiWiki用URL短縮ライブラリ対応
 * XMLサイトマップ出力プラグイン
 *
 * @author		オヤジ戦隊ダジャレンジャー <red@dajya-ranger.com>
 * @copyright	Copyright © 2021, dajya-ranger.com
 * @link		https://dajya-ranger.com/pukiwiki/xml-sitemap-plugin/
 * @example		https://(PukiWiki URL)/?cmd=sitemap[&page=[TRUE|true]]
 * @license		Apache License 2.0
 * @version		0.1.0
 * @since 		0.1.0 2021/01/08 暫定公開
 *
 */

// SEO対応プラグインファイル読み込み
include_once(PLUGIN_DIR . 'seo.inc.php');

// サイトマップ出力ファイル名
define('PLUGIN_SITEMAP_FILE', 'sitemap.xml');
// サイトマップ対象ページ名（正規表現）指定
// ※対象ページ名を指定する場合は指定ページ名のみが出力対象になるので注意
define('PLUGIN_SITEMAP_PAGE_ALLOW', '');
// サイトマップ除外ページ名（正規表現）指定
define('PLUGIN_SITEMAP_PAGE_DISALLOW', '^(PukiWiki\/.*)$');
// サイトマッププライオリティ初期値：ルート
define('PLUGIN_SITEMAP_PLUGIN_PRIORITY_ROOT', '1.0');
// サイトマッププライオリティ初期値：コンテンツページ
define('PLUGIN_SITEMAP_PLUGIN_PRIORITY_CONTENT', '0.8');
// サイトマッププライオリティ初期値：タグページ
define('PLUGIN_SITEMAP_PLUGIN_PRIORITY_TAG', '0.5');

// RecentChangesの設定
if (!defined('PKWK_MAXSHOW_CACHE')) {
	define('PKWK_MAXSHOW_CACHE', 'recent.dat');
}

function plugin_sitemap_action() {
	// global宣言されている変数はpukiwiki.ini.phpで設定
	global $whatsnew;					// Modified page list
	global $non_list;					// Regex of ignore pages
	global $defaultpage;				// Top / Default page
	global $vars;

										// サイトマップキャッシュファイル
	$cache_file = CACHE_DIR . PLUGIN_SITEMAP_FILE;
										// ページ更新キャッシュファイル
	$recent_file = CACHE_DIR . PKWK_MAXSHOW_CACHE;
										// サイトマップ強制出力フラグセット
	$forced = isset($vars['page']) ? strtolower($vars['page']) : '';
	if ($forced != '' && $forced != 'true') {
		die('&page=TRUE（XMLサイトマップ強制出力）以外の引数は受け付けません');
	} else {
		$forced = ($forced == 'true');
	}
										// 初期値セット
	$script = get_script_uri();
	$body = '';

	if ( !file_exists($cache_file)  ||
	     (file_exists($recent_file) &&
	     (filemtime($cache_file) < filemtime($recent_file) ) ) || $forced ) {
		// サイトマップキャッシュファイルが存在しない または
		// ページ更新キャッシュファイルより古い場合   または
		// 強制出力オプション指定の場合はXMLサイトマップを生成する
		$urls = array();
		foreach (get_existpages() as $page) {
			if ( ($page != $whatsnew) && 
			     !preg_match("/$non_list/", $page) &&
			    ((PLUGIN_SITEMAP_PAGE_ALLOW == '') ||
                  preg_match('/' . PLUGIN_SITEMAP_PAGE_ALLOW . '/', $page)) &&
				((PLUGIN_SITEMAP_PAGE_DISALLOW  == '') ||
				  !preg_match('/' . PLUGIN_SITEMAP_PAGE_DISALLOW . '/', $page))
				&& check_readable($page, false, false) ) {
				// RecentChanges ページ以外　　　　　　　　　　 かつ
				// pukiwiki.ini設定無視ページ指定以外　　　　　 かつ
				// サイトマップ対象ページ指定に合致するページ　 かつ
				// サイトマップ除外ページ指定に合致しないページ かつ
				// 閲覧可能なページの場合は
				// XMLサイトマップ出力URLとして編集する

				// 実ページ名をURL短縮ライブラリで実際の短縮URLに変換する
				$page_url = get_short_url_from_pagename($page);
				// URLセット
				$isRoot = ($defaultpage == $page);
				$url = $script .  (!$isRoot ? $page_url : '');
				// プライオリティセット
				if ($isRoot) {
					// ルート（トップページ）URL
					$priority = PLUGIN_SITEMAP_PLUGIN_PRIORITY_ROOT;
				} else if (PLUGIN_SEO_TAG_PAGE !='' ) {
					// SEOプラグイン導入＆タグページ出力設定
					$priority = 
						(0 === strpos($page, PLUGIN_SEO_TAG_PAGE . '/')) ? 
							PLUGIN_SITEMAP_PLUGIN_PRIORITY_TAG : 
							PLUGIN_SITEMAP_PLUGIN_PRIORITY_CONTENT;
				} else {
					// SEOプラグイン未導入（もしくはタグページ出力未設定）
					$priority = PLUGIN_SITEMAP_PLUGIN_PRIORITY_CONTENT;
				}
				// ページ更新日付セット
				$time = date('Y-m-d\TH:i:sP', get_filetime($page));
				// XMLサイトマップ出力URL情報セット
				$urls[] = "  <url>\n" .
						  "    <loc>" . $url . "</loc>\n" .
						  "    <lastmod>" . $time . "</lastmod>\n" .
						  "    <priority>" . $priority . "</priority>\n" .
				          "  </url>\n";
			}
		}

		sort($urls, SORT_NATURAL | SORT_FLAG_CASE);
		foreach ($urls as $url) $body .= $url;
		$body = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . 
				'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
				. "\n" . $body . "</urlset>\n";

		// XMLサイトマップキャッシュファイル書き込み
		$fp = fopen($cache_file, 'w');
		flock($fp, LOCK_EX);
		rewind($fp);
		fwrite($fp, $body);
		flock($fp, LOCK_UN);
		fclose($fp);
	} else {
		// XMLサイトマップキャッシュファイル読み込み
		$fp = fopen($cache_file, 'r');
		$body = fread($fp, filesize($cache_file));
		fclose($fp);
	}

	// XMLサイトマップ出力
	header('Content-Type: text/xml; charset=UTF-8');
	echo $body;

	exit;
}
