<?php
namespace Trax\Catalogo\Console;
 
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Console\Cli;
use Trax\Catalogo\Cron\GetStock;
use Trax\Catalogo\Cron\GetCatalog;
use Trax\Catalogo\Cron\OrderStatus;
use \Magento\Framework\App\State;

/**
* Class Commands
*/
class Commands extends Command 
{
   
    const NAME = 'name';
 
    /**
    * @var Trax\Catalogo\Cron\GetStock
    */
    private $_stock;

    /**
    * @var Trax\Catalogo\Cron\GetCatalog
    */
    private $_catalog;


     /**
    * @var Trax\Catalogo\Cron\OrderStatus
    */
    private $_orderStatus;

    /**
    * @var \Magento\Framework\App\State
    */
    protected $_appState;

   public function __construct(GetStock $stock, GetCatalog $catalog, OrderStatus $orderStatus,State $appState)
   {
        $this->_stock   = $stock;
        $this->_catalog = $catalog;
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
       $this->setName('trax:upload');
       $this->setDescription('Upload TRAX Catalog');
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

        $output->writeln("Starting Get Trax " . $name . " " .  date('Y-m-d H:i:s'));

        switch($name){
            case 'stock':
                $this->_stock->execute();
            break;
            case 'catalog':
                $this->_catalog->execute();
            case 'orderStatus':
                $this->_orderStatus->execute();
            break;
        }

        $output->writeln("Ending Get Trax " . $name . " " . date('Y-m-d H:i:s')); 
        
        return Cli::RETURN_SUCCESS;
    }
}