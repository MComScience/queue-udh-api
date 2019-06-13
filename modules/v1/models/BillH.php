<?php

namespace app\modules\v1\models;

use Yii;

/**
 * This is the model class for table "Bill_h".
 *
 * @property string $hn
 * @property string $regNo
 * @property string $lastDetail
 * @property string $totalAmt
 * @property string $totalAmtPaid
 * @property string $cashierCode
 * @property string $actualDisc
 * @property string $beginAR
 * @property string $brokerCode
 * @property string $nonDiscAmt
 * @property string $statementNo
 * @property string $tmpRecpNo
 * @property string $contCode
 * @property string $payNo
 * @property string $addupAmt
 * @property string $curentAR
 * @property string $recpFlag
 * @property string $invNo
 * @property string $drugRun
 * @property string $prnBillSumFlag
 * @property string $prnPayFlag
 * @property string $curDebt_old
 * @property string $firstChgRec
 * @property string $lastChgRec
 * @property string $lastCashAmt
 * @property string $lastARAmt
 * @property string $curActualDisc
 * @property string $curAddupAmt
 * @property string $reprnRecpFlag
 * @property string $recpNo
 * @property string $recpDate
 * @property string $recpStatusFlag
 * @property string $statStatusFlag
 * @property string $tmpRecpStatus
 * @property string $noPayFlag
 * @property string $lastUpd
 * @property string $useDrg
 * @property string $REFERIN
 * @property string $REFEROUT
 * @property string $HMAIN
 * @property string $HSUB
 * @property string $TFReasonIn
 * @property string $TFReasonOut
 * @property string $rigthDate
 * @property string $paid_class
 * @property string $LastStateDate
 * @property string $NextStateDate
 * @property int $paymVersion
 * @property string $depositAmt
 * @property string $right_date
 * @property string $final_date
 * @property string $SocialID
 * @property string $visit_insystem
 * @property string $overDiscAmt
 * @property int $paidCnt
 * @property string $lastEditRight
 * @property string $lastDebt
 * @property string $curDebt1
 * @property string $rigthTime
 * @property string $RecpType
 * @property string $curDebt
 * @property string $PCUFlag
 * @property string $regist_date
 * @property string $CSCDSend
 * @property string $CSCDSendDate
 */
class BillH extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Bill_h';
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
            [['hn', 'regNo', 'lastDetail', 'totalAmt', 'totalAmtPaid', 'cashierCode', 'actualDisc', 'beginAR', 'brokerCode', 'nonDiscAmt', 'statementNo', 'tmpRecpNo', 'contCode', 'payNo', 'addupAmt', 'curentAR', 'recpFlag', 'invNo', 'drugRun', 'prnBillSumFlag', 'prnPayFlag', 'curDebt_old', 'firstChgRec', 'lastChgRec', 'lastCashAmt', 'lastARAmt', 'curActualDisc', 'curAddupAmt', 'reprnRecpFlag', 'recpNo', 'recpDate', 'recpStatusFlag', 'statStatusFlag', 'tmpRecpStatus', 'noPayFlag', 'useDrg', 'REFERIN', 'REFEROUT', 'HMAIN', 'HSUB', 'TFReasonIn', 'TFReasonOut', 'rigthDate', 'paid_class', 'LastStateDate', 'NextStateDate', 'right_date', 'final_date', 'SocialID', 'visit_insystem', 'lastEditRight', 'curDebt1', 'rigthTime', 'RecpType', 'PCUFlag', 'regist_date', 'CSCDSend', 'CSCDSendDate'], 'string'],
            [['lastUpd'], 'safe'],
            [['paymVersion', 'paidCnt'], 'integer'],
            [['depositAmt', 'overDiscAmt', 'lastDebt', 'curDebt'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'hn' => 'Hn',
            'regNo' => 'Reg No',
            'lastDetail' => 'Last Detail',
            'totalAmt' => 'Total Amt',
            'totalAmtPaid' => 'Total Amt Paid',
            'cashierCode' => 'Cashier Code',
            'actualDisc' => 'Actual Disc',
            'beginAR' => 'Begin Ar',
            'brokerCode' => 'Broker Code',
            'nonDiscAmt' => 'Non Disc Amt',
            'statementNo' => 'Statement No',
            'tmpRecpNo' => 'Tmp Recp No',
            'contCode' => 'Cont Code',
            'payNo' => 'Pay No',
            'addupAmt' => 'Addup Amt',
            'curentAR' => 'Curent Ar',
            'recpFlag' => 'Recp Flag',
            'invNo' => 'Inv No',
            'drugRun' => 'Drug Run',
            'prnBillSumFlag' => 'Prn Bill Sum Flag',
            'prnPayFlag' => 'Prn Pay Flag',
            'curDebt_old' => 'Cur Debt Old',
            'firstChgRec' => 'First Chg Rec',
            'lastChgRec' => 'Last Chg Rec',
            'lastCashAmt' => 'Last Cash Amt',
            'lastARAmt' => 'Last Ar Amt',
            'curActualDisc' => 'Cur Actual Disc',
            'curAddupAmt' => 'Cur Addup Amt',
            'reprnRecpFlag' => 'Reprn Recp Flag',
            'recpNo' => 'Recp No',
            'recpDate' => 'Recp Date',
            'recpStatusFlag' => 'Recp Status Flag',
            'statStatusFlag' => 'Stat Status Flag',
            'tmpRecpStatus' => 'Tmp Recp Status',
            'noPayFlag' => 'No Pay Flag',
            'lastUpd' => 'Last Upd',
            'useDrg' => 'Use Drg',
            'REFERIN' => 'Referin',
            'REFEROUT' => 'Referout',
            'HMAIN' => 'Hmain',
            'HSUB' => 'Hsub',
            'TFReasonIn' => 'Tf Reason In',
            'TFReasonOut' => 'Tf Reason Out',
            'rigthDate' => 'Rigth Date',
            'paid_class' => 'Paid Class',
            'LastStateDate' => 'Last State Date',
            'NextStateDate' => 'Next State Date',
            'paymVersion' => 'Paym Version',
            'depositAmt' => 'Deposit Amt',
            'right_date' => 'Right Date',
            'final_date' => 'Final Date',
            'SocialID' => 'Social ID',
            'visit_insystem' => 'Visit Insystem',
            'overDiscAmt' => 'Over Disc Amt',
            'paidCnt' => 'Paid Cnt',
            'lastEditRight' => 'Last Edit Right',
            'lastDebt' => 'Last Debt',
            'curDebt1' => 'Cur Debt1',
            'rigthTime' => 'Rigth Time',
            'RecpType' => 'Recp Type',
            'curDebt' => 'Cur Debt',
            'PCUFlag' => 'Pcu Flag',
            'regist_date' => 'Regist Date',
            'CSCDSend' => 'Cscd Send',
            'CSCDSendDate' => 'Cscd Send Date',
        ];
    }

    /**
     * {@inheritdoc}
     * @return BillHQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new BillHQuery(get_called_class());
    }
}
