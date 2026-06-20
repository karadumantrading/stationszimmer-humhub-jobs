<?php

namespace humhub\modules\jobs\assets;

use yii\web\AssetBundle;

/**
 * CSS des Moduls. Quelle ist der `resources`-Ordner des Moduls; HumHub
 * publiziert ihn automatisch.
 *
 * @verify: AssetBundle-Publishing gegen installierte HumHub-Version.
 */
class Assets extends AssetBundle
{
    public $css = ['css/jobs.css'];

    public function init()
    {
        $this->sourcePath = dirname(__DIR__) . '/resources';
        parent::init();
    }
}
