<?php

namespace app\modules\v1\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
/**
 * This is the model class for table "tbl_display".
 *
 * @property int $display_id
 * @property string $display_name ชื่อจอแสดงผล
 * @property string $display_css Css
 * @property int $page_length จำนวนรายการที่แสดง(แถว)
 * @property string $counter_id เคาน์เตอร์
 * @property string $service_id ชื่อบริการ/แผนก/ห้องตรวจ
 * @property int $display_status สถานะ
 */
class TblDisplay extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_DEACTIVE = 0;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_display';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['display_name', 'counter_id', 'service_id', 'display_status'], 'required'],
            [['display_css', 'counter_id', 'service_id'], 'safe'],
            [['page_length', 'display_status'], 'integer'],
            [['display_name'], 'string', 'max' => 100],
            [['hold_label'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'display_id' => 'Display ID',
            'display_name' => 'ชื่อจอแสดงผล',
            'hold_label' => 'ข้อความคิวที่เรียกไปแล้ว',
            'display_css' => 'Css',
            'page_length' => 'จำนวนรายการที่แสดง(แถว)',
            'counter_id' => 'เคาน์เตอร์',
            'service_id' => 'ชื่อบริการ/แผนก/ห้องตรวจ',
            'display_status' => 'สถานะ',
        ];
    }

    /**
     * {@inheritdoc}
     * @return TblDisplayQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TblDisplayQuery(get_called_class());
    }

    // สถานะทั้งหมด
    public function getAllStatus()
    {
        return [
            self::STATUS_ACTIVE => 'เปิดใช้งาน',
            self::STATUS_DEACTIVE => 'ปิดใช้งาน'
        ];
    }

    // สถานะการใช้งาน
    public function getStatusName()
    {
        $status = $this->getAllStatus();
        return ArrayHelper::getValue($status, $this->display_status, '');
    }

    public function getServiceNames()
    {
        $li = [];
        if ($this->service_id) {
            $serviceIds = unserialize($this->service_id);
            $items = (new \yii\db\Query())
                ->select(['tbl_service.service_id', 'CONCAT(\'(\', tbl_queue_service.queue_service_name ,\') \', tbl_service.service_name) AS service_name'])
                ->from('tbl_service')
                ->innerJoin('tbl_service_group', 'tbl_service_group.service_group_id = tbl_service.service_group_id')
                ->innerJoin('tbl_queue_service', 'tbl_queue_service.queue_service_id = tbl_service_group.queue_service_id')
                ->where([
                    'tbl_service.service_status' => 1,
                    'tbl_service.service_id' => $serviceIds
                ])
                ->all();
            foreach ($items as $item) {
                $li[] = Html::tag('li', $item['service_name']);
            }
        }
        return Html::tag('ol', implode("\n", $li));
    }

    public function getCounterNames()
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
}
