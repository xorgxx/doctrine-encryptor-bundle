<?php
    
    namespace DoctrineEncryptor\DoctrineEncryptorBundle\Command;
    
    use DoctrineEncryptor\DoctrineEncryptorBundle\Attribute\neoxEncryptor;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Command\Helper\helperCommand;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\OpenSSL\OpenSSLAlgo;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\OpenSSL\OpenSSLTools;
    use ReflectionException;
    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Input\InputOption;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\Question\ChoiceQuestion;
    use Symfony\Component\Console\Style\SymfonyStyle;
    use Symfony\Component\Console\Input\ArrayInput;
    
    #[AsCommand(
        name: 'neox:encryptor:switch',
        description: 'Add a short description for your command',
    )]
    class NeoxEncryptorConvertCommand extends Command
    {
        private const CANCEL = "Cancel";
        private const ALL = "ALL";
        
        // XDEBUG_TRIGGER=1 php bin/console neox:encryptor:switch
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
                ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
        }
        
        /**
         * @throws ReflectionException
         * @throws \JsonException
         */
        protected function execute(InputInterface $input, OutputInterface $output): int
        {
            $io            = new SymfonyStyle($input, $output);
            $entity[]      = self::ALL;
            $listeAlgos[]  = self::CANCEL;
            $listEncryptor = [
                "openSSLAsymmetric (openSSLAsym)",
                "openSSLSymmetric (openSSLSym)"
            ];
            
            $switchEncryptor    = $this->askChoiceQuestion("Please choose ENCRYPTOR to switch to:", $listEncryptor, $input, $output);
            
            $this->processEncryptor($input, $output, $entity);
        
            // Update parameter in doctrine_encryptor.yaml
            $this->helperCommand->setNewEncryptor($switchEncryptor);
            
            $this->cacheClear($input, $output);
            
//            $this->processEncryptor($input, $output, $entity, "Encrypt");
            
            $io->success("Switch : has been processed. - You can now use neox:encryptor:wasaaaa to encrypt database.");
            
            return Command::SUCCESS;
        }
        
        private function askChoiceQuestion(string $questionText, array $choices, InputInterface $input, OutputInterface $output): string
        {
            $question = new ChoiceQuestion($questionText, $choices);
            $question->setErrorMessage('%s does not exist.');
            return $this->getHelper('question')->ask($input, $output, $question);
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
        
        private function cacheClear(InputInterface $input, OutputInterface $output): void
        {
            $autreCommande          = $this->getApplication()->find('c:c');
            $autreCommandeInput = new ArrayInput([
                // Spécifiez les options et arguments nécessaires pour la commande
                // Par exemple:
                // 'argument' => 'valeur',
                // '--option' => 'valeur',
            ]);
            
            $autreCommande->run($autreCommandeInput, $output);
        }
    }
