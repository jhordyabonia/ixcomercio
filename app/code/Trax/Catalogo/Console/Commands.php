<?php
namespace Trax\Catalogo\Console;
 
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Console\Cli;
use Trax\Catalogo\Cron\GetStock;
use Trax\Catalogo\Cron\GetCatalog;


/**
* @var Trax\Catalogo\Cron\GetStock
*/
private $_stock;

/**
* @var Trax\Catalogo\Cron\GetCatalog
*/
private $_catalog;
 
class Commands extends Command 
{
   public function __construct(GetStock $stock, GetCatalog $catalog)
   {
        $this->_stock   = $stock;
        $this->_catalog = $catalog;
   }

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

   protected  function execute(InputInterface $input, OutputInterface $output)
   {
        $name = $input->getOption(self::NAME);

        $output->writeln("Starting Get Trax " . $name . " " .  date('Y-m-d H:i:s'));

        switch($name){
            case 'stock':
                $this->_stock->execute();
            break;
            case 'catalog':
                $this->_catalog->execute();
            break;
        }

        $output->writeln("Ending Get Trax " $name . " " . date('Y-m-d H:i:s')); 
        
        return Cli::RETURN_SUCCESS;
    }
}