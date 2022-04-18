<?php

namespace Intcomex\Adyen\Console;

use Adyen\Payment\Cron\ServerIpAddress;
use Exception;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddressCachingCommand extends Command
{
    const COMMAND_NAME = 'adyen:address:caching';
    const COMMAND_DESCRIPTION = 'Adyen address caching';

    /**
     * @var State
     */
    protected $_appState;

    /**
     * @var ServerIpAddress
     */
    protected $_serverIpAddress;

    /**
     * @param State $appState
     * @param ServerIpAddress $serverIpAddress
     */
    public function __construct(
        State $appState,
        ServerIpAddress $serverIpAddress
    ) {
        $this->_appState = $appState;
        $this->_serverIpAddress = $serverIpAddress;
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
        $output->writeln('Starting address caching ' . date('Y-m-d H:i:s'));
        $this->_serverIpAddress->execute();
        $output->writeln('Ending address caching ' . date('Y-m-d H:i:s'));
        return Cli::RETURN_SUCCESS;
    }
}
