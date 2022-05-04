<?php
namespace Intcomex\Auditoria\Console;
 
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Console\Cli;
use Intcomex\Auditoria\Cron\GetPriceList;
use \Magento\Framework\App\State;

/**
* Class Commands
*/
class Commands extends Command 
{
   
    const NAME = 'name';

     /**
    * @var Intcomex\Auditoria\Cron\GetPriceList
    */
    private $priceList;

    /**
    * @var \Magento\Framework\App\State
    */
    protected $_appState;

   public function __construct(GetPriceList $priceList, State $appState)
   {
        $this->_priceList   = $priceList;
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
       $this->setName('intcomex:auditoria');
       $this->setDescription('Auditoria de precios');
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

        $output->writeln("Starting Get PriceList " . $name . " " .  date('Y-m-d H:i:s'));

        switch($name){
            case 'pricelist':
                $this->_priceList->execute();
            break;
        }

        $output->writeln("Ending Get PriceList " . $name . " " . date('Y-m-d H:i:s')); 
        
        return Cli::RETURN_SUCCESS;
    }
}