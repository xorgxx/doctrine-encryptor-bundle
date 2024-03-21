<?php

    namespace DoctrineEncryptor\DoctrineEncryptorBundle\Command;
    
    use DoctrineEncryptor\DoctrineEncryptorBundle\Resources\script\PostInstallScript;
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
    use Symfony\Component\Yaml\Yaml;

    #[AsCommand(name: 'neox:encryptor:install', description: 'Add a short description for your command',)]
    class NeoxInstallCommand extends Command
    {
        private const CANCEL = "Cancel";
        private const ALL    = "ALL";

        // XDEBUG_TRIGGER=1 php bin/console neox:encryptor:switch

        public function __construct()
        {
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
            $io->info([
                "Welcome to Doctrine Encryptor Bundle automique setup!", 
                "We are going to check if your application have all requirements.",
                "GaufretteBundle & caching system",
                "Gaufrette is recommended to use Doctrine Encryptor to provide better security. in this configuration your key will be stored in external server.",
                "On top of gaufrette we also recommended to use caching system to speed up your application.",
            ] );
//            $io->warning([
//                "Gaufrettebundle need to at list minimal configuration to work. ",
//                "In config\\gaufrette.yml you need to specify : ",
//                'knp_gaufrette:',
//                '    adapters:',
//                '        foo_adapter:',
//                '            local:',
//                "                directory: '%kernel.project_dir%/config/doctrine-encryptor/'",
//     
//            ] );

//            $io->warning([
//                "Caching system need to be install & working ",
//            ] );
            $switchEncryptor = $this->askChoiceQuestion(
                "Please choose ENCRYPTOR to switch to:",
                [self::CANCEL, "Setup auto"],
                $input,
                $output
            );
            switch( $switchEncryptor ) {
                case self::CANCEL:
                    $io->success( 'Nothing has been changed.' );
                    return Command::SUCCESS;

                default:
                    
                    PostInstallScript::doctrineEncryptor(); // Switche
                    PostInstallScript::gaufrette(); // Switche

                    $io->success( "Setup [standart] is done." );
                    
                    
                    break;
            }
//            $this->processOpenSSLKey($input, $output); // Switche>
            //
            $io->success("Now : you can create key openssl run : neox:encryptor:openssl ");

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

        private function processOpenSSLKey(InputInterface $input, OutputInterface $output): void
        {
            $autreCommande          = $this->getApplication()->find('n:e:o');
            $autreCommandeArguments = [];
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

        private  function recursiveMerge(array $source, array $target): array
        {
            foreach ($source as $key => $value) {
                if (is_array($value) && isset($target[ $key ]) && is_array($target[ $key ])) {
                    // Si la clé existe dans les deux configurations et que les deux valeurs sont des tableaux,
                    // fusionner récursivement les sous-tableaux
                    $target[ $key ] = self::recursiveMerge($value, $target[ $key ]);
                } elseif (!isset($target[ $key ])) {
                    // Si la clé n'existe pas déjà dans la configuration cible, l'ajouter
                    $target[ $key ] = $value;
                }
            }

            return $target;
        }
    }
