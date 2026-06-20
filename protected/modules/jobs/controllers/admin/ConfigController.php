<?php

namespace humhub\modules\jobs\controllers\admin;

use Yii;
use humhub\modules\admin\components\Controller;
use humhub\modules\jobs\Module;
use humhub\modules\jobs\models\forms\ConfigForm;

/**
 * Admin-Konfiguration des Moduls (Administration → Jobbörse).
 *
 * @verify: humhub\modules\admin\components\Controller erzwingt Admin-Rechte
 * gegen die installierte Version (ManageModules-Permission).
 */
class ConfigController extends Controller
{
    public function actionIndex()
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('jobs');

        $form = new ConfigForm();
        if ($form->load(Yii::$app->request->post()) && $form->saveSettings($module)) {
            $this->view->saved();
            return $this->redirect(['/jobs/admin/config']);
        }
        $form->loadSettings($module);

        return $this->render('@jobs/views/admin/config', ['model' => $form]);
    }
}
