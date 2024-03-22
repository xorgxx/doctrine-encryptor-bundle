<?php

    namespace DoctrineEncryptor\DoctrineEncryptorBundle\Command;

    use DoctrineEncryptor\DoctrineEncryptorBundle\Attribute\NeoxEncryptor;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Command\Helper\HelperCommand;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\Halite\HaliteTools;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\DoctrineEncryptorService;
    use Psr\Log\LoggerInterface;
    use ReflectionException;
    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\ArrayInput;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Input\InputOption;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\Question\ChoiceQuestion;
    use Symfony\Component\Console\Style\SymfonyStyle;

    #[AsCommand( name: 'neox:encryptor:halite', description: 'Builder encryption key for halite', )]
    class NeoxEncryptorHaliteCommand extends Command
    {
        private const CANCEL = "Cancel";
        private const ALL    = "ALL";

        // XDEBUG_TRIGGER=1 php bin/console neox:encryptor:openssl
        public HelperCommand $helperCommand;

        public function __construct( HelperCommand $helperCommand, readonly LoggerInterface $logger)
        {
            $this->helperCommand = $helperCommand;
            parent::__construct();

        }

        protected function configure(): void
        {
            $this->addArgument( 'arg1', InputArgument::OPTIONAL, 'Argument description' )->addOption( 'option1', null, InputOption::VALUE_NONE, 'Option description' );
        }

        /**
         * @throws ReflectionException
         * @throws \JsonException
         */
        protected function execute( InputInterface $input, OutputInterface $output ): int
        {
            $io           = new SymfonyStyle( $input, $output );
            $entity[]     = self::ALL;
            $listeAlgos = [ self::CANCEL,
                "Create",
                "Decrypt before"
            ];

            $CurrentEncryptor = $this->helperCommand->getCurrentEncryptor();
            DoctrineEncryptorService::logger("Create halite key | current encyptor is  : " . $CurrentEncryptor, $this->logger);
            
            // Ask user which entity should be moved.
            $io->warning( [
                "Prior to initiating the process, ensure that all data in your database is encrypted.",
                "We highly advise halting all traffic to your database and putting your website in maintenance mode."
            ]);
      
            $question = new ChoiceQuestion( "", [self::CANCEL, "Continue, i know the risque"] );
            $question->setErrorMessage( 'ENTITY : %s does not exist.' );
            $algoOpen = $this->getHelper( 'question' )->ask( $input, $output, $question );

            switch( $algoOpen ) {
                case self::CANCEL:
                    $io->success( 'Nothing has been changed.' );
                    DoctrineEncryptorService::logger("Create halite key | User cancel!", $this->logger);
                    return Command::SUCCESS;

                default:
                    $io->success( "You have chosen to continue." );
                    break;
            }

            $io->warning( [
                "Builder key for HALITE",
                "If the first time you run this command, it will create a key. You dont need to read the next message",
                "If you have previously encrypted data in your database, do not attempt any further actions until you have decrypted all the data in your database. ** BE CAUTIOUS **",
                "If you are not sure if you have all your data encrypted, just run the command : php bin/console neox:encryptor:wasaaaa and do not worry about it.",
                "If key exist it will be override."
                ]);
            
            $question = new ChoiceQuestion( "", $listeAlgos );
            $question->setErrorMessage( 'ENTITY : %s does not exist.' );
            $algoOpen = $this->getHelper( 'question' )->ask( $input, $output, $question );

            switch( $algoOpen ) {
                case self::CANCEL:
                    DoctrineEncryptorService::logger("Create halite key | User cancel!", $this->logger);
                    $io->success( 'Nothing has been changed.' );
                    return Command::SUCCESS;
                case "Decrypt before":
                    DoctrineEncryptorService::logger("Create halite key | Decrypt before ...", $this->logger);
                    $this->processEncryptor( $input, $output, "Decrypt" );
                default:
                    DoctrineEncryptorService::logger("Create halite key | starting build key ...", $this->logger);
                    $io->success( "You have chosen {$algoOpen}." );
                    break;
            }
            $io->info( "Starting process building Key's" );
            
            // process ascymetric encryption
            $r = HaliteTools::buildEncryptionKey($this->helperCommand->haliteEncryptor);
            DoctrineEncryptorService::logger("Create halite key | Done build FINISH", $this->logger);
            $io->success( "Successfully build. check in folder that you setup in gaufrette.yaml" );
            
            return Command::SUCCESS;
        }

        private function processEncryptor(InputInterface $input, OutputInterface $output, string $mode = "Decrypt"): void
        {
            $autreCommande          = $this->getApplication()->find('n:e:w');
            $autreCommandeArguments = [
                '--processing' => 'ALL',
                '--action'     => $mode,
            ];
            $autreCommandeInput     = new ArrayInput($autreCommandeArguments);
            $autreCommande->run($autreCommandeInput, $output);
        }

    }
