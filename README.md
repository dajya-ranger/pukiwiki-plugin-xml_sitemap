# pukiwiki-plugin-xml_sitemap

PukiWiki用URL短縮ライブラリ対応XMLサイトマッププラグイン

- 暫定公開版です（[PukiWiki1.5.2](https://pukiwiki.osdn.jp/?PukiWiki/Download/1.5.2)及び[PukiWiki1.5.3](https://pukiwiki.osdn.jp/?PukiWiki/Download/1.5.3)で動作確認済）
- 本プラグインを利用することで、[PukiWiki用URL短縮ライブラリ](https://dajya-ranger.com/sdm_downloads/short-url-library-pkwk153/)を導入しているサイトのXMLサイトマップを出力することが可能です
- (PukiWiki)(https://ja.wikipedia.org/wiki/PukiWiki)でXMLサイトマップを作成・送信する意味や方法について分からない方は、自サイトの記事「[PukiWiki1.5.2にXMLサイトマップとGoogleアナリティクスを組み込む！](https://dajya-ranger.com/pukiwiki/embed-xml-sitemap-google-analytics/)」を参照して下さい
- 本プラグインでは、Googleで考慮されないとされる更新頻度（changefreq）出力には対応せず、URLの優先度（priority）に関しても次の通りザックリとしか出力しません（プラグインの設定で設定値は変更可能）
	- ルート（トップページ）URLは優先度「1.0」
	- コンテンツページURLは一律優先度「0.8」
	- [PukiWiki用SEO対応プラグイン](https://dajya-ranger.com/sdm_downloads/seo-support-plugin/)で出力するタグページURLは一律優先度「0.5」（当該プラグインを導入してタグ出力を設定している場合に設定され、未導入・未設定の場合はコンテンツページURLと同一優先度とする）
- プラグインの設置に関しては自サイトの記事「[PukiWiki用URL短縮ライブラリ対応XMLサイトマッププラグインを導入する！](https://dajya-ranger.com/pukiwiki/xml-sitemap-plugin/)」（執筆予定）を参照して下さい
- [PukiWiki](https://ja.wikipedia.org/wiki/PukiWiki)サイト汎用の「robots.txt」も同梱しているので、サイトのルートディレクトリに配置して活用して下さい（トップページ名が「FrontPage」でない場合はその部分だけ書き換えてお使い下さい）
