<?php
namespace app\modules\v1\components;

use yii\base\Component;

class AutoNumber extends Component
{
    public $prefix = 1;

    public $number = 1;

    public $digit = 6;

    public function init()
    {
        parent::init();
    }

    public function generate(){
        if(empty($this->number) || $this->number === null){
            //return $this->sprintPrefix($this->digit, 1);
            return $this->prefix.sprintf("%'.0".$this->digit."d", 1);
        }
        if(is_numeric($this->number)){
            $number = substr($this->number,strlen($this->prefix));
            //return $this->sprintPrefix($this->digit, ($number + 1));
            return $this->prefix.sprintf("%'.0".$this->digit."d", ((int)$number + 1));
        }elseif (is_string($this->number)) {
            $prefix = substr($this->number,0,strlen($this->prefix));
            $number = substr($this->number,strlen($this->prefix));
            $length = strlen($number);
            if(is_numeric($number)){
                //return $this->sprintPrefix($length, ($number + 1));
                return $this->prefix.sprintf("%'.0".($length)."d", ($number + 1));
            }else{
                //return $this->sprintPrefix($length, 1);
                return $this->prefix.sprintf("%'.0".($length)."d", 1);
            }
        }
    }

    private function sprintPrefix($length, $no)
    {
        return $this->prefix.sprintf("%'.0".($length)."d", $no);
    }
}