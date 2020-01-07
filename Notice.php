<?php
/*
 * Copyright (C) 2020 Nobuhiko Kimoto
 * info@nob-log.info
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

/**
 * プラグインのメインクラス
 *
 * @package BulkOrder
 * @author Nobuhiko Kimoto
 * @version $Id: $
 */
class Notice extends SC_Plugin_Base {

    /**
     * コンストラクタ
     */
    public function __construct(array $arrSelfInfo)
    {
        parent::__construct($arrSelfInfo);
    }

    /**
     * インストール
     * installはプラグインのインストール時に実行されます.
     * 引数にはdtb_pluginのプラグイン情報が渡されます.
     *
     * @param  array $arrPlugin plugin_infoを元にDBに登録されたプラグイン情報(dtb_plugin)
     * @return void
     */
    public function install($arrPlugin)
    {
        // プラグインのロゴ画像をアップ
        if (file_exists(PLUGIN_UPLOAD_REALDIR .$arrPlugin['plugin_code']."/logo.png")) {
            if(copy(PLUGIN_UPLOAD_REALDIR . $arrPlugin['plugin_code']."/logo.png", PLUGIN_HTML_REALDIR . $arrPlugin['plugin_code']."/logo.png") === false);
        }
    }

    /**
     * アンインストール
     * uninstallはアンインストール時に実行されます.
     * 引数にはdtb_pluginのプラグイン情報が渡されます.
     *
     * @param  array $arrPlugin プラグイン情報の連想配列(dtb_plugin)
     * @return void
     */
    public function uninstall($arrPlugin)
    {
        // ロゴ画像削除
        if (file_exists(PLUGIN_HTML_REALDIR .$arrPlugin['plugin_code']."/logo.png")) {
            if(SC_Helper_FileManager_Ex::deleteFile(PLUGIN_HTML_REALDIR . $arrPlugin['plugin_code']."/logo.png") === false);
        }
    }

    /**
     * 稼働
     * enableはプラグインを有効にした際に実行されます.
     * 引数にはdtb_pluginのプラグイン情報が渡されます.
     *
     * @param  array $arrPlugin プラグイン情報の連想配列(dtb_plugin)
     * @return void
     */
    public function enable($arrPlugin)
    {
    }

    /**
     * 停止
     * disableはプラグインを無効にした際に実行されます.
     * 引数にはdtb_pluginのプラグイン情報が渡されます.
     *
     * @param  array $arrPlugin プラグイン情報の連想配列(dtb_plugin)
     * @return void
     */
    public function disable($arrPlugin)
    {
    }

    /**
     * 処理の介入箇所とコールバック関数を設定
     * registerはプラグインインスタンス生成時に実行されます
     *
     * @param SC_Helper_Plugin $objHelperPlugin
     */
    public function register(SC_Helper_Plugin $objHelperPlugin)
    {
        //$objHelperPlugin->addAction('prefilterTransform', array(&$this, 'prefilterTransform'), $this->arrSelfInfo['priority']);
        $objHelperPlugin->addAction("LC_Page_preProcess", array($this, "preProcess"), $this->arrSelfInfo['priority']);
    }

    function preProcess($objPage) {
        if (isset($objPage->tpl_authority)) {

            $url = HTTP_URL . 'data/logs/site.log';
            if (self::is_open($url)) {
                SC_Utils_Ex::sfErrorHeader('&gt;&gt; ' . $url. ' が公開されています。情報漏えいの可能性があります。対処してください。 <a href="https://nob-log.info/2013/05/25/wrong-installation-eccube-is-dangerous/">https://nob-log.info/2013/05/25/wrong-installation-eccube-is-dangerous/</a>');
            }

            $url = 'https://www.ec-cube.net/info/weakness/index.php?level=3&version='.ECCUBE_VERSION;
            if (self::is_open($url)) {
                SC_Utils_Ex::sfErrorHeader('&gt;&gt; <a href="'.$url.'">脆弱性リスト</a>を確認してください。');
            }
        }
    }

    function is_open($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Chrome 10');

        if (!curl_exec($ch)) {
            return false;
        }

        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return $code == '200';
    }
}
