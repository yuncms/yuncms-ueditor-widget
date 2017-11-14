<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\ueditor;

use Yii;
use yii\base\Action;
use yii\web\Response;
use yii\helpers\ArrayHelper;
use yuncms\attachment\AttachmentTrait;
use yuncms\attachment\models\Attachment;
use yuncms\attachment\components\Uploader;

/**
 * Class UEditorAction
 * @package yuncms\ueditor
 */
class UEditorAction extends Action
{
    use AttachmentTrait;

    /**
     * @var array 客户端配置参数
     */
    public $options = [];
    /**
     * @var array 允许上传的图片文件后缀
     */
    public $imageAllowFiles;
    /**
     * @var array 允许上传的视频文件后缀
     */
    public $videoAllowFiles;
    /**
     * @var array 允许上传的普通文件后缀
     */
    public $fileAllowFiles;

    /**
     * Initializes the action and ensures the temp path exists.
     */
    public function init()
    {
        parent::init();
        $this->initClientOptions();
        //关闭CSRF
        $this->controller->enableCsrfValidation = false;
    }

    /**
     * 获取客户端配置
     * @return void
     */
    public function initClientOptions()
    {
        $fileAllowFiles = $this->normalizeExtension($this->getSetting('fileAllowFiles'));
        $imageAllowFiles = $this->normalizeExtension($this->getSetting('imageAllowFiles'));
        $videoAllowFiles = $this->normalizeExtension($this->getSetting('videoAllowFiles'));

        $imageMaxSize = $this->getMaxUploadByte($this->getSetting('imageMaxSize'));
        $videoMaxSize = $this->getMaxUploadByte($this->getSetting('videoMaxSize'));
        $fileMaxSize = $this->getMaxUploadByte($this->getSetting('fileMaxSize'));
        $this->options = ArrayHelper::merge([
            "imageActionName" => "upload-image",
            "imageFieldName" => "upfile",
            /* 上传大小限制，单位B */
            "imageMaxSize" => $imageMaxSize,
            /* 上传图片格式显示 */
            "imageAllowFiles" => $imageAllowFiles,
            "imageCompressEnable" => true,
            "imageCompressBorder" => 1600,
            "imageInsertAlign" => "none",
            "imageUrlPrefix" => "",
            /* 涂鸦图片上传配置项 */
            "scrawlActionName" => "upload-scrawl",
            "scrawlFieldName" => "upfile",
            /* 上传大小限制，单位B */
            "scrawlMaxSize" => $imageMaxSize,
            /* 图片访问路径前缀 */
            "scrawlUrlPrefix" => "",
            "scrawlInsertAlign" => "none",
            /* 截图工具上传 */
            /* 执行上传截图的action名称 */
            "snapscreenActionName" => "upload-image",
            /* 上传保存路径,可以自定义保存路径和文件名格式 */
            "snapscreenUrlPrefix" => "",
            "snapscreenInsertAlign" => "none",
            /* 抓取远程图片配置 */
            "catcherLocalDomain" => ["127.0.0.1", "localhost"],
            "catcherActionName" => "catch-image",
            "catcherFieldName" => "source",
            "catcherUrlPrefix" => "",
            /* 上传大小限制，单位B */
            "catcherMaxSize" => $imageMaxSize,
            /* 抓取图片格式显示 */
            "catcherAllowFiles" => $imageAllowFiles,

            /* 上传视频配置 */
            "videoActionName" => "upload-video",
            "videoFieldName" => "upfile",
            "videoUrlPrefix" => "",
            /* 视频访问路径前缀 */
            "videoMaxSize" => $videoMaxSize,
            /* 上传大小限制，单位B，默认100MB */
            "videoAllowFiles" => $videoAllowFiles,

            /* 上传文件配置 */
            "fileActionName" => "upload-file",
            "fileFieldName" => "upfile",
            "fileUrlPrefix" => "",
            "fileMaxSize" => $fileMaxSize,
            /* 上传大小限制，单位B，默认50MB */
            "fileAllowFiles" => $fileAllowFiles,
            /* 上传文件格式显示 */
            "imageManagerActionName" => "list-image",
            /* 执行图片管理的action名称 */
            "imageManagerListPath" => "",
            "imageManagerListSize" => 20,
            "imageManagerUrlPrefix" => "",
            "imageManagerInsertAlign" => "none",
            "imageManagerAllowFiles" => $imageAllowFiles,
            /* 列出的文件类型 */
            "fileManagerActionName" => "list-file",
            "fileManagerListPath" => "",
            "fileManagerUrlPrefix" => "",
            "fileManagerListSize" => 20,
            "fileManagerAllowFiles" => $fileAllowFiles
            /* 列出的文件类型 */
        ], $this->options);
    }

    /**
     * 执行该Action
     *
     * @param string $action 操作名称
     * @param string $callback 回调方法
     * @return string|array
     */
    public function run($action, $callback = null)
    {
        if ($action == 'config') {
            $result = $this->options;
        } else if (in_array($action, ['upload-file', 'upload-image'])) {
            $result = $this->upload($action);
        } else if (in_array($action, ['list-image', 'list-file'])) {
            $result = $this->lists($action);
        } else if ($action == 'catch-image') {
            $result = $this->uploadCrawler();
        } else if ($action == 'upload-scrawl') {//涂鸦上传
            $result = $this->uploadScrawl();
        } else {
            $result = ['state' => 'Request address error'];
        }
        if (is_null($callback)) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $result;
        } else {
            Yii::$app->response->format = Response::FORMAT_JSONP;
            return ['callback' => $callback, 'data' => $result];
        }
    }

    /**
     * 上传
     * @param $action
     * @return array|string
     */
    protected function upload($action)
    {
        switch ($action) {
            case 'upload-image':
                $fieldName = $this->options['imageFieldName'];
                $config = [
                    'maxFiles' => 1,
                    'extensions' => $this->getSetting('imageAllowFiles'),
                    'checkExtensionByMimeType' => false,
                    "maxSize" => $this->options['imageMaxSize'],
                ];
                break;
            case 'upload-video':
                $fieldName = $this->options['videoFieldName'];
                $config = [
                    'maxFiles' => 1,
                    'extensions' => $this->getSetting('videoAllowFiles'),
                    'maxSize' => $this->options['videoMaxSize'],
                    'checkExtensionByMimeType' => false,
                ];
                break;
            default:
                $fieldName = $this->options['fileFieldName'];
                $config = [
                    'maxFiles' => 1,
                    'extensions' => $this->getSetting('fileAllowFiles'),
                    'maxSize' => $this->options['fileMaxSize'],
                    'checkExtensionByMimeType' => false,
                ];
                break;
        }
        $uploader = new Uploader([
            'fileField' => $fieldName,
            'config' => $config,
        ]);
        $uploader->upFile();
        return $uploader->getFileInfo();
    }

    /**
     * 涂鸦上传
     * @return array|string
     */
    protected function uploadScrawl()
    {
        /* 上传配置 */
        $config = [
            'maxFiles' => 1,
            'extensions' => $this->getSetting('imageAllowFiles'),
            'checkExtensionByMimeType' => false,
            "maxSize" => $this->options['imageMaxSize'],
            "oriName" => "scrawl.png"
        ];

        $uploader = new Uploader([
            'fileField' => $this->options['scrawlFieldName'],
            'config' => $config,
        ]);
        $uploader->upBase64();
        return $uploader->getFileInfo();
    }

    /**
     * 远程图片本地化
     */
    protected function uploadCrawler()
    {
        /* 上传配置 */
        $config = [
            'maxFiles' => 1,
            'extensions' => $this->getSetting('imageAllowFiles'),
            'checkExtensionByMimeType' => false,
            "maxSize" => $this->options['imageMaxSize'],
            "oriName" => "remote.png"
        ];
        $sources = Yii::$app->request->post($this->options['catcherFieldName']);
        if (is_array($sources)) {
            $lists = [];
            foreach ($sources as $imgUrl) {
                $uploader = new Uploader([
                    'fileField' => $imgUrl,
                    'config' => $config,
                ]);
                $uploader->saveRemote();
                $info = $uploader->getFileInfo();
                array_push($lists, [
                    "state" => $info["state"],
                    "url" => $info["url"],
                    "size" => $info["size"],
                    "title" => htmlspecialchars($info["title"]),
                    "original" => htmlspecialchars($info["original"]),
                    "source" => htmlspecialchars($imgUrl)
                ]);
            }
            return $lists;
        } else {
            return [
                'state' => Yii::t('attachment', 'File write failed.'),
            ];
        }
    }

    /**
     * 获取已上传的文件列表
     * @param $action
     * @return array
     */
    protected function lists($action)
    {
        //查询实例
        $query = Attachment::find()->where(['user_id' => Yii::$app->user->id])->orderBy(['id' => SORT_DESC]);
        /* 判断类型 */
        switch ($action) {
            /* 列出文件 */
            case 'list-file':
                $query->andWhere(['ext' => explode(',', Yii::$app->settings->get('fileAllowFiles', 'attachment'))]);
                break;
            /* 列出图片 */
            case 'list-image':
            default:
                $query->andWhere(['ext' => explode(',', Yii::$app->settings->get('imageAllowFiles', 'attachment'))]);
        }
        $offset = Yii::$app->request->get('start', 0);
        $limit = Yii::$app->request->get('size', $this->options['imageManagerListSize']);
        $total = $query->count();
        if ($total > 0) {
            $files = $query->limit($limit)->offset($offset)->asArray()->all();
            $lists = [];
            foreach ($files as $file) {
                array_push($lists, [
                    'original' => $file['filename'],
                    'url' => $this->getSetting('storeUrl') . $file['path'],
                    'mtime' => $file['created_at']
                ]);
            }
            return ["state" => "SUCCESS", "list" => $lists, "start" => 0, "total" => $total];
        } else {
            return ["state" => "no match file", "list" => [], "start" => $offset, "total" => $total];
        }
    }
}