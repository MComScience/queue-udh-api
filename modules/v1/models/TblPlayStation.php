<?php

namespace app\modules\v1\models;

use Yii;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "tbl_play_station".
 *
 * @property int $play_station_id
 * @property string $play_station_name ชื่อโปรแกรมเสียง
 * @property string $counter_id เคาน์เตอร์
 * @property string $counter_service_id ช่องบริการ/ห้องตรวจ
 * @property string $last_active_date วันที่เล่นเสียงล่าสุด
 * @property int $play_station_status สถานะ
 */
class TblPlayStation extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_DEACTIVE = 0;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_play_station';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['play_station_name', 'counter_id', 'counter_service_id', 'play_station_status'], 'required'],
            [['play_station_id', 'play_station_status'], 'integer'],
            [['counter_id', 'counter_service_id'], 'safe'],
            [['last_active_date'], 'safe'],
            [['play_station_name'], 'string', 'max' => 255],
            [['play_station_id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'play_station_id' => 'Play Station ID',
            'play_station_name' => 'ชื่อโปรแกรมเสียง',
            'counter_id' => 'เคาน์เตอร์',
            'counter_service_id' => 'ช่องบริการ/ห้องตรวจ',
            'last_active_date' => 'วันที่เล่นเสียงล่าสุด',
            'play_station_status' => 'สถานะ',
        ];
    }

    /**
     * {@inheritdoc}
     * @return TblPlayStationQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TblPlayStationQuery(get_called_class());
    }

     // สถานะทั้งหมด
    public function getAllStatus()
    {
        return [
            self::STATUS_ACTIVE => 'เปิดใช้งาน',
            self::STATUS_DEACTIVE => 'ปิดใช้งาน'
        ];
    }

    public function getCounterList()
    {
        $li = [];
        if ($this->counter_id) {
            $counters = unserialize($this->counter_id);
            $items = TblCounter::find()->where(['counter_id' => $counters])->asArray()->all();
            foreach ($items as $item) {
                $li[] = Html::tag('li', $item['counter_name']);
            }
        }
        return Html::tag('ol', implode("\n", $li));
    }

    public function getCounterServiceList()
    {
        $li = [];
        if ($this->counter_service_id) {
            $counters = unserialize($this->counter_service_id);
            $items = (new \yii\db\Query())
                ->select(['tbl_counter_service.counter_service_id', 'CONCAT(\'(\',tbl_counter.counter_name, \') \', tbl_counter_service.counter_service_name) AS counter_service_name'])
                ->from('tbl_counter_service')
                ->innerJoin('tbl_counter', 'tbl_counter.counter_id = tbl_counter_service.counter_id')
                ->where([
                    'tbl_counter_service.counter_service_status' => 1,
                    'tbl_counter.counter_id' => $counters
                ])
                ->all();
            foreach ($items as $item) {
                $li[] = Html::tag('li', $item['counter_service_name']);
            }
        }
        return Html::tag('ol', implode("\n", $li));
    }

    // สถานะการใช้งาน
    public function getStatusName()
    {
        $status = $this->getAllStatus();
        return ArrayHelper::getValue($status, $this->play_station_status, '');
    }
}
