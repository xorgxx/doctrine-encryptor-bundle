<?php
    
    namespace DoctrineEncryptor\DoctrineEncryptorBundle\Pattern;
    
    use DoctrineEncryptor\DoctrineEncryptorBundle\Attribute\neoxEncryptor;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Entity\NeoxEncryptor as NeoxEncryptorEntity;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\OpenSSL\OpenSSLTools;
    use JsonException;
    use ReflectionClass;
    use ReflectionException;
    
    class DoctrineEncryptorService
    {
        public array $encryptors = [];
        public array $neoxStats = [
            "Encrypt" => 0,
            "Decrypt" => 0,
        ];
        private bool $force = false;
        private mixed $encryptor;
        
        public function __construct(readonly NeoxDoctrineFactory $neoxDoctrineFactory, readonly NeoxDoctrineTools $neoxDoctrineTools)
        {
            $this->getEncryptor();
        }
        
        /**
         * @throws ReflectionException
         */
        public static function isSupport(string $className): bool
        {
            $filename    = (new ReflectionClass($className))->getFileName();
            $fileContent = file_get_contents($filename);
            
            // Search for neoxEncryptor in file contents, ignoring comments
            $cleanContent = preg_replace('/\/\*.*?\*\/|\/\/.*?[\r\n]/s', '', $fileContent);
            return str_contains($cleanContent, neoxEncryptor::class);
        }
        
        public static function callBackType(string $type, mixed $mode = false)
        {
            
            $array = ["007" => "007"];
            $date  = new \dateTime("2007-07-07 07:07:07");
            
            $msg = [
                "string"            => "<enc>",
                "integer"           => 7,
                "int"               => 7,
                "smallInt"          => 77,
                "bigInt"            => 777,
                "bool"              => true,
                "boolean"           => true,
                "dateTime"          => $date,
                "DateTimeInterface" => $date,
                "date"              => $date,
                "time"              => $date,
                "float"             => 777.7,
                "Decimal"           => 777.7,
                "array"             => (object)$array,
                "object"            => (object)$array,
                "Array"             => (object)$array,
                "ArrayObject"       => (object)$array,
                "ArrayIterator"     => (object)$array,
            ];
            $o   = ($mode && in_array($type, $msg, true)) ? true : ($msg[$type] ?? null);
            return $o;
        }
        
        /**
         * @throws ReflectionException
         * @throws JsonException
         */
        public function encrypt($entity, string $event, $force = false): void
        {
            $items       = [];
            $Reflections = $this->getReflection($entity);
            
            foreach ($Reflections[$entity::class] as $Reflection) {
                // process the value Encrypt/decrypt
                $t       = serialize($Reflection->getValue());
                $process = $this->encryptor->encrypt($t);
                
                // get the value of the property
                if ($Reflection->getAttributeProperty() === "in") {
                    // set the value of the property with the processed value in entity
//                    $Reflection->getProperty()->setValue($entity, $process);
                }
                
                if ($Reflection->getAttributeProperty() === "out" &&
                    ($event === "postPersist" || $event === "preUpdate" || $event === "convert") &&
                    $this->force === false
                ) {
                    // set the value of the property with the processed value in entity
                    $neoxEncryptor = $this->encryptor->getEncryptorId($entity);
                    if ( $Reflection->getValue() ) {
                        $items[$Reflection->getPropertyName()] = $process;
                        $process                               = self::callBackType($Reflection->getType());
                        if ($facker = $Reflection->getAttributeFacker()) {
                            $process = (new ReflectionClass($facker))->newInstance()->create();
                        };
                    } else {
                        $process = null;
                    }
                }
                // preUpdate source entity will be encrypted
                $Reflection->getProperty()->setValue($entity, $process);
            }
            if ($items) {
                $neoxEncryptor?->setContent(json_encode($items, JSON_THROW_ON_ERROR | false, 512));
                $this->setNeoxEncryptor($neoxEncryptor);
            }
            
            ++$this->neoxStats["Encrypt"];
        }
        
        /**
         * @throws ReflectionException
         * @throws JsonException
         */
        public function decrypt($entity, string $event, bool $force = false): void
        {
            /** @var entityAttribute $Reflection */
            $Reflections   = $this->getReflection($entity);
            $neoxEncryptor = $this->encryptor->getEncryptorId($entity);
            
            foreach ($Reflections[$entity::class] as $Reflection) {
                // process the value Encrypt/decrypt
                $propertyValue = $Reflection->getValue();
                $process       = $propertyValue;
                if (OpenSSLTools::isBase64($propertyValue)) {
                    $decryptedValue     = $this->encryptor->decrypt($propertyValue);
                    if ($this->isSerialized_($decryptedValue)) {
                        $process        = $this->isSerialized($decryptedValue) ? $this->isSerialized($decryptedValue) : $propertyValue;
                    }
                }
                
                if ($Reflection->getAttributeProperty() === "in") {
                    // process the value Encrypt/decrypt
                    // $process = $this->encryptor->decrypt($Reflection->getValue());
                }
                
                if ($Reflection->getAttributeProperty() === "out" && $neoxEncryptor->getId()) {
                    $propertyName = $Reflection->getPropertyName();
                    $content      = json_decode($neoxEncryptor->getContent(), false, 512, JSON_THROW_ON_ERROR);
                    $value        = isset($content->$propertyName) ?? null;
                    $process      = $value ? $this->isSerialized($this->encryptor->decrypt($content->$propertyName)) : null;
                }
                
                $Reflection->getProperty()->setValue($entity, $process);
            }
            ++$this->neoxStats["Decrypt"];
        }
        
        /**
         */
        public function remove($entity): void
        {
            // get id neoxEncryptor to remove
            $neoxEncryptor = $this->encryptor->getEncryptorId($entity);
            $this->encryptor->entityManager->remove($neoxEncryptor);
        }
        
        /**
         * Use for processing by CLI Symfony ONLY
         *
         * @throws ReflectionException
         * @throws JsonException
         */
        public function setEntityConvert($entity, string $action): void
        {
            $this->neoxStats["Decrypt"] = 0;
            $this->neoxStats["Encrypt"] = 0;
            
            if ($Entity = $this->encryptor->entityManager->getRepository($entity)->findall()) {
                
                /**
                 * Important : Reset the listeners !! it will loop for ever !!
                 * THIS should affect only this instance of DoctrineEncryptor
                 **/
                $this->neoxDoctrineTools->EventListenerPostFlush();
                $this->neoxDoctrineTools->EventListenerPostUpdate();
                
                foreach ($Entity as $item) {
                    if ($action === "Decrypt") {
//                        $this->decrypt($item, "convert", false);
                        // check if property is encrypted in NeoxEncryptor if yes delete
                        if ($neoxEncryptor = $this->encryptor->getEncryptorId($item)) {
                            $this->encryptor->entityManager->remove($neoxEncryptor);
                        }
                        $this->encryptor->entityManager->persist($item);
                    } else {
                        $this->encrypt($item, "convert", false);
                    }
                    
                }
                // flush the changes
                $this->encryptor->entityManager->flush();
                
                // Important : restart the listeners !!
                $this->neoxDoctrineTools->EventListenerOnFlush(true);
                $this->neoxDoctrineTools->EventListenerPostUpdate(true);
            }
        }
        
        
        /**
         * @throws ReflectionException
         */
        public function getReflection($entity): array
        {
            
            $r[$entity::class] = [];
            
            $reflectorName = new ReflectionClass($entity);
            foreach ($reflectorName->getProperties() as $property) {
                
                $object = new ReflectionInfo();
                $object->setProperty($property);
                
                // filter on "neoxEncryptor" attribute
                // https://www.php.net/manual/fr/reflectionclass.getattributes.php
                $encryptAttribute = $property->getAttributes(neoxEncryptor::class)[0] ?? null;
                if ($encryptAttribute !== null) {
                    // Accessing properties of the attribute neoxEncryptor class if "in" or "out"
                    $attriNInstance = $encryptAttribute->newInstance();
                    $object->setAttributeProperty($attriNInstance->build);
                    $object->setAttributeFacker($attriNInstance->facker);
                    $object->setType($property->getType()->getName());
                    $object->setPropertyName($property->getName());
                    $object->setValue($property->getValue($entity));
                    $r[$entity::class][] = $object;
                }
            }
            return $r;
        }
        
        
        public function getEncryptor(): void
        {
            $this->encryptor = $this->neoxDoctrineFactory->buildEncryptor();
        }
        
        /**
         * @param $neoxEncryptor
         *
         * @return void
         */
        private function setNeoxEncryptor($neoxEncryptor): void
        {
            $this->force = true;
            $this->encryptor->entityManager->persist($neoxEncryptor);
            $this->encryptor->entityManager->flush();
            $this->force = false;
        }
        
        public function encryptOFF()
        {
            return $this->neoxDoctrineFactory->parameterBag->get("doctrine_encryptor.encryptor_off") ?? true;
        }
        
        private function isSerialized($data)
        {
            $unserializedData = @unserialize($data); //&& (is_array($unserializedData) || is_object($unserializedData))
            if ($unserializedData !== false ) {
                return $unserializedData;
            }
            return $unserializedData == false ? $data : $unserializedData;
        }
        
        private function isSerialized_($data)
        {
            $unserializedData = @unserialize($data);    // && (is_array($unserializedData) || is_object($unserializedData))
            if ($unserializedData !== false ) {
                return true;
            }
            return false;
        }
    }