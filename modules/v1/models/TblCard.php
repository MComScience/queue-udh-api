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

    // สถานะทั้งหมด
    public function getAllStatus()
    {
        return [
            self::STATUS_ACTIVE => 'เปิดใช้งาน',
            self::STATUS_DEACTIVE => 'ปิดใช้งาน'
        ];
    }

    public function getDefaultCard()
    {
        return '<div class="x_content">
        <div class="row" style="margin-bottom:0px; margin-left:0px; margin-right:0px; margin-top:0px">
        <div class="col-md-12 col-sm-12 col-xs-12" style="padding:0 21px 0px 21px">
        <div class="col-xs-12" style="padding:0"><img alt="" class="center-block" src="/images/udh-logo.png" style="display:block; margin-left:auto; margin-right:auto; width:60px" /></div>
        </div>
        
        <p style="text-align:center"><span style="font-size:14px"><strong>โรงพยาบาลอุดรธานี</strong> </span></p>
        
        <p style="text-align:center"><strong>{dept_name}</strong></p>
        
        <p style="margin-left:0px; margin-right:0px; text-align:center"><span style="font-size:36px"><strong>{number}</strong> </span></p>
        
        <h4 style="margin-left:1px; margin-right:1px; text-align:center"><span style="font-size:18px"><strong>HN</strong> : <strong> {hn} </strong> </span></h4>
        
        <p style="margin-left:1px; margin-right:1px; text-align:center"><span style="font-size:16px"><strong>ชื่อ</strong> : <strong> {fullname} </strong> </span></p>
        
        <p style="text-align:center">สิทธิการรักษา</p>
        
        <div class="maininscl_name">
        <p style="margin-left:10%; text-align:left"><input checked="checked" type="checkbox" /> <strong> {message_right} </strong></p>
        </div>
        
        <div class="col-xs-8 col-xs-offset-2" style="border-top:dashed 1px #404040; padding:4px 0px 3px 0px">
        <div class="col-xs-12" style="padding:1px">
        <p style="text-align:center"><span style="font-size:14px"><strong>ขั้นตอนการรับบริการ</strong> </span></p>
        </div>
        </div>
        
        <div class="col-xs-12">
        <div class="col-xs-12 text-center" style="padding-top:1px; text-align:center">
        <p style="text-align:left"><strong>1. ชั่งน้ำหนักวัดส่วนสูง</strong></p>
        
        <p style="text-align:left"><strong>2. วัดความดันโลหิต</strong></p>
        
        <p style="text-align:left"><strong>3. รอซักประวัติ</strong></p>
        </div>
        </div>
        </div>
        
        <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12" style="padding:0px 21px 0px 21px">
        <div class="col-xs-6" style="padding:0; text-align:center">
        {qrcode}
        </div>
        
        <div class="col-xs-6" style="padding:0px; text-align:center">{barcode}</div>
        </div>
        </div>
        
        <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12" style="padding:10px 0px 0px">
        <h4 style="text-align:center"><strong>ขอบคุณที่ใช้บริการ</strong></h4>
        </div>
        </div>
        
        <div class="row">
        <div class="col-md-6 col-sm-6 col-xs-6" style="padding:10px 0px 0px">
        <div class="col-xs-12">
        <p style="text-align:left">{date}</p>
        </div>
        </div>
        
        <div class="col-md-6 col-sm-6 col-xs-6" style="padding:10px 0px 0px; text-align:right">
        <div class="col-xs-12">
        <p style="text-align:right">{time}</p>
        </div>
        </div>
        </div>
        </div>
        ';
    }
}
