<?php
    
    namespace DoctrineEncryptor\DoctrineEncryptorBundle\Command\Helper;
    
    use Doctrine\Persistence\ManagerRegistry;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Attribute\neoxEncryptor;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\DoctrineEncryptorService;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\OpenSSL\OpenSSLAlgo;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\OpenSSL\OpenSSLTools;
    use JsonException;
    use ReflectionException;
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
    use Symfony\Component\Yaml\Yaml;
    
    class helperCommand
    {
        
        public array $entityStatus = [];
        
        public function __construct(readonly ManagerRegistry $doctrine, readonly DoctrineEncryptorService $doctrineEncryptorService, readonly ParameterBagInterface $parameterBag)
        {
        }
        
        /**
         * @throws ReflectionException
         */
        public function getListAllEntitySupport(): array
        {
            $metadata = $this->doctrine->getManager()->getMetadataFactory()->getAllMetadata();
            
            foreach ($metadata as $classMetadata) {
                $entityName     = $classMetadata->getName();
                $properties     = $classMetadata->getFieldNames();
                $propertiesList = [];
                
                if (!DoctrineEncryptorService::isSupport($entityName)) {
                    continue;
                }
                
                foreach ($properties as $property) {
                    foreach ($classMetadata->getReflectionProperty($property)->getAttributes() as $attribute) {
                        if ($attribute->getName() === neoxEncryptor::class) {
                            
                            $buildIn = $attribute->newInstance()->build;
                            
                            $fieldMapping     = $classMetadata->getFieldMapping($property);
                            $type             = $fieldMapping['type'] ?? null;
                            $length           = isset($fieldMapping['length']) ? ' - ' . $fieldMapping['length'] : '';
                            $propertiesList[] = $type ? sprintf('   Encryptor : %s - Property : %s ( %s%s ) ', $buildIn, $property, $type, $length) : $property;
                            
                            break;
                        }
                    }
                }
                if (!empty($propertiesList)) {
                    $this->entityStatus[] = [
                        'entity'     => $entityName,
                        'properties' => $propertiesList,
                    ];
                }
            }
            
            
            return $this->entityStatus;
        }
        
        /**
         * @throws ReflectionException
         * @throws JsonException
         */
        public function setEntityConvert($Entity, $Action): array
        {
            $this->doctrineEncryptorService->setEntityConvert($Entity, $Action);
            return $this->doctrineEncryptorService->neoxStats;
        }
        
        public function getCurrentEncryptor(): string
        {
            return $this->parameterBag->get('doctrine_encryptor.encryptor_system');
        }
        
        
        public function setNewEncryptor(string $encryptor)
        {
            $file        = dirname(__DIR__, 6) . '/config/packages/doctrine_encryptor.yaml';
            $lines       = file($file, FILE_IGNORE_NEW_LINES);
            $comments    = [];
            $yamlContent = [];
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) {
                    $comments[] = $line;
                } else {
                    $yamlContent[] = $line;
                }
            }
            $config                                           = Yaml::parse(implode("\n", $yamlContent), Yaml::PARSE_CUSTOM_TAGS | Yaml::PARSE_CUSTOM_TAGS);
            if (preg_match('/\((.*?)\)/', $encryptor, $matches)) {
                $encryptor = $matches[1]; // La partie correspondante entre parenthèses
   
            }
            $config['doctrine_encryptor']['encryptor_system'] = $encryptor;
            $yaml                                             = Yaml::dump($config, PHP_INT_MAX, 2, Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE, 0);
            $newFileContent                                   = implode("\n", $comments) . "\n" . $yaml;
            
            file_put_contents($file, $newFileContent);
        }

        public function checkKeyExist(){
            $file        = dirname(__DIR__, 6) . '/config/packages/doctrine_encryptor.yaml';
            return  file($file, FILE_IGNORE_NEW_LINES);
        }

        public function deleteKeyExist(){
            $file        = dirname(__DIR__, 6) . '/config/packages/doctrine_encryptor.yaml';
            $lines       = file($file, FILE_IGNORE_NEW_LINES);

            foreach ($lines as $line) {
                if (file_exists($file_path)) {
                    // Supprime le fichier
                    if (unlink($file_path)) {
                        echo "Le fichier a été supprimé avec succès.";
                    } else {
                        echo "Une erreur s'est produite lors de la suppression du fichier.";
                    }
                } else {
                    echo "Le fichier n'existe pas.";
                }
            }

        }
        public function buildListeEncryptor($excludedItem): array{
            $listEncryptor = [
                "Halite (halite)",
                "openSSLAsymmetric (openSSLAsym)",
                "openSSLSymmetric (openSSLSym)"
            ];
            
            return  array_filter($listEncryptor, function($item) use ($excludedItem) {
                return stripos($item, "({$excludedItem})") === false;
            });
        }
    }