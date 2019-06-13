<?php
namespace app\components;

use app\modules\v1\traits\ModelTrait;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\base\InvalidConfigException;
use yii\helpers\Url;

class SoundComponent extends Component
{
    use ModelTrait;

    public $number;

    public $counter_service_id;

    private $_source;

    public function init()
    {
        parent::init();
        if ($this->number == null || $this->counter_service_id == null) {
            throw new InvalidConfigException(
                "{counter_service_id} or {number} was not found. \n\n"
            );
        }
        $this->getMedia();
    }

    private function getMedia()
    {
        if ($this->number != null && $this->counter_service_id != null) {
            $txt_split = str_split($this->number);
            $modelCounter = $this->findModelCounterService($this->counter_service_id);
            $modelSound = $modelCounter->serviceNoSound;//เสียงหมายเลข (หนึ่ง สอง สาม)
            $ServiceSound = $modelCounter->serviceSound;//เสียงบริการ (ที่ช่อง ที่ห้อง ที่โต๊ะ)
            $basePath = "/media/" . $modelSound['sound_path_name'];
            $hostname = Url::base(true);
            $begin = [$hostname.$basePath . "/please.wav"]; //เชิญหมายเลข
            $end = [//ที่โต๊ะ 1 ค่ะ
                $hostname."/media/" . $ServiceSound['sound_path_name'] . '/' . $ServiceSound['sound_name'],
                $hostname.$basePath . '/' . $modelSound['sound_name'],
                $hostname.$basePath . '/' . $modelSound['sound_path_name'] . '_Sir.wav',
            ];

            $sound = array_map(function ($num) use ($basePath, $modelSound, $hostname) {//A001
                return $hostname.$basePath . '/' . $modelSound['sound_path_name'] . '_' . $num . '.wav';
            }, $txt_split);
            $sound = ArrayHelper::merge($begin, $sound);//[เชิญหมายเลข, A001]
            $sound = ArrayHelper::merge($sound, $end);// [เชิญหมายเลขA001, ที่โต๊ะ 1 ค่ะ]
            $this->_source = $sound;
        }
    }

    public function getSource()
    {
        return $this->_source;
    }
}