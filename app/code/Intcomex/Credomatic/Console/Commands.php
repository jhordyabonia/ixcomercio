<?php
namespace Intcomex\Credomatic\Console;
 
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Console\Cli;
use Intcomex\Credomatic\Cron\OrderStatus;
use \Magento\Framework\App\State;

/**
* Class Commands
*/
class Commands extends Command 
{
   
    const NAME = 'name';

     /**
    * @var Intcomex\Credomatic\Cron\OrderStatus
    */
    private $_orderStatus;

    /**
    * @var \Magento\Framework\App\State
    */
    protected $_appState;

   public function __construct(OrderStatus $orderStatus,State $appState)
   {
        $this->_orderStatus = $orderStatus;
        $this->_appState = $appState; 


        parent::__construct();    
   }

   /**
   * Define command attributes such as name, description, arguments
   */
   protected  function configure()
   {
       $options = [
            new InputOption(
                self::NAME,
                null,
                InputOption::VALUE_REQUIRED,
                'Name'
            )
       ];
       $this->setName('credomatic:transaction');
       $this->setDescription('Check Transacctions Credomatic');
       $this->setDefinition($options);       
       parent::configure();
   }

   /**
    * method will run when the command is called via console
    * @param InputInterface $input
    * @param OutputInterface $output
    * @return int|void|null
    */
   protected  function execute(InputInterface $input, OutputInterface $output)
   {
        $this->_appState->setAreaCode('adminhtml');

        $name = $input->getOption(self::NAME);

        $output->writeln("Starting Process " . $name . " " .  date('Y-m-d H:i:s'));

        switch($name){
            case 'orderStatus':
                $this->_orderStatus->execute();
            break;
        }

        $output->writeln("Ending Process " . $name . " " . date('Y-m-d H:i:s')); 
        
        return Cli::RETURN_SUCCESS;
    }
}