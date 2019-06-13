<?php

namespace app\modules\v1\models;

use Yii;

/**
 * This is the model class for table "Deptq_d".
 *
 * @property string $toQId
 * @property string $qStatus
 * @property string $fromQId
 * @property string $hn
 * @property string $regNo
 * @property string $deptCode
 * @property string $docCode
 * @property string $qDateIn
 * @property string $qTimeIn
 * @property string $procDate
 * @property string $procTime
 * @property string $qDateOut
 * @property string $qTimeOut
 * @property string $deptRef
 * @property string $cardType
 * @property int $cardNo
 * @property string $contCode
 * @property string $labQ
 * @property string $labDateOut
 * @property string $labTimeOut
 * @property string $radQ
 * @property string $radDateOut
 * @property string $radTimeOut
 * @property string $medQ
 * @property string $medDateOut
 * @property string $medTimeOut
 * @property string $finQ
 * @property string $finDateOut
 * @property string $finTimeOut
 * @property string $hasApp
 * @property string $referFlag
 * @property string $labWaitStat
 * @property string $radWaitStat
 * @property string $radConfirm
 * @property string $labConfirm
 * @property string $rxPrintCnt
 * @property string $newPat
 * @property string $visitType
 * @property int $lastDetailNo
 * @property string $lastUpd
 * @property string $toQNo
 * @property string $fromQNo
 * @property string $labQNo
 * @property string $radQNo
 * @property string $medQNo
 * @property string $finQNo
 * @property string $rxNo
 * @property string $medRecv
 * @property string $completeType
 * @property string $transferReason
 * @property string $transferHosp
 * @property string $maker
 * @property string $rxTimeIn
 * @property string $rxTimeOut
 * @property string $medCheckIn
 * @property string $medCheckOut
 * @property string $medMakeOut
 * @property string $medApproveOut
 * @property string $procOutDate
 * @property string $procOutTime
 * @property string $medFirstKeyIn
 * @property string $cancelRxMaker
 * @property string $cancelRxTime
 * @property string $registerType
 * @property string $NotQId
 * @property string $procOutType
 * @property string $cardindate
 * @property string $cardintime
 * @property string $cardinusr
 * @property string $key_df
 * @property string $key_med
 * @property string $key_diag
 * @property string $key_lab
 * @property string $key_rad
 * @property string $ladmit_n
 * @property string $consultnote
 * @property string $medRecvTime
 * @property string $medRecvDate
 * @property string $cancelRxTime1
 * @property string $hideselect
 * @property string $prevQId
 * @property string $prevQNo
 * @property string $refer
 * @property string $status
 * @property string $OPDTime
 * @property string $OPDDate
 * @property string $myrx
 * @property string $diagTime
 * @property string $deptdesc1
 * @property string $printCard
 * @property string $admitStatus
 * @property string $docStatus
 * @property string $docDateOut
 * @property string $docTimeOut
 * @property string $printCardDate
 * @property string $printCardTime
 * @property string $medStatus
 * @property string $medSite
 * @property string $medUpdate
 * @property string $printOrder
 */
class DeptqD extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Deptq_d';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_mssql');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['toQId', 'qStatus', 'fromQId', 'hn', 'regNo', 'deptCode', 'docCode', 'qDateIn', 'qTimeIn', 'procDate', 'procTime', 'qDateOut', 'qTimeOut', 'deptRef', 'cardType', 'contCode', 'labQ', 'labDateOut', 'labTimeOut', 'radQ', 'radDateOut', 'radTimeOut', 'medQ', 'medDateOut', 'medTimeOut', 'finQ', 'finDateOut', 'finTimeOut', 'hasApp', 'referFlag', 'labWaitStat', 'radWaitStat', 'radConfirm', 'labConfirm', 'rxPrintCnt', 'newPat', 'visitType', 'toQNo', 'fromQNo', 'labQNo', 'radQNo', 'medQNo', 'finQNo', 'rxNo', 'medRecv', 'completeType', 'transferReason', 'transferHosp', 'maker', 'rxTimeIn', 'rxTimeOut', 'procOutDate', 'procOutTime', 'cancelRxMaker', 'cancelRxTime', 'registerType', 'NotQId', 'procOutType', 'cardindate', 'cardintime', 'cardinusr', 'key_df', 'key_med', 'key_diag', 'key_lab', 'key_rad', 'ladmit_n', 'consultnote', 'medRecvTime', 'medRecvDate', 'hideselect', 'prevQId', 'prevQNo', 'refer', 'status', 'OPDTime', 'OPDDate', 'myrx', 'diagTime', 'deptdesc1', 'printCard', 'admitStatus', 'docStatus', 'docDateOut', 'docTimeOut', 'printCardDate', 'printCardTime', 'medStatus', 'medSite', 'printOrder'], 'string'],
            [['hn'], 'required'],
            ['rxNo', 'unique'],
            [['cardNo', 'lastDetailNo'], 'integer'],
            [['lastUpd', 'medCheckIn', 'medCheckOut', 'medMakeOut', 'medApproveOut', 'medFirstKeyIn', 'cancelRxTime1', 'medUpdate'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'toQId' => 'To Q ID',
            'qStatus' => 'Q Status',
            'fromQId' => 'From Q ID',
            'hn' => 'Hn',
            'regNo' => 'Reg No',
            'deptCode' => 'Dept Code',
            'docCode' => 'Doc Code',
            'qDateIn' => 'Q Date In',
            'qTimeIn' => 'Q Time In',
            'procDate' => 'Proc Date',
            'procTime' => 'Proc Time',
            'qDateOut' => 'Q Date Out',
            'qTimeOut' => 'Q Time Out',
            'deptRef' => 'Dept Ref',
            'cardType' => 'Card Type',
            'cardNo' => 'Card No',
            'contCode' => 'Cont Code',
            'labQ' => 'Lab Q',
            'labDateOut' => 'Lab Date Out',
            'labTimeOut' => 'Lab Time Out',
            'radQ' => 'Rad Q',
            'radDateOut' => 'Rad Date Out',
            'radTimeOut' => 'Rad Time Out',
            'medQ' => 'Med Q',
            'medDateOut' => 'Med Date Out',
            'medTimeOut' => 'Med Time Out',
            'finQ' => 'Fin Q',
            'finDateOut' => 'Fin Date Out',
            'finTimeOut' => 'Fin Time Out',
            'hasApp' => 'Has App',
            'referFlag' => 'Refer Flag',
            'labWaitStat' => 'Lab Wait Stat',
            'radWaitStat' => 'Rad Wait Stat',
            'radConfirm' => 'Rad Confirm',
            'labConfirm' => 'Lab Confirm',
            'rxPrintCnt' => 'Rx Print Cnt',
            'newPat' => 'New Pat',
            'visitType' => 'Visit Type',
            'lastDetailNo' => 'Last Detail No',
            'lastUpd' => 'Last Upd',
            'toQNo' => 'To Q No',
            'fromQNo' => 'From Q No',
            'labQNo' => 'Lab Q No',
            'radQNo' => 'Rad Q No',
            'medQNo' => 'Med Q No',
            'finQNo' => 'Fin Q No',
            'rxNo' => 'Rx No',
            'medRecv' => 'Med Recv',
            'completeType' => 'Complete Type',
            'transferReason' => 'Transfer Reason',
            'transferHosp' => 'Transfer Hosp',
            'maker' => 'Maker',
            'rxTimeIn' => 'Rx Time In',
            'rxTimeOut' => 'Rx Time Out',
            'medCheckIn' => 'Med Check In',
            'medCheckOut' => 'Med Check Out',
            'medMakeOut' => 'Med Make Out',
            'medApproveOut' => 'Med Approve Out',
            'procOutDate' => 'Proc Out Date',
            'procOutTime' => 'Proc Out Time',
            'medFirstKeyIn' => 'Med First Key In',
            'cancelRxMaker' => 'Cancel Rx Maker',
            'cancelRxTime' => 'Cancel Rx Time',
            'registerType' => 'Register Type',
            'NotQId' => 'Not Q ID',
            'procOutType' => 'Proc Out Type',
            'cardindate' => 'Cardindate',
            'cardintime' => 'Cardintime',
            'cardinusr' => 'Cardinusr',
            'key_df' => 'Key Df',
            'key_med' => 'Key Med',
            'key_diag' => 'Key Diag',
            'key_lab' => 'Key Lab',
            'key_rad' => 'Key Rad',
            'ladmit_n' => 'Ladmit N',
            'consultnote' => 'Consultnote',
            'medRecvTime' => 'Med Recv Time',
            'medRecvDate' => 'Med Recv Date',
            'cancelRxTime1' => 'Cancel Rx Time1',
            'hideselect' => 'Hideselect',
            'prevQId' => 'Prev Q ID',
            'prevQNo' => 'Prev Q No',
            'refer' => 'Refer',
            'status' => 'Status',
            'OPDTime' => 'Opd Time',
            'OPDDate' => 'Opd Date',
            'myrx' => 'Myrx',
            'diagTime' => 'Diag Time',
            'deptdesc1' => 'Deptdesc1',
            'printCard' => 'Print Card',
            'admitStatus' => 'Admit Status',
            'docStatus' => 'Doc Status',
            'docDateOut' => 'Doc Date Out',
            'docTimeOut' => 'Doc Time Out',
            'printCardDate' => 'Print Card Date',
            'printCardTime' => 'Print Card Time',
            'medStatus' => 'Med Status',
            'medSite' => 'Med Site',
            'medUpdate' => 'Med Update',
            'printOrder' => 'Print Order',
        ];
    }

    /**
     * {@inheritdoc}
     * @return DeptqDQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new DeptqDQuery(get_called_class());
    }
}
