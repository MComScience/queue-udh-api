<?php
namespace app\modules\v1\models;

use dektrium\user\models\LoginForm as BaseLoginForm;

class LoginForm extends BaseLoginForm
{
    public function getUser(){
        return $this->user;
    }
}