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
    
    class OpenSSLAsymEncryptor implements EncryptorInterface
    {
        
        
        public function __construct(
            readonly ParameterBagInterface $parameterBag,
            readonly EntityManagerInterface $entityManager,
            readonly NeoxDoctrineTools $neoxDoctrineTools,
        )
        {
        }
        
        /**
         * @param string $plainText
         *
         * @return string
         */
        public function encrypt($plainText): string
        {
            $KEYS   = $this->getEncryptionKey();

            try {
                // Encrypt plain text
                if (!openssl_private_encrypt($plainText, $encryptedMessage, $KEYS["privateKey"])) {
                    throw new \Exception('Encryption failed.');
                }
                
                // Return base64 encoded ciphertext
                return base64_encode($encryptedMessage);
            } catch (\Exception $e) {
                // Error
                throw new \Exception('Error during encryption: ' . $e->getMessage() . "\n\n" . openssl_error_string() . "\n\n");
            }
        }
        
        /**
         * @param $plainText
         *
         * @return string
         */
        public function decrypt($plainText): string
        {
            $KEYS   = $this->getEncryptionKey();
            openssl_private_decrypt($plainText, $decryptedMessage, $KEYS["privateKey"]);
            return $decryptedMessage ?? $plainText;
        }
        
        /**
         * @param string $msg
         *
         * @return array
         */
        public function getEncryptionKey(string $msg = ""): array
        {
            $Directory              = OpenSSLTools::getDirectoryOpenSSL();
            $PRIVATE_KEY            = $Directory . OpenSSLTools::PRIVATE_KEY;
            $PUBLIC_KEY             = $Directory . OpenSSLTools::PUBLIC_KEY;
            
            try {
                $privateKey = openssl_pkey_get_private(file_get_contents($PRIVATE_KEY));
                if ($privateKey === false) {
                    throw new \Exception('Unable to recover private key.');
                }
                $publicKey = openssl_pkey_get_public(file_get_contents($PUBLIC_KEY));
                if ($publicKey === false) {
                    throw new \Exception('Unable to recover private key.');
                }
            } catch (\Exception $e) {
                // Error
                throw new \Exception('Error during encryption: ' . $e->getMessage() . "\n\n" . openssl_error_string() . "\n\n");
            }
            
            return [
                'privateKey'    => $privateKey,
                'publicKey'     => $publicKey
            ];
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