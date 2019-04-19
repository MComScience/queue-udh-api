<?php
namespace app\modules\v1\models;

use dektrium\user\models\Profile as BaseProfile;
use trntv\filekit\behaviors\UploadBehavior;

class Profile extends BaseProfile
{
    public function rules()
    {
        $rules = parent::rules();
        return $rules;
    }
}