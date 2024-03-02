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
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
    
    class OpenSSLSymEncryptor implements EncryptorInterface
    {
        /**
         * AES (Advanced Encryption Standard) :
         * AES-128-CBC : IV de 16 octets
         * AES-192-CBC : IV de 16 octets
         * AES-256-CBC : IV de 16 octets
         *
         * DES (Data Encryption Standard) :
         * DES-CBC : IV de 8 octets
         *
         * 3DES (Triple DES) :
         * DES-EDE-CBC : IV de 8 octets
         *
         * Blowfish :
         * BF-CBC : IV de 8 octets
         *
         * RC4 (Rivest Cipher 4) :
         * RC4 : Pas d'IV (le chiffrement RC4 ne nécessite pas d'IV)
         *
         * ChaCha20 :
         * ChaCha20 : IV de 8 octets
         *
         * Camellia :
         * Camellia-128-CBC : IV de 16 octets
         * Camellia-192-CBC : IV de 16 octets
         * Camellia-256-CBC : IV de 16 octets
         *
         * SEED :
         * SEED-CBC : IV de 16 octets
         */
        private string $cipherAlgorithm = 'Camellia-256-CBC';
        
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
            $secret     = $this->getEncryptionKey();
            $cipherText = openssl_encrypt($plainText, $this->cipherAlgorithm, $secret['pws'], OPENSSL_RAW_DATA, $secret['iv']);
            $plainText  = base64_Encode($cipherText);
//            }
            if (!$cipherText) {
                throw new \Exception("Unable to encrypt message. {$plainText} - is this string !?? (knowladge issue).  Your data havent been encrypted.");
            }
            return $plainText;
        }
        
        /**
         * @param $plainText
         *
         * @return string
         */
        public function decrypt($plainText): string
        {
//            if (OpenSSLTools::isBase64($plainText)) {
            $secret     = $this->getEncryptionKey();
            $cipherText = base64_decode($plainText);
            $plainText  = openssl_decrypt($cipherText, $this->cipherAlgorithm, $secret['pws'], OPENSSL_RAW_DATA, $secret['iv']);
//            }
            return $plainText;
        }
        
        /**
         * @param string $msg
         *
         * @return array
         */
        public function getEncryptionKey(string $msg = ""): array
        {
            $secret               = OpenSSLTools::getPwsSalt();
            $ivLength             = openssl_cipher_iv_length($this->cipherAlgorithm);
            $EncryptionKey["iv"]  = substr($secret['salt'], 0, $ivLength);
            $EncryptionKey["pws"] = substr($secret['pws'], 0, 32);
            
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
            $indice = OpenSSLTools::builderIndice($entity);
            if (!$encryptor = $this->entityManager->getRepository(NeoxEncryptor::class)->findOneBy(['data' => $indice])) {
                $encryptor = new NeoxEncryptor();
                $encryptor->setData($indice);
            };
            
            return $encryptor;
        }
    }