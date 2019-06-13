<?php

namespace app\modules\settings\controllers;

use Yii;
use app\modules\v1\models\TblProfileService;
use app\modules\v1\models\TblService;
use app\modules\v1\models\search\TblProfileServiceSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use \yii\web\Response;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * ProfileServiceController implements the CRUD actions for TblProfileService model.
 */
class ProfileServiceController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                    'bulk-delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all TblProfileService models.
     * @return mixed
     */
    public function actionIndex()
    {    
        $searchModel = new TblProfileServiceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * Displays a single TblProfileService model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {   
        $request = Yii::$app->request;
        if($request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                    'title'=> "TblProfileService #".$id,
                    'content'=>$this->renderAjax('view', [
                        'model' => $this->findModel($id),
                    ]),
                    'footer'=> Html::button('Close',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                            Html::a('Edit',['update','id'=>$id],['class'=>'btn btn-primary','role'=>'modal-remote'])
                ];    
        }else{
            return $this->render('view', [
                'model' => $this->findModel($id),
            ]);
        }
    }

    /**
     * Creates a new TblProfileService model.
     * For ajax request will return json object
     * and for non-ajax request if creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $request = Yii::$app->request;
        $model = new TblProfileService();  

        if($request->isAjax){
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            if($request->isGet){
                return [
                    'title'=> "บันทึกรายการ",
                    'content'=>$this->renderAjax('create', [
                        'model' => $model,
                    ]),
                    'footer'=> Html::button('Close',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                                Html::button('Save',['class'=>'btn btn-primary','type'=>"submit"])
        
                ];         
            }else if($model->load($request->post())){
                $data = $request->post('TblProfileService');
                $model->service_id = !empty($data['service_id']) ? Json::encode($data['service_id']) : null;
                $model->examination_id = !empty($data['examination_id']) ? Json::encode($data['examination_id']) : null;
                if($model->save()){
                    return [
                        'forceReload'=>'#crud-datatable-pjax',
                        'title'=> "บันทึกรายการ",
                        'content'=>'<span class="text-success">Create TblProfileService success</span>',
                        'footer'=> Html::button('Close',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                                Html::a('Create More',['create'],['class'=>'btn btn-primary','role'=>'modal-remote'])
            
                    ];  
                } else {
                    return [
                        'title'=> "บันทึกรายการ",
                        'content'=>$this->renderAjax('create', [
                            'model' => $model,
                        ]),
                        'footer'=> Html::button('Close',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                                    Html::button('Save',['class'=>'btn btn-primary','type'=>"submit"])
            
                    ];  
                }       
            }else{           
                return [
                    'title'=> "บันทึกรายการ",
                    'content'=>$this->renderAjax('create', [
                        'model' => $model,
                    ]),
                    'footer'=> Html::button('Close',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                                Html::button('Save',['class'=>'btn btn-primary','type'=>"submit"])
        
                ];         
            }
        }else{
            /*
            *   Process for non-ajax request
            */
            if ($model->load($request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->profile_service_id]);
            } else {
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
        }
       
    }

    /**
     * Updates an existing TblProfileService model.
     * For ajax request will return json object
     * and for non-ajax request if update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $request = Yii::$app->request;
        $model = $this->findModel($id);
        $model->service_id = !empty($model['service_id']) ? Json::decode($model['service_id']) : [];       
        $model->examination_id = !empty($model['examination_id']) ? Json::decode($model['examination_id']) : [];       

        if($request->isAjax){
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            if($request->isGet){
                return [
                    'title'=> "แก้ไขรายการ #".$id,
                    'content'=>$this->renderAjax('update', [
                        'model' => $model,
                    ]),
                    'footer'=> Html::button('Close',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                                Html::button('Save',['class'=>'btn btn-primary','type'=>"submit"])
                ];         
            }else if($model->load($request->post())){
                $data = $request->post('TblProfileService');
                $model->service_id = !empty($data['service_id']) ? Json::encode($data['service_id']) : null;
                $model->examination_id = !empty($data['examination_id']) ? Json::encode($data['examination_id']) : null;
                if($model->save()){
                    return [
                        'forceReload'=>'#crud-datatable-pjax',
                        'title'=> "แก้ไขรายการ #".$id,
                        'content'=>$this->renderAjax('view', [
                            'model' => $model,
                        ]),
                        'footer'=> Html::button('Close',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                                Html::a('Edit',['update','id'=>$id],['class'=>'btn btn-primary','role'=>'modal-remote'])
                    ]; 
                } else {
                    return [
                        'title'=> "แก้ไขรายการ #".$id,
                        'content'=>$this->renderAjax('update', [
                            'model' => $model,
                        ]),
                        'footer'=> Html::button('Close',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                                    Html::button('Save',['class'=>'btn btn-primary','type'=>"submit"])
                    ];  
                }
                   
            }else{
                 return [
                    'title'=> "แก้ไขรายการ #".$id,
                    'content'=>$this->renderAjax('update', [
                        'model' => $model,
                    ]),
                    'footer'=> Html::button('Close',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                                Html::button('Save',['class'=>'btn btn-primary','type'=>"submit"])
                ];        
            }
        }else{
            /*
            *   Process for non-ajax request
            */
            if ($model->load($request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->profile_service_id]);
            } else {
                return $this->render('update', [
                    'model' => $model,
                ]);
            }
        }
    }

    /**
     * Delete an existing TblProfileService model.
     * For ajax request will return json object
     * and for non-ajax request if deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $request = Yii::$app->request;
        $this->findModel($id)->delete();

        if($request->isAjax){
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceClose'=>true,'forceReload'=>'#crud-datatable-pjax'];
        }else{
            /*
            *   Process for non-ajax request
            */
            return $this->redirect(['index']);
        }


    }

     /**
     * Delete multiple existing TblProfileService model.
     * For ajax request will return json object
     * and for non-ajax request if deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionBulkDelete()
    {        
        $request = Yii::$app->request;
        $pks = explode(',', $request->post( 'pks' )); // Array or selected records primary keys
        foreach ( $pks as $pk ) {
            $model = $this->findModel($pk);
            $model->delete();
        }

        if($request->isAjax){
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceClose'=>true,'forceReload'=>'#crud-datatable-pjax'];
        }else{
            /*
            *   Process for non-ajax request
            */
            return $this->redirect(['index']);
        }
       
    }

    /**
     * Finds the TblProfileService model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return TblProfileService the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = TblProfileService::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionSubService($id) {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = [];
        $list = (new \yii\db\Query())
            ->select(['tbl_service.service_id', 'CONCAT(\'(\', tbl_queue_service.queue_service_name ,\') \', tbl_service.service_name) AS service_name'])
            ->from('tbl_service')
            ->innerJoin('tbl_service_group', 'tbl_service_group.service_group_id = tbl_service.service_group_id')
            ->innerJoin('tbl_queue_service', 'tbl_queue_service.queue_service_id = tbl_service_group.queue_service_id')
            ->where([
                'tbl_service.service_status' => 1,
                'tbl_service_group.queue_service_id' => $id
            ])
            ->all();
        $selected  = null;
        if (count($list) > 0) {
            $selected = '';
            foreach ($list as $i => $data) {
                $out[] = ['id' => $data['service_id'], 'text' => $data['service_name']];
            }
            // Shows how you can preselect a value
            return ['output' => $out, 'selected' => ''];
        }
        return ['output' => '', 'selected' => ''];
    }
}
