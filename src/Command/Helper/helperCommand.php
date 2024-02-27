<?php
    
    namespace DoctrineEncryptor\DoctrineEncryptorBundle\Command\Helper;
    
    use Doctrine\Persistence\ManagerRegistry;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Attribute\neoxEncryptor;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\DoctrineEncryptorService;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\OpenSSL\OpenSSLAlgo;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\OpenSSL\OpenSSLTools;
    use JsonException;
    use ReflectionException;
    
    class helperCommand
    {
        
        public array $entityStatus = [];
        
        public function __construct(readonly ManagerRegistry $doctrine, readonly DoctrineEncryptorService $doctrineEncryptorService)
        {}
        
        /**
         * @throws ReflectionException
         */
        public function getListAllEntitySupport() : array
        {
            $metadata = $this->doctrine->getManager()->getMetadataFactory()->getAllMetadata();
            
            foreach ($metadata as $classMetadata) {
                $entityName         = $classMetadata->getName();
                $properties         = $classMetadata->getFieldNames();
                $propertiesList     = [];
                
                if ( !DoctrineEncryptorService::isSupport($entityName)) {
                    continue;
                }
                
                foreach ($properties as $property) {
                    foreach ($classMetadata->getReflectionProperty($property)->getAttributes() as $attribute) {
                        if ($attribute->getName() === neoxEncryptor::class) {
                            
                            $buildIn            = $attribute->newInstance()->build;
                            
                            $fieldMapping       = $classMetadata->getFieldMapping($property);
                            $type               = $fieldMapping['type'] ?? null;
                            $length             = isset($fieldMapping['length']) ? ' - ' . $fieldMapping['length'] : '';
                            $propertiesList[]   = $type ? sprintf('   Encryptor : %s - Property : %s ( %s%s ) ', $buildIn, $property, $type, $length) : $property;
                            
                            break;
                        }
                    }
                }
                if (!empty($propertiesList)) {
                    $this->entityStatus[] = [
                        'entity'        => $entityName,
                        'properties'    => $propertiesList,
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
        
    }