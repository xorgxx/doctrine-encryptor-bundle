<?php

    namespace DoctrineEncryptor\DoctrineEncryptorBundle\Pattern;

    use DoctrineEncryptor\DoctrineEncryptorBundle\Attribute\NeoxEncryptor;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Entity\NeoxEncryptor as NeoxEncryptorEntity;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\OpenSSL\OpenSSLTools;
    use JsonException;
    use Reflection;
    use ReflectionClass;
    use ReflectionException;
    use ReflectionProperty;
    use Psr\Log\LoggerInterface;

    class DoctrineEncryptorService
    {
        public array $encryptors = [];
        //        public array   $neoxStats         = [ "wasaaaa" => 0,
        //                                              "Encrypt" => 0,
        //                                              "Decrypt" => 0, ];
        private bool   $force             = false;
        public mixed   $encryptor;
        public ?string $entityCurentState = null;
        public ?string $fieldCurentState  = null;


        public function __construct(
            readonly NeoxDoctrineFactory $neoxDoctrineFactory,
            readonly NeoxDoctrineTools $neoxDoctrineTools,
            readonly LoggerInterface $logger
        ) {
            $this->getEncryptor();
        }

        /**
         * @throws ReflectionException
         */
        public static function isSupport(string $className): bool
        {
            $filename = (new ReflectionClass($className))->getFileName();

            // to not provide of NeoxEncryptorEntity
            if ($filename instanceof NeoxEncryptorEntity) {
                return false;
            }

            $fileContent = file_get_contents($filename);

            // Search for neoxEncryptor in file contents, ignoring comments
            $cleanContent = preg_replace('/\/\*.*?\*\/|\/\/.*?[\r\n]/s', '', $fileContent);
            return str_contains($cleanContent, NeoxEncryptor::class);
        }

        public static function callBackType($type, mixed $mode = false)
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

            if ($mode && (is_object($type) || is_array($type))) {
                return $type;
            }

            return ($mode && in_array($type, $msg, true)) ? true : ($msg[ $type ] ?? null);
        }

        /**
         * @throws ReflectionException
         * @throws JsonException
         */
        public function encrypt($entity, string $event, bool $force = false): ?NeoxEncryptorEntity
        {
            $items = [];

            $Reflections = $this->getReflection($entity);

            /** @var ReflectionInfo $Reflection */
            foreach ($Reflections[ $entity::class ] as $Reflection) {
                // process the value Encrypt/decrypt
                if ($Reflection->getValue() === null) {
                    continue;
                }
                $t       = serialize($Reflection->getValue());
                $process = $this->encryptor->encrypt($t);
                // get the value of the property
                if ($Reflection->getAttributeProperty() === "in") {
                    // set the value of the property with the processed value in entity
                    $process = $Reflection->getValue() ? $process : null;
                }

                if ($Reflection->getAttributeProperty() === "out") {
                    // set the value of the property with the processed value in entity
                    $neoxEncryptor = $this->encryptor->getEncryptorId($entity);
                    if ($Reflection->getValue()) {
                        $items[ $Reflection->getPropertyName() ] = $process;
                        $process                                 = self::callBackType($Reflection->getType());
                        if ($facker = $Reflection->getAttributeFacker()) {
                            $process = (new $facker)->create();
                        };
                    } else {
                        $process = null;
                    }
                }
                // Set new value of the source entity will be encrypted
                $Reflection->getProperty()->setValue($entity, $process);
            }

            $this->logger("enc", $entity, $Reflection);

            if ($items) {
                $this->entityCurentState = $entity::class;
                $neoxEncryptor?->setContent(json_encode($items, JSON_THROW_ON_ERROR | false, 512));
                $this->encryptor->entityManager->persist($neoxEncryptor);
                return $neoxEncryptor;
            }
            return null;
        }

        /**
         * @throws ReflectionException
         * @throws JsonException
         */
        public function decrypt($entity, string $event, bool $force = false): void
        {
            $Reflections = $this->getReflection($entity);
            $this->neoxDoctrineTools->EventListenerAll();
            $neoxEncryptor = $this->encryptor->getEncryptorId($entity);
            $this->neoxDoctrineTools->EventListenerAll(true);

            foreach ($Reflections[ $entity::class ] as $Reflection) {
                // process the value Encrypt/decrypt
                $propertyValue = $Reflection->getValue();
                $process       = $propertyValue;
                if (OpenSSLTools::isBase64($propertyValue, $Reflection->getType())) {
                    $decryptedValue = $this->encryptor->decrypt($propertyValue);
                    $process        = $this->isSerialized($decryptedValue) ? : $propertyValue;
                }

                if ($Reflection->getAttributeProperty() === "in") {
                    // process the value Encrypt/decrypt
                    $process = $Reflection->getValue() ? $process : null;
                }

                if ($Reflection->getAttributeProperty() === "out" && $neoxEncryptor->getId()) {
                    $propertyName = $Reflection->getPropertyName();
                    $content      = json_decode($neoxEncryptor->getContent(), false, 512, JSON_THROW_ON_ERROR);
                    $value        = isset($content->$propertyName);
                    $process      = $value ? $this->isSerialized(
                        $this->encryptor->decrypt($content->$propertyName)
                    ) : $Reflection->getValue();
                };
                //                $this->fieldCurentState = $process;
                $Reflection->getProperty()->setValue($entity, $process);
            }

            $this->logger("dec", $entity, $Reflection);
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
            $this->neoxDoctrineTools->neoxStats[ "wasaaaa" ] = 0; //-> Yes i know ! feel strange but we need this !!
            $this->neoxDoctrineTools->neoxStats[ "Decrypt" ] = 0;
            $this->neoxDoctrineTools->neoxStats[ "Encrypt" ] = 0;
            if ($Entity = $this->encryptor->entityManager->getRepository($entity)->findall()) {
                /**
                 * Important : Reset the listeners !! it will loop for ever !!
                 * THIS should affect only this instance of DoctrineEncryptor
                 **/

                foreach ($Entity as $item) {
                    if ($action === "Decrypt") {
                        //                        ++$this->neoxStats[ "Decrypt" ];
                        $this->entityCurentState = "Decrypt";
                        // check if property is encrypted in NeoxEncryptor if yes delete
                        $neoxEncryptor = $this->encryptor->getEncryptorId($item);
                        if ($neoxEncryptor->getid()) {
                            $this->encryptor->entityManager->remove($neoxEncryptor);
                        }
                    } else {
                        //                        ++$this->neoxStats[ "Encrypt" ];
                        $p = $this->encrypt($item, "convert", false);
                    }
                }
                $this->neoxDoctrineTools->EventListenerAll();
                $this->neoxDoctrineTools->EventListenerOnFlush();
                $this->encryptor->entityManager->flush();
                $this->neoxDoctrineTools->EventListenerAll(true);
                $this->neoxDoctrineTools->EventListenerOnFlush(true);
            }
        }

        /**
         * @throws ReflectionException
         * @throws JsonException
         */
        public function getTwigDecrypt($entity, string $action): string
        {
            $p       = "get{$action}";
            $process = $entity->$p();
            // Delete the namespace 'Proxies\__CG__\'
            $className = str_replace('Proxies\__CG__\\', '', $entity::class);

            if (self::isSupport($className)) {
                $this->neoxDoctrineTools->EventListenerAll();
                $neoxEncryptor = $this->encryptor->getEncryptorId($entity);
                $this->neoxDoctrineTools->EventListenerAll(true);

                //                if( $neoxEncryptor->getId() ) {
                //                    $propertyName = $action;
                //                    $content      = json_decode( $neoxEncryptor->getContent(), false, 512, JSON_THROW_ON_ERROR );
                //                    $value        = isset( $content->$propertyName );
                //                    $process      = $value ? $this->isSerialized( $this->encryptor->decrypt( $content->$propertyName ) ) : null;
                //                }
                if ($neoxEncryptor->getId()) {
                    $propertyName = $action;
                    $content      = json_decode($neoxEncryptor->getContent(), false, 512, JSON_THROW_ON_ERROR);
                    $value        = $content->$propertyName ?? null;
                    $process      = $value ? $this->isSerialized($this->encryptor->decrypt($value)) : null;
                }
            }

            return $process;
        }

        /**
         * @throws ReflectionException
         */
        public function getReflection($entity): array
        {
            $r[ $entity::class ] = [];
            $reflectorName       = new ReflectionClass($entity);

            // filter on "neoxEncryptor" attribute
            // https://www.php.net/manual/fr/reflectionclass.getattributes.php
            $neoxEncryptorProperties = array_filter(
                $reflectorName->getProperties(), function ($property) {
                return count($property->getAttributes(NeoxEncryptor::class)) > 0;
            }
            );

            foreach ($neoxEncryptorProperties as $property) {
                $object = new ReflectionInfo();

                $object->setProperty($property);
                //Take the first attribute found
                $encryptAttribute = $property->getAttributes(NeoxEncryptor::class)[ 0 ];
                $attriNInstance   = $encryptAttribute->newInstance();
                $object->setAttributeProperty($attriNInstance->build);
                $object->setAttributeFacker($attriNInstance->facker);
                $object->setType($property->getType()->getName());
                $object->setPropertyName($property->getName());
                $object->setValue($property->getValue($entity));
                $r[ $entity::class ][] = $object;
            }
            return $r;
        }

        public function getEncryptor(): void
        {
            $this->encryptor = $this->neoxDoctrineFactory->buildEncryptor();
        }

        public function encryptOFF(): bool
        {
            return $this->neoxDoctrineFactory->parameterBag->get("doctrine_encryptor.encryptor_off") ?? true;
        }

        public function isSerialized($data)
        {
            //            $unserializedData = @unserialize( $data ); //
            //            if( $unserializedData !== false && ( is_array( $unserializedData ) || is_object( $unserializedData ) ) ) {
            //                return $unserializedData;
            //            }
            //            $p = !$unserializedData ? $data : $unserializedData;


            return ($unserializedData = @unserialize($data)) !== false ? $unserializedData : $data;
        }

        /**
         * @param       $entity
         * @param mixed $Reflection
         *
         * @return void
         * @throws ReflectionException
         */
        private function logger($action, $entity, mixed $Reflection): void
        {
            $className   = (new ReflectionClass($this->encryptor))->getShortName();
            $entityClass = $entity::class;
            $this->logger->info("$action : $className : $entityClass | " . $Reflection->getAttributeProperty());
        }

    }