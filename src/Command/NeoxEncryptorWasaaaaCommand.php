<?php
    
    namespace DoctrineEncryptor\DoctrineEncryptorBundle\Command;
    
    use DoctrineEncryptor\DoctrineEncryptorBundle\Attribute\neoxEncryptor;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Command\Helper\helperCommand;
    use ReflectionException;
    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Input\InputOption;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\Question\ChoiceQuestion;
    use Symfony\Component\Console\Style\SymfonyStyle;
    
    #[AsCommand(
        name: 'neox:encryptor:wasaaaa',
        description: 'Add a short description for your command',
    )]
    class NeoxEncryptorWasaaaaCommand extends Command
    {
        private const CANCEL = "Cancel";
        private const ALL    = "ALL";
        
        // XDEBUG_TRIGGER=1 php bin/console neox:encryptor:wasaaaa
        public helperCommand $helperCommand;
        
        public function __construct(helperCommand $helperCommand)
        {
            $this->helperCommand = $helperCommand;
            parent::__construct();
            
        }
        
        protected function configure(): void
        {
            $this
                ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
                ->addOption('processing', null, InputOption::VALUE_REQUIRED, 'Processing description')
                ->addOption('action', null, InputOption::VALUE_REQUIRED, 'Action description');
        }
        
        /**
         * @throws ReflectionException
         * @throws \JsonException
         */
        protected function execute(InputInterface $input, OutputInterface $output): int
        {
            $io = new SymfonyStyle($input, $output);
            $entity[]           = self::CANCEL;
            $entity[]           = self::ALL;
            $entities[]         = [];
            
            // finding all entities with properties to encrypt or decrypt
            $EntitySupports = $this->helperCommand->getListAllEntitySupport();
            // foreach entity add it to list, to trait later
            // give back list of entities with properties to user "as status"
            foreach ($EntitySupports as $entityData) {
                $io->title(sprintf('[Find in] Entity : %s ', $entityData['entity']));
                $io->text($entityData["properties"]);
                $entity[]   = $entityData['entity'];
                $entities   = [$entityData['entity']];
            }
            
            $CurrentEncryptor = $this->helperCommand->getCurrentEncryptor();
            $io->info("Cureent encyptor is : " . $CurrentEncryptor);
            
            // Ask user which entity should be moved.
//            $question       = new ChoiceQuestion("Please choose the ENTITY you want work on:", $entity);
//            $question->setErrorMessage('ENTITY : %s does not exist.');
//            $processing     = $this->getHelper('question')->ask($input, $output, $question);
            $processing = $this->getProcessingOption($input, $output, $entity);
            
            switch ($processing) {
                case self::CANCEL:
                    $io->success('Nothing has been changed.');
                    return Command::SUCCESS;
                case self::ALL:
                    $io->success('You have chosen ALL.');
                    break;
                default:
                    $io->success("You have chosen {$processing}.");
                    break;
            }
            
            $io->newLine();
            // ask which action user wants to doo ?
//            $question   = new ChoiceQuestion("Select action : default [". self::CANCEL. "]",
//                [ self::CANCEL, "Encrypt", "Decrypt"], self::CANCEL
//            );
//            $action     = $this->getHelper('question')->ask($input, $output, $question);
            // ask which action user wants to do?
            $action = $this->getActionOption($input, $output);
            
            switch ($action) {
                case self::CANCEL:
                    $io->success('Nothing has been changed.');
                    return Command::SUCCESS;
                default:
                    $io->success("You have chosen {$action}.");
                    break;
            }
            
            // loop through one/all entities to encrypt/decrypt
            if($processing == "ALL"){
                foreach ($entities as $entity) {
                    if ( $stats = $this->helperCommand->setEntityConvert($entity, $action) ) {
                        $io->success("Entity : {$entity} has been processed. - {$action}  / {$stats[$action]} " );
                    }else{
                        $io->warning("Entity : {$entity} has not been processed. {$stats["Decrypt"]} / {$stats["Encrypt"]} " );
                    }
                } 
            }else{
                if ( $stats = $this->helperCommand->setEntityConvert($processing, $action) ) {
                    $io->success("Entity : {$processing} has been processed. - {$action}  / {$stats[$action]} " );
                }else{
                    $io->warning("Entity : {$processing} has not been processed. {$stats["Decrypt"]} / {$stats["Encrypt"]} " );
                }
            }

            return Command::SUCCESS;
        }
        
        private function getProcessingOption(InputInterface $input, OutputInterface $output, array $entity): string
        {
            $processing = $input->getOption('processing');
            
            if ($processing === null) {
                $question = new ChoiceQuestion("Please choose the ENTITY you want work on:", $entity);
                $question->setErrorMessage('ENTITY : %s does not exist.');
                $processing = $this->getHelper('question')->ask($input, $output, $question);
            }
            
            return $processing;
        }
        
        private function getActionOption(InputInterface $input, OutputInterface $output): string
        {
            $action = $input->getOption('action');
            
            if ($action === null) {
                $question = new ChoiceQuestion("Select action : default [" . self::CANCEL . "]",
                    [self::CANCEL, "Encrypt", "Decrypt"], self::CANCEL
                );
                $action = $this->getHelper('question')->ask($input, $output, $question);
            }
            
            return $action;
        }
    }
