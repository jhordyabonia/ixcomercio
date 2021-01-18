<?php
namespace Trax\Places\Console;
 
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Console\Cli;
use Trax\Places\Cron\GetPlaces;

use \Magento\Framework\App\State;

/**
* Class Commands
*/
class PlaceCommands extends Command 
{
   
    const NAME = 'name';
 
    /**
    * @var Trax\Catalogo\Cron\GetPlaces
    */
    private $_places;

    

    /**
    * @var \Magento\Framework\App\State
    */
    protected $_appState;

   public function __construct(GetPlaces $places, State $appState)
   {
        $this->_places   = $places;
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
       $this->setName('trax:places');
       $this->setDescription('Upload TRAX Places');
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
            case 'run':
                $this->_places->execute();
            break;
        }

        $output->writeln("Ending Get Trax " . $name . " " . date('Y-m-d H:i:s')); 
        
        return Cli::RETURN_SUCCESS;
    }
}