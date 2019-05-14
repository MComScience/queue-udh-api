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

class SiteController extends Controller
{
    use ModelTrait;

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
        // save log
        $logger->info('Printing', [
            'patient' => $modelPatient
        ]);
        return $this->renderAjax('print', [
            'model' => $model,
            'department' => $model->dept,
            'patient' => $modelPatient
        ]);
    }

    public function actionPrintOneStop($msg, $q)
    {
        $logger = Yii::$app->logger->getLogger();
        $patient = [
            'hn' => '-',
            'fullname' => '-'
        ];
        if ($msg !== 'ไม่พบข้อมูลผู้รับบริการ กรุณากรอกแบบฟอร์มที่ One stop service') {
            $client = new Client(['baseUrl' => 'http://10.188.231.11:8081/api']);
            $response = $client->createRequest()
                ->setMethod('GET')
                ->setUrl('/kiosk/get-pt-profile?q=' . $q)
                ->addHeaders(['content-type' => 'application/json'])
                ->addHeaders(['X-Access-Token' => '6615e94372943853d7dad7a3d847440e'])
                ->send();
            if ($response->isOk) {
                $data = $response->getData();
                if ($data){
                    $patient = $data['data'];
                }
            }
            // save log
            $logger->info('One stop service', [
                'patient' => $patient,
                'msg' => $msg
            ]);
            return $this->renderAjax('print-one-stop', [
                'patient' => $patient,
                'msg' => $msg
            ]);
        } else {
            // save log
            $logger->info('One stop service', [
                'patient' => $patient,
                'msg' => $msg
            ]);
            return $this->renderAjax('print-one-stop', [
                'patient' => $patient,
                'msg' => $msg
            ]);
        }
    }
}
