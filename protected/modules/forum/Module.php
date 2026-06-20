<?php

namespace humhub\modules\forum;

use Yii;
use yii\helpers\Url;
use humhub\modules\user\models\User;

/**
 * Modul «Forum» – kategorie-basiertes Pflege-Forum.
 *
 * @verify: humhub\components\Module gegen installierte Version.
 */
class Module extends \humhub\components\Module
{
    public function getConfigUrl()
    {
        return Url::to(['/forum/admin/category/index']);
    }

    /**
     * Darf der/die User moderieren (anpinnen, sperren, fremde Beiträge löschen)?
     * MVP: System-Admins. Später optional eine konfigurierbare Moderations-Gruppe.
     */
    public static function canModerate(?User $user = null): bool
    {
        $user = $user ?? Yii::$app->user->getIdentity();
        return $user !== null && $user->isSystemAdmin();
    }
}
