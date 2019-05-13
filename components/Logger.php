<?php

/**
 * Created by PhpStorm.
 * User: Tanakorn Phompak
 * Date: 13/5/2562
 * Time: 14:05
 */
namespace app\components;

use Yii;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\UidProcessor;
use yii\base\Component;
use Monolog\Logger as BaseLogger;

class Logger extends Component
{
    const LOG_NAME = 'queue-app';

    public $log_name = self::LOG_NAME;

    private $_logger;

    /**
     * @return mixed
     */
    public function getLogger()
    {
        return $this->_logger;
    }

    /**
     * @param mixed $logger
     */
    public function setLogger($logger)
    {
        $this->_logger = $logger;
    }

    public function init()
    {
        parent::init();
        $logger = new BaseLogger($this->log_name);
        $logger->pushProcessor(new UidProcessor());
        $logger->pushHandler(new StreamHandler(Yii::getAlias('@app/runtime').'/logs/'.$this->log_name.'.log', BaseLogger::DEBUG));
        $this->setLogger($logger);
    }
}