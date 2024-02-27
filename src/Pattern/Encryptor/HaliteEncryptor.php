<?php
    
    namespace DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\Encryptor;

    use Doctrine\ORM\EntityManagerInterface;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Entity\NeoxEncryptor;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\EncryptorInterface;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\OpenSSL\OpenSSLTools;
    use ParagonIE\Halite\Alerts\CannotPerformOperation;
    use ParagonIE\Halite\Alerts\InvalidDigestLength;
    use ParagonIE\Halite\Alerts\InvalidKey;
    use ParagonIE\Halite\Alerts\InvalidMessage;
    use ParagonIE\Halite\Alerts\InvalidSalt;
    use ParagonIE\Halite\Alerts\InvalidSignature;
    use ParagonIE\Halite\Alerts\InvalidType;
    use ParagonIE\Halite\Halite;
    use ParagonIE\Halite\KeyFactory;
    use ParagonIE\Halite\Symmetric\Crypto;
    use ParagonIE\Halite\Util;
    use ParagonIE\HiddenString\HiddenString;
    use SodiumException;
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
    
    
    class HaliteEncryptor implements EncryptorInterface
    {
        
        public function __construct(private readonly ParameterBagInterface $parameterBag, readonly EntityManagerInterface $entityManager)
        {
        }
        
        /**
         * @throws InvalidType
         * @throws InvalidSignature
         * @throws InvalidDigestLength
         * @throws SodiumException
         * @throws InvalidSalt
         * @throws InvalidKey
         * @throws InvalidMessage
         * @throws CannotPerformOperation
         */
        public function encrypt($field): string
        {
            $Halite = Halite::VERSION_PREFIX;
            // if it is already Encrypted then Decrypted
            if (!preg_match("/^{$Halite}/", $field)) {
                [$encryptionKey, $message] = $this->getEncryptionKey($field);
                return Crypto::encrypt($message, $encryptionKey);
            }
            return $field;
        }
        
        /**
         * @throws InvalidType
         * @throws InvalidSignature
         * @throws InvalidDigestLength
         * @throws SodiumException
         * @throws InvalidSalt
         * @throws InvalidKey
         * @throws InvalidMessage
         * @throws CannotPerformOperation
         */
        public function decrypt($field): string
        {
            $Halite = Halite::VERSION_PREFIX;
            
            // if it is already Encrypted then Decrypted
            if (preg_match("/^{$Halite}/", $field)) {
                [$encryptionKey, $message] = $this->getEncryptionKey($field);
                return Crypto::decrypt($message->getString(), $encryptionKey)->getString();
            }
            return $field;
        }
        
//        /**
//         * @param string $msg
//         *
//         * @return array
//         */
//        public function getEncryptionKey(string $msg = ""): array
//        {
//            $secret                     = OpenSSLTools::getPwsSalt();
//            $ivLength                   = openssl_cipher_iv_length($this->cipherAlgorithm);
//            $EncryptionKey["iv"]        = substr($secret['salt'], 0, $ivLength);
//            $EncryptionKey["pws"]       = substr($secret['pws'], 0, 32);
//
//            return $EncryptionKey;
//        }
        /**
         * @throws InvalidType
         * @throws InvalidKey
         * @throws SodiumException
         * @throws InvalidSalt
         */
        public function getEncryptionKey(string $msg = ""): array
        {
            $secret         = OpenSSLTools::getPwsSalt();
            $key            = new HiddenString($secret['pws']);
            $message        = new HiddenString($msg);
            $encryptionKey  = KeyFactory::deriveEncryptionKey($key, substr($secret['salt'], 0, 16));

            return [$encryptionKey, $message];
        }
        
        /**
         * @param $entity
         *
         * @return NeoxEncryptor|null
         */
        public function getEncryptorId($entity): ?NeoxEncryptor
        {
            // ff5d400f96d533dfda3018dc7dce45f5
            $indice     = OpenSSLTools::builderIndice($entity);
            if ( !$encryptor = $this->entityManager->getRepository(NeoxEncryptor::class)->findOneBy(['data' => $indice]) ){
                $encryptor = new NeoxEncryptor();
                $encryptor->setData($indice);
            };
            
            return $encryptor;
        }
//        /**
//         * @throws InvalidType
//         * @throws InvalidKey
//         * @throws CannotPerformOperation
//         * @throws InvalidSalt
//         * @throws SodiumException
//         */
//        public function getEncryptorId( $entity ): ?NeoxEncryptor
//        {
//            // ff5d400f96d533dfda3018dc7dce45f5
//            $salt                   = $this->getSalt();
//            $reflectionClass        = new \ReflectionClass($entity);
//            // here secure by using salt and entity | your slat should be unique and will renforce security
//            $key                    = new HiddenString($reflectionClass->getshortName() . substr($salt, 4, 4) . $entity->getId());
//            $encryptionKey          = KeyFactory::deriveEncryptionKey($key, $salt);
//
//            /**
//             * here secure by using salt and entity | your salt should be unique (Defined in doctrine_encryptor.yaml) and will renforce security
//             * This ligne will create dynamically link between neoxEncryptor / entity
//             * without using doctrine it will by match hard to link neoxEncryptor / entity !!!
//             **/
//            $indice                 = Util::keyed_hash($reflectionClass->getName(). substr($salt, 3, 5) . $entity->getId(), $encryptionKey,16);
//
//            if ( !$encryptor = $this->entityManager->getRepository(NeoxEncryptor::class)->findOneBy(['data' => $indice]) ){
//                $encryptor = new NeoxEncryptor();
//                $encryptor->setData($indice);
//            };
//
//            return $encryptor;
//        }
//
//        private function getSalt(): \UnitEnum|float|array|bool|int|string|null
//        {
//            return $this->parameterBag->get('doctrine_encryptor.encryptor_salt');
//        }
    }