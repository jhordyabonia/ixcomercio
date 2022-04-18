<?php

namespace Intcomex\Clearsale\Console;

use Clearsale\Integration\Observer\ClearsaleObserver;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateOrdersCommand extends Command
{
    const COMMAND_NAME = 'clearsale:orders:update';
    const COMMAND_DESCRIPTION = 'Update order status';

    /**
     * @var State
     */
    protected $_appState;

    /**
     * @var ClearsaleObserver
     */
    protected $_clearsaleObserver;

    /**
     * @param State $appState
     * @param ClearsaleObserver $clearsaleObserver
     */
    public function __construct(
        State $appState,
        ClearsaleObserver $clearsaleObserver
    ) {
        $this->_appState = $appState;
        $this->_clearsaleObserver = $clearsaleObserver;
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
     */
    protected  function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->_appState->setAreaCode(Area::AREA_CRONTAB);
        $output->writeln('Starting update orders Clearsale ' . date('Y-m-d H:i:s'));
        $this->_clearsaleObserver->getClearsaleOrderStatus();
        $output->writeln('Ending update orders Clearsale ' . date('Y-m-d H:i:s'));
        return Cli::RETURN_SUCCESS;
    }
}
