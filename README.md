# yuncms-ueditor-widget

适用于 YUNCMS 的百度 UEditor

[![Latest Stable Version](https://poser.pugx.org/yuncms/yuncms-ueditor-widget/v/stable.png)](https://packagist.org/packages/yuncms/yuncms-ueditor-widget)
[![Total Downloads](https://poser.pugx.org/yuncms/yuncms-ueditor-widget/downloads.png)](https://packagist.org/packages/yuncms/yuncms-ueditor-widget)
[![Build Status](https://img.shields.io/travis/yuncms/yuncms-ueditor-widget.svg)](http://travis-ci.org/yuncms/yuncms-ueditor-widget)
[![License](https://poser.pugx.org/yuncms/yuncms-ueditor-widget/license.svg)](https://packagist.org/packages/yuncms/yuncms-ueditor-widget)


## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yuncms/yuncms-ueditor-widget "*"
```

or add

```
"yuncms/yuncms-ueditor-widget": "*"
```

to the require section of your `composer.json` file.


Usage
-----

```php
<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;

class MyController extends Controller
{

    /** @inheritdoc */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => [
                            'upload'
                        ],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'upload' => [
                'class' => 'yuncms\ueditor\UEditorAction',
                //etc...
            ],
        ];
    }
}
````

Once the extension is installed, simply use it in your code by  :

```php

<?= $form->field($model, 'content')->widget(\yuncms\ueditor\UEditor::className(),[
	//etc...
]) ?>
<?= \yuncms\ueditor\UEditor::widget(); ?>
