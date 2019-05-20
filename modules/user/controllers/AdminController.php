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
use kartik\mpdf\Pdf;
use kartik\grid\Module;
use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;
use kartik\base\Config;
use kartik\grid\GridView;
use yii\web\Response;

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

    /**
     * Download the exported file
     *
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionExport()
    {
        /**
         * @var Module $module
         */
        $request = Yii::$app->request;
        $moduleId = $request->post('module_id', Module::MODULE);
        $module = Config::getModule($moduleId, Module::class);
        $type = $request->post('export_filetype', 'html');
        $name = $request->post('export_filename', Yii::t('kvgrid', 'export'));
        $content = $request->post('export_content', Yii::t('kvgrid', 'No data found'));
        $mime = $request->post('export_mime', 'text/plain');
        $encoding = $request->post('export_encoding', 'utf-8');
        $bom = $request->post('export_bom', 1);
        $hashConfig = $request->post('hash_export_config', 0);
        $hashConfig = empty($hashConfig) ? 0 : 1;
        $config = $request->post('export_config', '{}');
        $cfg = empty($hashConfig) ? '' : $config;
        $oldHash = $request->post('export_hash');
        $newData = $moduleId . $name . $mime . $encoding . $bom . $hashConfig . $cfg;
        $security = Yii::$app->security;
        $salt = $module->exportEncryptSalt;
        $newHash = $security->hashData($newData, $salt);
        if (!$security->validateData($oldHash, $salt) || $oldHash !== $newHash) {
            $params = "\nOld Hash:{$oldHash}\nNew Hash:{$newHash}\n";
            throw new InvalidCallException("The parameters for yii2-grid export seem to be tampered. Please retry!{$params}");
        }
        if ($type == GridView::PDF) {
            $config = Json::decode($config);
            return $this->generatePDF($content, "{$name}.pdf", $config);
        } elseif ($type == GridView::CSV || $type == GridView::TEXT) {
            if ($encoding != 'utf-8') {
                $content = mb_convert_encoding($content, $encoding, 'utf-8');
            } elseif ($bom) {
                $content = chr(239) . chr(187) . chr(191) . $content; // add BOM
            }
        }
        $this->setHttpHeaders($type, $name, $mime, $encoding);
        return $content;
    }

    /**
     * Generates the PDF file
     *
     * @param string $content the file content
     * @param string $filename the file name
     * @param array $config the configuration for yii2-mpdf component
     *
     * @return Response
     * @throws InvalidConfigException
     */
    protected function generatePDF($content, $filename, $config = [])
    {
        unset($config['contentBefore'], $config['contentAfter']);
        $config['filename'] = $filename;
        $config['methods']['SetAuthor'] = [Yii::t('kvgrid', 'Krajee Solutions')];
        $config['methods']['SetCreator'] = [Yii::t('kvgrid', 'Krajee Yii2 Grid Export Extension')];
        $config['content'] = $content;
        $pdf = new Pdf($config);
        return $pdf->render();
    }

    /**
     * Sets the HTTP headers needed by file download action.
     *
     * @param string $type the file type
     * @param string $name the file name
     * @param string $mime the mime time for the file
     * @param string $encoding the encoding for the file content
     */
    protected function setHttpHeaders($type, $name, $mime, $encoding = 'utf-8')
    {
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_RAW;
        $headers = $response->getHeaders();
        $headers->set('Content-Type', "{$mime}; charset={$encoding}");
        $headers->set('Content-Transfer-Encoding', $encoding);
        $headers->set('Cache-Control', 'public, must-revalidate, max-age=0');
        $headers->set('Pragma', 'public');
        $headers->set('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT');
        $headers->set('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT');
        $headers->set('Content-Disposition', "attachment; filename={$name}.{$type}");
    }
}
