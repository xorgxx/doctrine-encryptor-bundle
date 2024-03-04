<?php

    namespace DoctrineEncryptor\DoctrineEncryptorBundle\Command;

    use DoctrineEncryptor\DoctrineEncryptorBundle\Attribute\neoxEncryptor;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Command\Helper\helperCommand;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\OpenSSL\OpenSSLAlgo;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\OpenSSL\OpenSSLTools;
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

    #[AsCommand( name: 'neox:encryptor:renew', description: 'Add a short description for your command', )]
    class NeoxEncryptorOpenSSLRenewCommand extends Command
    {
        private const CANCEL = "Cancel";
        private const ALL    = "ALL";

        // XDEBUG_TRIGGER=1 php bin/console neox:encryptor:openssl
        public helperCommand $helperCommand;

        public function __construct( helperCommand $helperCommand )
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
            $io            = new SymfonyStyle( $input, $output );
            $entity[]      = self::ALL;
            $listeAlgos    = array_column( OpenSSLAlgo::getListe(), 'value' );
            $listeAlgos  = [self::CANCEL, "Ready to start"];
            $checkKeyExist = $this->helperCommand->checkKeyExist() ? true : false;

            if( !$checkKeyExist ) {
                $io->warning("Key not find. Stopped renew key. ");
                return Command::SUCCESS;
            }
            $io->success("Find key for renew.");

            // Ask user which entity should be moved.
            $question = new ChoiceQuestion( "To Renew key savetly you need to stop all trafic !! :", $listeAlgos );
            $question->setErrorMessage( 'ENTITY : %s does not exist.' );
            $algoOpen = $this->getHelper( 'question' )->ask( $input, $output, $question );

            switch ($algoOpen) {
                case self::CANCEL:
                    $io->success('Nothing has been changed.');
                    return Command::SUCCESS;
                default:
                    $io->success("We start first decrypt all databse ....");
                    $this->processEncryptor($input, $output, $entity);
                    $io->success("We start deleted Key ....");

                    // delete old key
                    if(OpenSSLTools::deleleteAsymetricKey()){

                        $this->processNewKey( $input, $output );
                        $io->success("We create new Key ....");

                        $io->success("Now all ok. - You can now use neox:encryptor:wasaaaa to encrypt database.");
                    }else{
                        $io->warning("Anything change, we have just uncrypt all database. error : edx004");
                    };

                    break;
            }

            return Command::SUCCESS;
        }

        private function processNewKey(InputInterface $input, OutputInterface $output): void
        {
            $autreCommande          = $this->getApplication()->find('n:e:o');
            $autreCommandeArguments = [

            ];
            $autreCommandeInput     = new ArrayInput($autreCommandeArguments);
            $autreCommande->run($autreCommandeInput, $output);
        }

        private function processEncryptor(InputInterface $input, OutputInterface $output, array $entity, string $mode = "Decrypt"): void
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
