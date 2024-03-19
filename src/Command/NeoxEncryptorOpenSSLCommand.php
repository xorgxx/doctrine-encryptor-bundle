<?php
    
    namespace DoctrineEncryptor\DoctrineEncryptorBundle\Command;
    
    use DoctrineEncryptor\DoctrineEncryptorBundle\Attribute\NeoxEncryptor;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Command\Helper\HelperCommand;
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
    
    #[AsCommand(
        name: 'neox:encryptor:openssl',
        description: 'Add a short description for your command',
    )]
    class NeoxEncryptorOpenSSLCommand extends Command
    {
        private const CANCEL = "Cancel";
        private const ALL    = "ALL";
        
        // XDEBUG_TRIGGER=1 php bin/console neox:encryptor:openssl
        public HelperCommand $helperCommand;
        
        public function __construct(HelperCommand $helperCommand)
        {
            $this->helperCommand = $helperCommand;
            parent::__construct();
            
        }
        
        protected function configure(): void
        {
            $this
                ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
                ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
        }
        
        /**
         * @throws ReflectionException
         * @throws \JsonException
         */
        protected function execute(InputInterface $input, OutputInterface $output): int
        {
            $io = new SymfonyStyle($input, $output);
            
            $entity[]           = self::ALL;
            $listeAlgos         = array_column(OpenSSLAlgo::getListe(), 'value');
            $listeAlgos[]       = self::CANCEL;
            
            // Ask user which entity should be moved.
            $question           = new ChoiceQuestion("Please choose the l'agorithme to use:", $listeAlgos);
            $question->setErrorMessage('ENTITY : %s does not exist.');
            $algoOpen           = $this->getHelper('question')->ask($input, $output, $question);
            
            switch ($algoOpen) {
                case self::CANCEL:
                    $io->success('Nothing has been changed.');
                    return Command::SUCCESS;
                    
                case OpenSSLAlgo::OPENSSL_KEYTYPE_RSA->name    : $KeyLengths  = [512, 1024, 2048, 4096];break;          // RSA
                case OpenSSLAlgo::OPENSSL_KEYTYPE_DSA->name    : $KeyLengths  = [512, 1024, 2048];break;                // DSA
                case OpenSSLAlgo::OPENSSL_KEYTYPE_DH->name     : $KeyLengths  = [512, 1024, 2048, 3072, 4096];break;    // DH
                case OpenSSLAlgo::OPENSSL_KEYTYPE_EC->name     : $KeyLengths  = ['prime192v1', 'secp224r1',  'prime256v1', 'secp384r1', 'secp521r1'];break;                  // ECC
                default:
                    $io->success("You have chosen {$algoOpen}.");
                    break;
            }

            $KeyLengths[]   = self::CANCEL;

            $io->newLine();
            // ask which action user wants to doo ?
            $question       = new ChoiceQuestion("Select Key Length (bits): ", $KeyLengths );
            $KeyLengths     = $this->getHelper('question')->ask($input, $output, $question);
            
            switch ($KeyLengths) {
                case self::CANCEL:
                    $io->success('Nothing has been changed.');
                    return Command::SUCCESS;
                default:
                    $io->success("You have chosen {$algoOpen} - {$KeyLengths}.");
                    break;
            }
            
            // process ascymetric encryption
            $r = OpenSSLTools::buildOpenSSLKey($this->helperCommand->openSSLSymEncryptor, $algoOpen, $KeyLengths);
            
            $this->processHaliteKey($input, $output);

            $io->success('You have a new command! Now make it your own! Pass --help to see your options.');
            
            return Command::SUCCESS;
        }

        private function processHaliteKey(InputInterface $input, OutputInterface $output): void
        {
            $autreCommande          = $this->getApplication()->find('n:e:h');
            $autreCommandeArguments = [];
            $autreCommandeInput     = new ArrayInput($autreCommandeArguments);
            $autreCommande->run($autreCommandeInput, $output);
        }
    }
