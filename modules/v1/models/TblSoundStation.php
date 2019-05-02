<?php

namespace app\modules\v1\models;

use Yii;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "tbl_sound_station".
 *
 * @property int $sound_station_id รหัส
 * @property string $sound_station_name ชื่อ/จุดบริการ
 * @property string $counter_service_id จุดบริการ
 * @property int $sound_station_status สถานะ
 */
class TblSoundStation extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_DEACTIVE = 0;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_sound_station';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sound_station_name', 'counter_service_id', 'sound_station_status'], 'required'],
            [['counter_service_id'], 'string'],
            [['sound_station_status'], 'integer'],
            [['sound_station_name'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'sound_station_id' => 'รหัส',
            'sound_station_name' => 'ชื่อ/จุดบริการ',
            'counter_service_id' => 'จุดบริการ',
            'sound_station_status' => 'สถานะ',
        ];
    }

    // สถานะการใช้งาน
    public function getStatusNameById($code)
    {
        $status = $this->getAllStatus();
        return ArrayHelper::getValue($status,$code, '');
    }

    // สถานะการใช้งาน
    public function getStatusName()
    {
        $status = $this->getAllStatus();
        return ArrayHelper::getValue($status,$this->sound_station_status, '');
    }

    // สถานะทั้งหมด
    public function getAllStatus()
    {
        return [
            self::STATUS_ACTIVE => 'เปิดใช้งาน',
            self::STATUS_DEACTIVE => 'ปิดใช้งาน'
        ];
    }
}
