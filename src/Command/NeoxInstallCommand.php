<?php

    namespace DoctrineEncryptor\DoctrineEncryptorBundle\Command;

    use DoctrineEncryptor\DoctrineEncryptorBundle\Attribute\NeoxEncryptor;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Command\Helper\HelperCommand;
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
    
    #[AsCommand(name: 'neox:encryptor:install', description: 'Add a short description for your command',)]
    class NeoxInstallCommand extends Command
    {
        private const CANCEL = "Cancel";
        private const ALL    = "ALL";

        // XDEBUG_TRIGGER=1 php bin/console neox:encryptor:switch
        public HelperCommand $helperCommand;

        public function __construct(HelperCommand $helperCommand)
        {
            $this->helperCommand = $helperCommand;
            parent::__construct();
        }

        protected function configure(): void
        {
            $this->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')->addOption(
                    'option1',
                    null,
                    InputOption::VALUE_NONE,
                    'Option description'
                );
        }

        /**
         * @throws ReflectionException
         * @throws \JsonException
         */
        protected function execute(InputInterface $input, OutputInterface $output): int
        {
            $io           = new SymfonyStyle($input, $output);
            $entity[]     = self::ALL;
            $listeAlgos[] = self::CANCEL;
            
            // Welcome
            $io->text([
                "Welcome to Doctrine Encryptor Bundle!", 
                "We are going to check if your application have all requirements.",
                "GaufretteBundle & caching system",
                "Gaufrette is recommended to use Doctrine Encryptor to provide better security. in this configuration your key will be stored in external server.",
                "On top of gaufrette we also recommended to use caching system to speed up your application.",
            ] );
            $io->text([
                "Gaufrettebundle need to at list minimal configuration to work. ",
                "In config\\gaufrette.yml you need to specify : ",
                'knp_gaufrette:',
                '    adapters:',
                '        local_adapter:',
                '            local:',
                "                directory: '%kernel.project_dir%/config/doctrine-encryptor/'",
                '',
                '    filesystems:',
                '        local:',
                '            adapter: local_adapter'
            ] );

            $io->text([
                "Caching system need to be install & working ",
            ] );
            
            // 
            
            $switchEncryptor = $this->askChoiceQuestion(
                "Please choose ENCRYPTOR to switch to:",
                $listEncryptor,
                $input,
                $output
            );

            $io->success("Switch : has been processed. - You can now use neox:encryptor:wasaaaa to encrypt database.");

            return Command::SUCCESS;
        }

        private function askChoiceQuestion(
            string $questionText,
            array $choices,
            InputInterface $input,
            OutputInterface $output
        ): string {
            $question = new ChoiceQuestion($questionText, $choices);
            $question->setErrorMessage('%s does not exist.');
            return $this->getHelper('question')->ask($input, $output, $question);
        }

        private function processEncryptor(
            InputInterface $input,
            OutputInterface $output,
            array $entity,
            string $mode = "Decrypt"
        ): void {
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
            $autreCommande      = $this->getApplication()->find('c:c');
            $autreCommandeInput = new ArrayInput([
                // Spécifiez les options et arguments nécessaires pour la commande
                // Par exemple:
                // 'argument' => 'valeur',
                // '--option' => 'valeur',
            ]);

            $autreCommande->run($autreCommandeInput, $output);
        }
    }
