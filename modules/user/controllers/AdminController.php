<?php

/**
 * Created by PhpStorm.
 * User: Tanakorn Phompak
 * Date: 14/5/2562
 * Time: 11:16
 */

namespace app\modules\user\controllers;

use app\modules\v1\models\Profile;
use Yii;
use app\modules\v1\models\User;
use dektrium\user\controllers\AdminController as BaseAdminController;
use yii\helpers\FileHelper;
use yii\helpers\Json;
use yii\web\UploadedFile;

class AdminController extends BaseAdminController
{
    public function actionImportUser()
    {
        $model = new User();
        if ($model->load(Yii::$app->request->post())) {
            $model->excelFile = UploadedFile::getInstance($model, 'excelFile');
            if (!empty($model->excelFile) && $model->upload()) {
                $filename = $model->excelFile->baseName . '.' . $model->excelFile->extension;
                $filepath = Yii::getAlias('@webroot/uploads/') . $filename;
                try {
                    $inputFile = \PHPExcel_IOFactory::identify($filepath);
                    $objReader = \PHPExcel_IOFactory::createReader($inputFile);
                    $objPHPExcel = $objReader->load($filepath);
                } catch (Exception $e) {
                    Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด' . $e->getMessage());
                }

                $sheet = $objPHPExcel->getSheet(0);
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                $objWorksheet = $objPHPExcel->getActiveSheet();

                foreach ($objWorksheet->getRowIterator() as $rowIndex => $row) {
                    $tmpdata = $objWorksheet->rangeToArray('A' . $rowIndex . ':' . $highestColumn . $rowIndex);
                    $data[] = $tmpdata[0];
                }
                $header = $data[0];
                unset($data[0]);
                $success = 0;
                $fail = 0;
                $userUnique = [];
                foreach ($data as $data) {
                    $user = \Yii::createObject([
                        'class' => User::className(),
                        'scenario' => 'create',
                    ]);
                    $user->setAttributes([
                        'email' => $data[1],
                        'username' => $data[2],
                        'password' => (string)$data[3],
                        'role' => 10
                    ]);

                    $profile = \Yii::createObject(Profile::className());
                    $profile->setAttributes([
                        'name' => $data[0],
                        'timezone' => 'Asia/Bangkok'
                    ]);
                    $user->setProfile($profile);
                    if ($user->validate() && $user->create()) {
                        $success++;
                    } else {
                        $fail++;
                        $userUnique[] = $data[0];
                        Yii::$app->session->setFlash('error', Json::encode($user->errors)."<br>".Json::encode($userUnique));
                        return $this->refresh();
                        break;
                    }
                }
                if ($fail > 0) {
                    Yii::$app->session->setFlash('error', 'นำเข้าไม่สำเร็จ ' . $fail . ' รายการ');
                    return $this->refresh();
                } else {
                    FileHelper::unlink($filepath);
                    Yii::$app->session->setFlash('success', 'นำเข้าสำเร็จ ' . $success . ' รายการ');
                    return $this->redirect(['/user/admin/index']);
                }
            }
        }
        return $this->render('import-user', [
            'model' => $model
        ]);
    }
}
