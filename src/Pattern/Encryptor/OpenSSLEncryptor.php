<?php
    /********************** OpenSSLEncryptor.php ***********************************************
    * CODE EXPERIMENTAL - DO NOT USE IN PRODUCTION
    *******************************************************************************************/
    
    namespace DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\Encryptor;

    use Doctrine\ORM\EntityManagerInterface;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Entity\NeoxEncryptor;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\EncryptorInterface;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\NeoxDoctrineTools;
    use ParagonIE\Halite\Alerts\CannotPerformOperation;
    use ParagonIE\Halite\Alerts\InvalidKey;
    use ParagonIE\Halite\Alerts\InvalidSalt;
    use ParagonIE\Halite\Alerts\InvalidType;
    use ParagonIE\Halite\KeyFactory;
    use ParagonIE\Halite\Util;
    use ParagonIE\HiddenString\HiddenString;
    use SodiumException;
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
    
    class OpenSSLEncryptor implements EncryptorInterface
    {
        private const HASH_ALGORITHM = 'sha256';
        
        private $secretKey;
        private $cipherAlgorithm;
        private $base64;
        private $formatBase64Output;
        private $randomPseudoBytes;
        private $iv;
        private $ivLength;
        
        public function __construct(readonly ParameterBagInterface $parameterBag, readonly EntityManagerInterface $entityManager, readonly NeoxDoctrineTools $neoxDoctrineTools)
        {
        }

        
        /**
         * @param string $secretIv
         */
        public function buildSecretIv(string $secretIv): void
        {
            $ivLength   = openssl_cipher_iv_length($this->cipherAlgorithm);
            $secretIv   = $this->randomPseudoBytes ? openssl_random_pseudo_bytes($ivLength) : $secretIv;
            $key        = hash_hmac(self::HASH_ALGORITHM, $secretIv, $this->secretKey, true);
            $this->iv   = substr($key, 0, $ivLength);
            
        }
        
        /**
         * @param string $plainText
         *
         * @return string
         */
        public function encrypt($plainText): string
        {
            $encrypted      = openssl_encrypt($plainText, $this->cipherAlgorithm, $this->secretKey, OPENSSL_RAW_DATA, $this->iv);
            $encrypted      = $this->iv . $encrypted;
            
            return $this->base64 ? $this->base64Encode($encrypted) : $encrypted;
        }
        
        /**
         * @param $plainText
         *
         * @return string
         */
        public function decrypt($plainText): string
        {
            $iv         = substr($plainText, 0, $this->ivLength);
            $raw        = substr($plainText, $this->ivLength);
            $decrypted  = openssl_decrypt($raw, $this->cipherAlgorithm, $this->secretKey, OPENSSL_RAW_DATA, $iv);
            
            if ($decrypted === false) {
                // Error !!
                throw new \RuntimeException('Erreur lors du déchiffrement.');
            }
            return trim($decrypted);
        }
        
        /**
         * @param string $data
         *
         * @return string
         */
        private function base64Encode(string $data): string
        {
            return $this->formatBase64Output ?
                rtrim(strtr(base64_encode($data), '+/', '-_'), '=') :
                base64_encode($data);
        }
        
        /**
         * @param string $data
         *
         * @return string
         */
        private function base64Decode(string $data): string
        {
            return $this->formatBase64Output ?
                base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT), true) :
                base64_decode($data, true);
        }
        
        /**
         * @param string $msg
         *
         * @return array
         */
        public function getEncryptionKey(string $msg = ""): array
        {
            // TODO: Implement getEncryptionKey() method.
        }
        
        /**
         * @param $entity
         *
         *  @throws InvalidType
         *  @throws InvalidKey
         *  @throws CannotPerformOperation
         *  @throws InvalidSalt
         *  @throws SodiumException
         *
         * @return NeoxEncryptor|null
         */
        public function getEncryptorId($entity): ?NeoxEncryptor
        {
            // ff5d400f96d533dfda3018dc7dce45f5
            $salt                   = $this->getSalt();
            $reflectionClass        = new \ReflectionClass($entity);
            // here secure by using salt and entity | your slat should be unique and will renforce security
            $key                    = new HiddenString($reflectionClass->getshortName() . substr($salt, 4, 4) . $entity->getId());
            $encryptionKey          = KeyFactory::deriveEncryptionKey($key, $salt);
            
            /**
             * here secure by using salt and entity | your salt should be unique (Defined in doctrine_encryptor.yaml) and will renforce security
             * This ligne will create dynamically link between neoxEncryptor / entity
             * without using doctrine it will by match hard to link neoxEncryptor / entity !!!
             **/
            $indice                 = Util::keyed_hash($reflectionClass->getName(). substr($salt, 3, 5) . $entity->getId(), $encryptionKey,16);
            
            if ( !$encryptor = $this->entityManager->getRepository(NeoxEncryptor::class)->findOneBy(['data' => $indice]) ){
                $encryptor = new NeoxEncryptor();
                $encryptor->setData($indice);
            };
            
            return $encryptor;
        }
    }