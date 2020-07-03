<?php
namespace Trax\Catalogo\Console;
 
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Console\Cli;
 
class TraxCommands extends Command 
{
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

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $name = $input->getOption(self::NAME)

        switch($name){
            case 'stock'  : $entityManager = $objectManager->get('\Trax\Catalogo\Cron\GetStock');break;
            case 'catalog': $entityManager = $objectManager->get('\Trax\Catalogo\Cron\GetCatalog');break;
        }

        $output->writeln("Starting Get Trax " . $name . " " .  date('Y-m-d H:i:s'));
        $entityManager->execute();
        $output->writeln("Ending Get Trax " $name . " " . date('Y-m-d H:i:s'));
        
        return Cli::RETURN_SUCCESS;
    }
}