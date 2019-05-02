<?php

namespace app\modules\v1\models;

use Yii;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "tbl_card".
 *
 * @property int $card_id รหัสบัตรคิว
 * @property string $card_name ชื่อบัตรคิว
 * @property string $card_template แบบบัตรคิว
 * @property int $card_status สถานะ
 */
class TblCard extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_DEACTIVE = 0;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_card';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['card_name', 'card_template', 'card_status'], 'required'],
            [['card_template'], 'string'],
            [['card_status'], 'integer'],
            [['card_name'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'card_id' => 'รหัสบัตรคิว',
            'card_name' => 'ชื่อบัตรคิว',
            'card_template' => 'แบบบัตรคิว',
            'card_status' => 'สถานะ',
        ];
    }

    // สถานะการใช้งาน
    public function getStatusNameById($code)
    {
        $status = [
            self::STATUS_ACTIVE => 'เปิดใช้งาน',
            self::STATUS_DEACTIVE => 'ปิดใช้งาน'
        ];
        return ArrayHelper::getValue($status,$code, '');
    }

    // สถานะการใช้งาน
    public function getStatusName()
    {
        $status = [
            self::STATUS_ACTIVE => 'เปิดใช้งาน',
            self::STATUS_DEACTIVE => 'ปิดใช้งาน'
        ];
        return ArrayHelper::getValue($status,$this->card_status, '');
    }
}
