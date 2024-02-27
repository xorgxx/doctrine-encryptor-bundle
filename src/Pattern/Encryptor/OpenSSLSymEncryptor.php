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
            if (!$this->isBase64($plainText)) {
                $secret         = $this->getEncryptionKey();
                $cipherText     = openssl_encrypt($plainText, $this->cipherAlgorithm, $secret['pws'], OPENSSL_RAW_DATA, $secret['iv']);
                $plainText      = base64_Encode($cipherText);
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
            if ($this->isBase64($plainText)) {
                $secret     = $this->getEncryptionKey();
                $cipherText = base64_decode($plainText);
                $plainText  = openssl_decrypt($cipherText, $this->cipherAlgorithm, $secret['pws'], OPENSSL_RAW_DATA, $secret['iv']);
            }
        
            return $plainText;
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
        
        private function isBase64($string) {
            // Décode la chaîne en Base64
            $decodedString = base64_decode($string, true);
            
            // Vérifie si le décodage a réussi et que la chaîne décodée est identique à la chaîne d'origine
            if ($decodedString !== false && base64_encode($decodedString) === $string) {
                return true; // La chaîne est encodée en Base64
            } else {
                return false; // La chaîne n'est pas encodée en Base64
            }
        }
    }