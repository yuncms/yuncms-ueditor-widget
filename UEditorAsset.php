<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */
namespace yuncms\ueditor;

use Yii;
use yii\web\AssetBundle;

/**
 * Class MarkdownEditorAsset
 * @package xutl\ueditor
 */
class UEditorAsset extends AssetBundle
{
    public $sourcePath = '@vendor/yuncms/yuncms-ueditor-widget/assets';

    public $js = [
        'ueditor.config.js',
        'ueditor.all.min.js',
    ];

    public $css = [
        'themes/iframe.css',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];
}