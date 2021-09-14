<?php


namespace Ef\SmsGateway\Console;
date_default_timezone_set('Asia/Colombo');

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use DateInterval;
use DatePeriod;
use DateTime;

use Ef\SmsGateway\Model\EfSmsMessageFactory;
use Ef\SmsGateway\DialogSms\DialogSms;

class SendSms extends Command
{

	/**
	 * @var Ef\Logs\Helper\Logadd $logHandler
	 */
	protected $logHandler;

	public function __construct(\Ef\Logs\Helper\Logadd $_lonHandle) 
	{
		$this->logHandler = $_lonHandle;
		parent::__construct();
	}

	protected function configure()
    {
        $this->setName('ef:sms:send');
        $this->setDescription('Execute SMS queue. Send next available sms list.');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {

            $begin = new DateTime();
            $this->logHandler->add_info('Send SMS - CLI command ', [  "Start time" => $begin ]);
            $output->writeln("Sending SMS - Starting operation.");

            $dialogSms = new DialogSms();
            $dialogSms->send_sms();
            
            $output->writeln( "Sending SMS - Operation completed.");

        }catch (\Exception $exception){

            $this->logHandler->add_error('Send SMS - CLI command', [ "error" => $exception->getMessage() , "Time" => new DateTime() ]);
            $output->writeln( $exception->getMessage());
        }
    }
}