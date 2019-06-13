<?php
namespace app\filters;

use app\modules\v1\models\User;
use Closure;

class AccessRule extends \yii\filters\AccessRule {

    /**
     * @inheritdoc
     */
    protected function matchRole($user)
    {
        $roles = empty($this->roles) ? [] : $this->roles;
        if (empty($roles)) {
            return true;
        }

        if ($user === false) {
            throw new InvalidConfigException('The user application component must be available to specify roles in AccessRule.');
        }
        foreach ($roles as $role) {
            if ($role === '?' && \Yii::$app->user->isGuest) {
                return true;
            } elseif (!\Yii::$app->user->isGuest && $role === '@') {
                return true;
            } elseif(!\Yii::$app->user->isGuest && $role == $user->identity->role){
                return true;
            } elseif ($user->can($role)) {
                return true;
            }
        }

        return false;
    }
}