<?php

namespace Intcomex\Adyen\Console;

use Adyen\Payment\Model\Cron;
use Exception;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessNotificationCommand extends Command
{
    const COMMAND_NAME = 'adyen:process:notification';
    const COMMAND_DESCRIPTION = 'Adyen process notification';

    /**
     * @var State
     */
    protected $_appState;

    /**
     * @var Cron
     */
    protected $_cron;

    /**
     * @param State $appState
     * @param Cron $cron
     */
    public function __construct(
        State $appState,
        Cron $cron
    ) {
        $this->_appState = $appState;
        $this->_cron = $cron;
        parent::__construct();
    }

    /**
     * Define command attributes such as name, description, arguments.
     */
    protected  function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription(self::COMMAND_DESCRIPTION);
        parent::configure();
    }

    /**
     * Method will run when the command is called via console.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws LocalizedException
     * @throws Exception
     */
    protected  function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->_appState->setAreaCode(Area::AREA_CRONTAB);
        $output->writeln('Starting process notification ' . date('Y-m-d H:i:s'));
        $this->_cron->processNotification();
        $output->writeln('Ending process notification ' . date('Y-m-d H:i:s'));
        return Cli::RETURN_SUCCESS;
    }
}
