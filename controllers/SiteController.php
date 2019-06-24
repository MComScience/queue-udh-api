<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use yii\helpers\Json;
use app\modules\v1\traits\ModelTrait;
use yii\httpclient\Client;
use app\modules\v1\models\TblQueue;
use app\modules\v1\models\TblQueueFailed;
use app\modules\v1\models\TblService;
use yii\web\NotFoundHttpException;
use yii\helpers\ArrayHelper;

class SiteController extends Controller
{
    use ModelTrait;

    const MSG_HN_DUPLICATE = 'พบข้อมูลผู้รับบริการมากกว่า 1 HN กรุณาติดต่อห้องบัตร';
    const MSG_NO_DATA = 'ไม่พบข้อมูลผู้รับบริการ กรุณากรอกแบบฟอร์มที่ห้องบัตร';

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            /* 'error' => [
                'class' => 'yii\web\ErrorAction',
            ], */
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
            'glide' => 'trntv\glide\actions\GlideAction'
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        //return Json::encode($this->yearsMonthsBetween('2015-01-02', '2019-05-05'));
        return $this->render('index');
    }

    private function yearsMonthsBetween($start_date, $end_date)
    {
        $d1 = new \DateTime( $start_date );
        $d2 = new \DateTime( $end_date );

        $diff = $d2->diff( $d1 );
        return [
            'years' => $diff->y,
            'months' => $diff->m,
        ];
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionError()
    {
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        $exception = Yii::$app->errorHandler->exception;
        $response = new Response();
        $response->statusCode = 400;
        if ($exception !== null) {
            return $response->data = Json::encode([
                'name' => $exception->getName(),
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'status' => 404,
                'type' => 'yii\\web\\NotFoundHttpException'
            ]);
        }
        $response->data = [
            'name' => 'Bad Request',
            'message' => Yii::t('app', 'The system could not process your request. Please check and try again.'),
            'code' => 0,
            'status' => 400,
            'type' => 'yii\\web\\BadRequestHttpException'
        ];

        return $response;
    }

    public function actionClearCache()
    {
        $assetPath = \Yii::getAlias('@app') . '/web/assets/';

        $this->recursiveDelete($assetPath);

        if (\Yii::$app->cache->flush()) {
            \Yii::$app->session->setFlash('success', 'Cache has been flushed.');
            return 'Cache has been flushed.';
        } else {
            \Yii::$app->session->setFlash('error', 'Failed to flush cache.');
            return 'Failed to flush cache.';
        }
    }

    public static function recursiveDelete($path)
    {
        if (is_file($path)) {
            return @unlink($path);
        } elseif (is_dir($path)) {
            $scan = glob(rtrim($path, '/') . '/*');
            foreach ($scan as $index => $newPath) {
                self::recursiveDelete($newPath);
            }
            return @rmdir($path);
        }
    }

    public function actionPrint($id)
    {
        $logger = Yii::$app->logger->getLogger();
        $model = $this->findModelQueue($id);
        $modelPatient = $this->findModelPatient($model['patient_id']);
        $service = $this->findModelService($model['service_id']);
        $modelCard = $this->findModelCard($service['card_id']);
        $service_code = !empty($service['service_code']) ? $service['service_code'] : '-';
        
        $doc_name = '-';
        if(!empty($modelPatient['appoint']) && $model['appoint'] == '1') {
            $appoints = Json::decode($modelPatient['appoint']);
            if(is_array($appoints)) {
                $mapAppoint = ArrayHelper::map($appoints, 'dept_code', 'doc_name');
                $doc_name = ArrayHelper::getValue($mapAppoint, $service_code, '-');
            }
        }

        $card_template = strtr($modelCard['card_template'],[
            '{hn}' => $modelPatient['hn'],
            '{vn}' => $modelPatient['vn'],
            '{cid}' => $modelPatient['cid'],
            '{number}' => $model['queue_no'], // เลขคิว
            '{dept_name}' => $service['service_name'].' ('.$service_code.')', // แผนก
            '{message_right}' => $modelPatient['maininscl_name'], // ชื่อสิทธิ
            '{fullname}' => $modelPatient['fullname']. ' (<i style="font-size: 13px;">'.$model->getCasePatientName().'</i>)', // ชื่อผู้ป่วย + กรณีผู้ป่วย
            '{date}' => Yii::$app->formatter->asDate('now', 'php: d M ') . (Yii::$app->formatter->asDate('now', 'php:Y') + 543),
            '{time}' => Yii::$app->formatter->asDate('now', 'php: H:i น.'),
            '{appoint}' => $model->getAppointName(),
            '{qtype}' => $model->getCasePatientName(),
            '{priority}' => $model->getPriorityName(),
            '{doc_name}' => $doc_name
        ]);
        $i = !empty($service['print_copy_qty']) ? $service['print_copy_qty'] : 1; // จำนวน copy
        $template = '';
        for($x = 0; $x < $i; $x++) {
            $template .= strtr($card_template,[
                '{qrcode}' => '<div id="qrcode'.$x.'" class="qrcode" style="text-align: right;padding-right: 20px;"></div>',
                '{barcode}' => '<img id="barcode'.$x.'"/>',
            ]);
        }
        // save log
        $logger->info('Printing', [
            'patient' => Json::encode($modelPatient),
            'queue' => Json::encode($model)
        ]);
        return $this->renderAjax('print', [
            'model' => $model,
            'service' => $service,
            'patient' => $modelPatient,
            'template' => $template,
            'i' => $i
        ]);
    }

    public function actionPrintOneStop($msg, $q)
    {
        $logger = Yii::$app->logger->getLogger();
        $model = new TblQueueFailed();
        $patient = [
            'hn' => '-',
            'fullname' => '-'
        ];
        if ($msg !== self::MSG_NO_DATA) {
            $client = new Client(['baseUrl' => Yii::$app->params['API_BASE_URL']]);
            $url = '/kiosk/get-pt-profile?q=' . $q;
            if($msg == self::MSG_HN_DUPLICATE){
                $url = '/kiosk/get-pt-profile?q=' . $q. '&action=get_dup_detail';
            }
            $response = $client->createRequest()
                ->setMethod('GET')
                ->setUrl($url)
                ->addHeaders(['content-type' => 'application/json'])
                ->addHeaders(['X-Access-Token' => Yii::$app->params['API_TOKEN']])
                ->send();
            if ($response->isOk) {
                $data = $response->getData();
                if ($data){
                    $patient = $data['data'];
                }
            }
            // save log
            $logger->info('ติดต่อห้องบัตร', [
                'msg' => $msg,
                'patient' => $patient,
            ]);
            $model->queue_failed_message = $msg;
            $model->hn = is_array($patient['hn']) && isset($patient['hn']) ? Json::encode($patient['hn']) : $patient['hn'];
            $model->fullname = isset($patient['fullname']) ? $patient['fullname'] : '';
            $model->created_at = Yii::$app->formatter->asDate('now', 'php:Y-m-d H:i:s');
            $model->save(false);
            return $this->renderAjax('print-one-stop', [
                'patient' => $patient,
                'msg' => $msg
            ]);
        } else {
            $model->queue_failed_message = $msg;
            $model->hn = is_array($patient['hn']) && isset($patient['hn']) ? Json::encode($patient['hn']) : $patient['hn'];
            $model->fullname = isset($patient['fullname']) ? $patient['fullname'] : '';
            $model->created_at = Yii::$app->formatter->asDate('now', 'php:Y-m-d H:i:s');
            $model->save(false);
            // save log
            $logger->info('ติดต่อห้องบัตร', [
                'msg' => $msg,
                'patient' => $patient,
            ]);
            return $this->renderAjax('print-one-stop', [
                'patient' => $patient,
                'msg' => $msg
            ]);
        }
    }

    public function actionImportData()
    {
        $rows = Yii::$app->db3->createCommand('SELECT * FROM tbl_queue WHERE tbl_queue.queue_id > 57816 ORDER BY tbl_queue.queue_id ASC LIMIT 2000')->queryAll();
        $count = 0;
        $no_service = [];
        $last_id = 0;
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {
            foreach ($rows as $key => $row) {
                $service = TblService::findOne(['service_code' => $row['dept_id']]);
                if(!$service) {
                    $no_service[] = [
                        'dept_id' => $row['dept_id'],
                        'index' => $key
                    ];
                    // throw new NotFoundHttpException('no data service. '.$row['dept_id']. ' #key '. $key);
                } else {
                    $db->createCommand()->insert('tbl_queue', [
                        'queue_id' => $row['queue_id'],
                        'queue_no' => $row['queue_no'],
                        'patient_id' => $row['patient_id'],
                        'service_group_id' => $service['service_group_id'],
                        'service_id' => $service['service_id'],
                        'priority_id' => $row['priority_id'],
                        'queue_station' => $row['queue_station'],
                        'case_patient' => $row['case_patient'],
                        'queue_status_id' => $row['queue_status_id'],
                        'appoint' => 0,
                        'parent_id' => '',
                        'created_at' => $row['created_at'],
                        'updated_at' => $row['updated_at'],
                        'created_by' => $row['created_by'],
                        'updated_by' => $row['updated_by'],
                    ])->execute();
                    $count++;
                    $last_id = $row['queue_id'];
                }
            }
            
            $transaction->commit();

            return Json::encode([
                'success' => $count,
                'no_service' => $no_service,
                'last_id' => $last_id
            ]);
        } catch(\Exception $e) {
            $transaction->rollBack();
            throw $e;
        } catch(\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public function actionImportData2()
    {
        $rows = Yii::$app->db3->createCommand('SELECT * FROM tbl_patient WHERE tbl_patient.patient_id > 57847 ORDER BY tbl_patient.patient_id ASC LIMIT 5000')->queryAll();
        $count = 0;
        $last_id = 0;
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {
            foreach ($rows as $key => $row) {
                $db->createCommand()->insert('tbl_patient', [
                    'patient_id' => $row['patient_id'],
                    'hn' => $row['hn'],
                    'vn' => '',
                    'cid' => $row['cid'],
                    'title' => $row['title'],
                    'firstname' => $row['firstname'],
                    'lastname' => $row['lastname'],
                    'fullname' => $row['fullname'],
                    'birth_date' => $row['birth_date'],
                    'age' => $row['age'],
                    'blood_group' => $row['blood_group'],
                    'nation' => $row['nation'],
                    'address' => $row['address'],
                    'occ' => $row['occ'],
                    'appoint' => $row['appoint'],
                    'maininscl_name' => $row['maininscl_name'],
                    'subinscl_name' => $row['subinscl_name'],
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at'],
                    'created_by' => $row['created_by'],
                    'updated_by' => $row['updated_by'],
                ])->execute();
                $count++;
                $last_id = $row['patient_id'];
            }
            
            $transaction->commit();

            return Json::encode([
                'success' => $count,
                'last_id' => $last_id
            ]);
        } catch(\Exception $e) {
            $transaction->rollBack();
            throw $e;
        } catch(\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}
