<?php

    namespace DoctrineEncryptor\DoctrineEncryptorBundle\Command;

    use DoctrineEncryptor\DoctrineEncryptorBundle\Attribute\NeoxEncryptor;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Command\Helper\HelperCommand;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\Halite\HaliteTools;
    use ReflectionException;
    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Command\Command;
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

        public function __construct( HelperCommand $helperCommand )
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
                "Create" ];

            // Ask user which entity should be moved.
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
                    $io->success( 'Nothing has been changed.' );
                    return Command::SUCCESS;

                default:
                    $io->success( "You have chosen {$algoOpen}." );
                    break;
            }

            // process ascymetric encryption
            $r = HaliteTools::buildEncryptionKey($this->helperCommand->haliteEncryptor);

            $io->success( "Successfully build. check in folder that you setup in gaufrette.yaml" );
            
            return Command::SUCCESS;
        }
    }
