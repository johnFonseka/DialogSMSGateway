<?php 

// This file to to check SMS gateway functionalities only.

// DELETE ON PRODUCTION !!!


namespace Ef\SmsGateway\Controller\Index;

use Ef\SmsGateway\Model\EfSmsMessageFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Context;

use Ef\SmsGateway\DialogSms\DialogSms;

class Index extends \Magento\Framework\App\Action\Action {

    protected $_sampleSms;
    protected $resultRedirect;

    protected $dialogSms;
    
    public function __construct(\Magento\Framework\App\Action\Context $context,
        \Ef\SmsGateway\Model\EfSmsMessageFactory  $_sampleSms,
    \Magento\Framework\Controller\ResultFactory $result) {
        parent::__construct($context);
        $this->_sampleSms = $_sampleSms;
        $this->resultRedirect = $result;
    }

    public function execute() {
        $resultRedirect = $this->resultRedirect->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        // Recording SMS to send
        $model = $this->_sampleSms->create();
        $saveData = $model->addSms('36', "Dialog sending API - ".date('Y-m-d H:i:s'));
        if($saveData){
            $this->messageManager->addSuccess( __('Insert Record Successfully !') );
        }

        // Arbitrary calling SEND SMS function to check API and DB functions.
        echo "Calling from Dialog SMS <br />";
        $dialogSms = new DialogSms($this->_sampleSms);
        echo "<pre>";
        var_dump($dialogSms->send_sms()); // This is the magic line
        echo "</pre>";
        exit;

        return $this->_pageFactory->create();
        return $resultRedirect;
    }
}

?>