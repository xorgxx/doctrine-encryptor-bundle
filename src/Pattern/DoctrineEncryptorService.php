<?php
    
    namespace DoctrineEncryptor\DoctrineEncryptorBundle\Pattern;
    
    use DoctrineEncryptor\DoctrineEncryptorBundle\Attribute\neoxEncryptor;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Entity\NeoxEncryptor as NeoxEncryptorEntity;
    use JsonException;
    use ReflectionClass;
    use ReflectionException;
    
    class DoctrineEncryptorService
    {
        public array $encryptors    = [];
        public array $neoxStats     = [
            "Encrypt"    => 0,
            "Decrypt"    => 0,
        ];
        private mixed $encryptor    ;
        
        public function __construct(readonly NeoxDoctrineFactory $neoxDoctrineFactory, readonly NeoxDoctrineTools $neoxDoctrineTools)
        {
            $this->getEncryptor();
        }
        
        /**
         * @throws ReflectionException
         */
        public static function isSupport(string $className): bool
        {
            $filename = (new ReflectionClass($className))->getFileName();
            return str_contains(file_get_contents($filename), neoxEncryptor::class);
        }
        
        public static function callBackType(string $type, mixed $mode = false)
        {
            $msg = [
                "string"        => "<enc>",
                "integer"       => 7,
                "smallInt"      => 77,
                "bigInt"        => 777,
                "boolean"       => true,
                "dateTime"      => "2000-02-02 02:02:02",
                "date"          => "2000-02-02",
                "time"          => "02:02:02",
                "float"         => 777.7,
                "Decimal"       => 777.7,
                "array"         => ["007" => "007"],
                "object"        => ["Decimal" => 777.7, "Array" => ["007" => "007"]],
            ];
            
            return ($mode && in_array($mode, $msg, true)) ? true : ($msg[$type] ?? null);
        }
        
        /**
         * @throws ReflectionException
         * @throws JsonException
         */
        public function encrypt($entity, string $event, $force = false): void
        {
            $this->processFields($entity, fn($value) => $this->encryptor->encrypt($value), $event, "encrypt", $force);
            ++$this->neoxStats["Encrypt"];
        }
        
        /**
         * @throws ReflectionException
         * @throws JsonException
         */
        public function decrypt($entity, string $event, bool $force = false): void
        {
            $this->processFields($entity, fn($value) => $this->encryptor->decrypt($value), $event, "decrypt", $force);
            ++$this->neoxStats["Decrypt"];
        }
        
        /**
         */
        public function remove( $entity ): void
        {
            // get id neoxEncryptor to remove
            $neoxEncryptor  = $this->encryptor->getEncryptorId($entity);
            $this->encryptor->entityManager->remove($neoxEncryptor);
        }
        
        /**
         * Use for processing by CLI Symfony ONLY
         * @throws ReflectionException
         * @throws JsonException
         */
        public function setEntityConvert($entity, string $action): void{
            if ( $Entity    = $this->encryptor->entityManager->getRepository($entity)->findall()) {
                
                /**
                 * Important : Reset the listeners !! it will loop for ever !!
                 * THIS should affect only this instance of DoctrineEncryptor
                 **/
                $this->neoxDoctrineTools->EventListenerPostFlush();
                $this->neoxDoctrineTools->EventListenerOnFlush();
                
                foreach ( $Entity as $item) {
                    if ($action === "Decrypt") {
                        // check if property is encrypted in NeoxEncryptor if yes delete
                        if( $neoxEncryptor  = $this->encryptor->getEncryptorId($item)) {
                            $this->encryptor->entityManager->remove($neoxEncryptor);
                        }
                        $this->encryptor->entityManager->persist($item);
                    }else{
                        $this->encrypt($item, "convert", false);
                    }
                    
                }
                // flush the changes
                $this->encryptor->entityManager->flush();
                
                // Important : restart the listeners !!
                $this->neoxDoctrineTools->EventListenerOnFlush(true);
                $this->neoxDoctrineTools->EventListenerPostFlush(true);
            }
        }
        
        /**
         * @throws ReflectionException
         * @throws JsonException
         * @throws ExceptionInterface
         */
        private function processFields($entity, callable $method, string $event, string $mode = "decrypt", bool $force = false): void
        {
            $neoxEncryptor  = $this->encryptor->getEncryptorId($entity);
            $items          = null;
            $reflectorName  = new ReflectionClass($entity);
            
            foreach ($reflectorName->getProperties() as $property) {
                // filter on "neoxEncryptor" attribute
                // https://www.php.net/manual/fr/reflectionclass.getattributes.php
                $encryptAttribute = $property->getAttributes(neoxEncryptor::class)[0] ?? null;
                if ($encryptAttribute !== null) {
                    // Accessing properties of the attribute neoxEncryptor class if "in" or "out"
                    $attributeProperty  = $encryptAttribute->newInstance()->build;
                    // get the type to later use to process the value by Type
                    $type               = $property->getType()->getName();
                    // get data
                    $propertyName       = $property->getName();
                    // get the value item
                    $value              = $property->getValue($entity);
                    $process            = $value;
                    
                    /**
                     * Starting the process to encrypt or decrypt Value
                     **/
                    if ($attributeProperty === "in") {
                        // process the value Encrypt/decrypt
                        // Process the value
                        $process = $method($value);
                    } else {
                        // process the value Encrypt/decrypt on specific Event PostFlush to add record in neoxEncryptor
                        $this->encryptors[$reflectorName->getName()] = $attributeProperty;
                        if (!$force) {
                            //
                            unset($this->encryptors[$reflectorName->getName()]);
                            
                            if ($value_ = $neoxEncryptor?->getContent()) {
                                $value_     = json_decode($value_, false, 512, JSON_THROW_ON_ERROR)->$propertyName;
                                $process    = $mode === "decrypt" ? $method($value_) : self::callBackType($type);
                                
                                if ($mode === "encrypt") {
                                    $items[$property->getName()] = $this->encryptor->encrypt($value);
                                }
                            } elseif ($event !== "postLoad") {
                                /**
                                 * ===== VERY IMPORTANT =====
                                 * We check $event make sure that data will not be created or updated to avoid problems in data !!!
                                 **/
                                $items[$property->getName()] = $this->encryptor->encrypt($value);
                                $process = self::callBackType($type);
                            }
                        }
                    }
                    
                    // set the value of the property with the processed value in entity
                    $property->setValue($entity, $process);
                    
                }
            }
            // If $event is "convert" we add the data in neoxEncryptor we have to do without EventListener Doctrine !!
            if($event === "convert") {$this->encryptor->entityManager->persist($entity);}
            
            /**
             * ===== VERY IMPORTANT =====
             * We check $event make sur that data will not be created or update to avoid problem in data !!!
             *
             **/
            if ($items && $force === false && $event !== "postLoad") {
                $neoxEncryptor?->setContent(json_encode($items, JSON_THROW_ON_ERROR | false, 512));
                $this->setNeoxEncryptor($neoxEncryptor);
            }
        }

        private function getEncryptor(): void{
            $this->encryptor = $this->neoxDoctrineFactory->buildEncryptor();
        }
        
        private function setNeoxEncryptor(?NeoxEncryptorEntity $encryptor): void
        {
            $this->encryptor->entityManager->persist($encryptor);
            $this->neoxDoctrineTools->EventListenerOnFlush();
//            $this->encryptor->neoxDoctrineTools->EventListenerPostFlush();
            $this->encryptor->entityManager->flush();
            $this->neoxDoctrineTools->EventListenerOnFlush(true);
//            $this->encryptor->neoxDoctrineTools->EventListenerPostFlush(true);
        }
    }