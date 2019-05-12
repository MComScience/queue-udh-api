<?php

namespace app\modules\v1\traits;

use yii\web\NotFoundHttpException;
use app\modules\v1\models\TblCaller;
use app\modules\v1\models\TblCard;
use app\modules\v1\models\TblCounter;
use app\modules\v1\models\TblCounterService;
use app\modules\v1\models\TblDept;
use app\modules\v1\models\TblDeptGroup;
use app\modules\v1\models\TblKiosk;
use app\modules\v1\models\TblPatient;
use app\modules\v1\models\TblPriority;
use app\modules\v1\models\TblQueue;
use app\modules\v1\models\TblQueueStatus;
use app\modules\v1\models\TblSound;
use app\modules\v1\models\TblSoundStation;

trait ModelTrait
{
    // ข้อมูลคิวเรียก
    protected function findModelCaller($id)
    {
        if (($model = TblCaller::findOne($id)) !== null) {
            return $model;
        } else {
            $this->handleError(TblCaller::className());
        }
    }

    // บัตรคิว
    protected function findModelCard($id)
    {
        if (($model = TblCard::findOne($id)) !== null) {
            return $model;
        } else {
            $this->handleError(TblCard::className());
        }
    }

    // เคาท์เตอร์
    protected function findModelCounter($id)
    {
        if (($model = TblCounter::findOne($id)) !== null) {
            return $model;
        } else {
            $this->handleError(TblCounter::className());
        }
    }

    // จุดบริการ ช่องบริการ
    protected function findModelCounterService($id)
    {
        if (($model = TblCounterService::findOne($id)) !== null) {
            return $model;
        } else {
            $this->handleError(TblCounterService::className());
        }
    }

    // แผนก
    protected function findModelDept($id)
    {
        if (($model = TblDept::findOne($id)) !== null) {
            return $model;
        } else {
            $this->handleError(TblDept::className());
        }
    }

    // กลุ่มแผนก
    protected function findModelDeptGroup($id)
    {
        if (($model = TblDeptGroup::findOne($id)) !== null) {
            return $model;
        } else {
            $this->handleError(TblDeptGroup::className());
        }
    }

    // ตู้ออกบัตรคิว
    protected function findModelKiosk($id)
    {
        if (($model = TblKiosk::findOne($id)) !== null) {
            return $model;
        } else {
            $this->handleError(TblKiosk::className());
        }
    }

    // ข้อมูลผู้ป่วย
    protected function findModelPatient($id)
    {
        if (($model = TblPatient::findOne($id)) !== null) {
            return $model;
        } else {
            $this->handleError(TblPatient::className());
        }
    }

    // ลำดับความสำคัญ
    protected function findModelPriority($id)
    {
        if (($model = TblPriority::findOne($id)) !== null) {
            return $model;
        } else {
            $this->handleError(TblPriority::className());
        }
    }

    // ข้อมูลคิว
    protected function findModelQueue($id)
    {
        if (($model = TblQueue::findOne($id)) !== null) {
            return $model;
        } else {
            $this->handleError(TblQueue::className());
        }
    }

    // สถานะคิว
    protected function findModelQueueStatus($id)
    {
        if (($model = TblQueueStatus::findOne($id)) !== null) {
            return $model;
        } else {
            $this->handleError(TblQueueStatus::className());
        }
    }

    // ข้อมูลเสียง
    protected function findModelSound($id)
    {
        if (($model = TblSound::findOne($id)) !== null) {
            return $model;
        } else {
            $this->handleError(TblSound::className());
        }
    }

    // โปรแกรมเสียง
    protected function findModelSoundStation($id)
    {
        if (($model = TblSoundStation::findOne($id)) !== null) {
            return $model;
        } else {
            $this->handleError(TblSoundStation::className());
        }
    }

    private function handleError($className)
    {
        throw new NotFoundHttpException('The requested data does not exist.');
    }
}