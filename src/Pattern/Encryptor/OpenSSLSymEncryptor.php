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
        private string $secretKey;
        private string $iv;
        

        public function __construct(readonly ParameterBagInterface $parameterBag, readonly EntityManagerInterface $entityManager, readonly NeoxDoctrineTools $neoxDoctrineTools)
        {
            $this->cipherAlgorithm = $parameterBag->get('doctrine_encryptor.encryptor_cipher_algorithm');
            $this->secretKey       = openSSLTools::getSecretBin();
            $this->iv              = openSSlTools::getIv($this->cipherAlgorithm);
        }

        /**
         * @param string $plainText
         *
         * @return string
         * @throws \Exception
         */
        public function encrypt($plainText): string
        {
            $cipherText = openssl_encrypt($plainText, $this->cipherAlgorithm, $this->secretKey, OPENSSL_RAW_DATA, $this->iv);
            $this->neoxDoctrineTools->setCountEncrypt(($cipherText ? 1 : 0  ));
            $plainText  = base64_Encode($cipherText);

            if (!$cipherText) {
                throw new \Exception("Unable to encrypt message. {$plainText} - is this string !?? (knowledge issue).  Your data havent been encrypted.");
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
            $cipherText = base64_decode($plainText);
            $plainText  = openssl_decrypt($cipherText, $this->cipherAlgorithm, $this->secretKey, OPENSSL_RAW_DATA, $this->iv);
            $this->neoxDoctrineTools->setCountDecrypt(($cipherText? 1 : 0  ));
            return $plainText;
        }
        
        /**
         * @param string $msg
         *
         * @return array
         */
        public function getEncryptionKey(string $msg = ""): array
        {
            // .....
        }
        
        /**
         * @param $entity
         *
         * @return NeoxEncryptor|null
         */
        public function getEncryptorId($entity): ?NeoxEncryptor
        {
            // ff5d400f96d533dfda3018dc7dce45f5
            $indice     = OpenSSLTools::builderIndice($entity, $this->secretKey);
            return $this->entityManager->getRepository(NeoxEncryptor::class)->findOneBy(['data' => $indice]) ?: (new NeoxEncryptor())->setData($indice);
        }
    }