<?php
    /********************** OpenSSLEncryptor.php ***********************************************
    * CODE EXPERIMENTAL - DO NOT USE IN PRODUCTION
    *******************************************************************************************/
    
    namespace DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\Encryptor;

    use Doctrine\ORM\EntityManagerInterface;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Entity\NeoxEncryptor;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\EncryptorInterface;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\NeoxDoctrineTools;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\OpenSSL\OpenSSLTools;
    use ParagonIE\Halite\Alerts\CannotPerformOperation;
    use ParagonIE\Halite\Alerts\InvalidKey;
    use ParagonIE\Halite\Alerts\InvalidSalt;
    use ParagonIE\Halite\Alerts\InvalidType;
    use ParagonIE\Halite\KeyFactory;
    use ParagonIE\Halite\Util;
    use ParagonIE\HiddenString\HiddenString;
    use SodiumException;
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
    
    class OpenSSLSymEncryptor implements EncryptorInterface
    {
        private const HASH_ALGORITHM = 'whirlpool';
        
        private $secretKey;
        private $cipherAlgorithm = 'ChaCha20';  // aes-256-cbc
        private $base64;
        private $formatBase64Output;
        private $randomPseudoBytes;
        private $iv;
        private $ivLength;
        
        public function __construct(readonly ParameterBagInterface $parameterBag, readonly EntityManagerInterface $entityManager, readonly NeoxDoctrineTools $neoxDoctrineTools)
        {
        }
        
        /**
         * @param string $plainText
         *
         * @return string
         */
        public function encrypt($plainText): string
        {
//            if (OpenSSLTools::isBase64($plainText)) {
                $secret         = $this->getEncryptionKey();
                $cipherText     = openssl_encrypt($plainText, $this->cipherAlgorithm, $secret['pws'], OPENSSL_RAW_DATA, $secret['iv']);
                $plainText      = base64_Encode($cipherText);
//            }
            return $plainText;
        }
        
        /**
         * @param $plainText
         *
         * @return string
         */
        public function decrypt($plainText): string
        {
            if (OpenSSLTools::isBase64($plainText)) {
                $secret     = $this->getEncryptionKey();
                $cipherText = base64_decode($plainText);
                $plainText  = openssl_decrypt($cipherText, $this->cipherAlgorithm, $secret['pws'], OPENSSL_RAW_DATA, $secret['iv']);
            }
        
            return $plainText;
        }
        
        /**
         * @param string $msg
         *
         * @return array
         */
        public function getEncryptionKey(string $msg = ""): array
        {
            $secret                     = OpenSSLTools::getPwsSalt();
            $ivLength                   = openssl_cipher_iv_length($this->cipherAlgorithm);
            $EncryptionKey["iv"]        = substr($secret['salt'], 0, $ivLength);
            $EncryptionKey["pws"]       = substr($secret['pws'], 0, 32);
            
            return $EncryptionKey;
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
    }