<?php
    
    namespace DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\OpenSSL;
    
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\DoctrineEncryptorService;
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
    
    class OpenSSLTools
    {
        const PRIVATE_KEY   = 'doctrine_encryptor_private.pem';
        const PUBLIC_KEY    = 'doctrine_encryptor_public.pem';
        const PATH_FOLDER   = '/config/OpenSSL/';
        const PREFIX        = 'NEOX';
        
        public function __construct(readonly ParameterBagInterface $parameterBag)
        {
        }
        
        public static function buildAsymetricKey(string $algoOpen, string $KeyLengths): void
        {    
            $directory      = dirname(__DIR__, 6). '/config/OpenSSL/';
            $privateKeyFile = $directory . self::PRIVATE_KEY;
            $publicKeyFile  = $directory . self::PUBLIC_KEY;
            
            // Verify that the directory is writable
            if (!is_writable($directory)) {
                throw new \RuntimeException("Directory $directory is not writable.");
            }
            
            // Check if the keystores already exist
            if (file_exists($privateKeyFile) || file_exists($publicKeyFile)) {
                throw new \RuntimeException("Key files already exist.");
            }
            
            if ($algoOpen === 'OPENSSL_KEYTYPE_EC') {
                $keypair = openssl_pkey_new(array(
                    'private_key_type'  => OpenSSLAlgo::getValue($algoOpen),
                    'curve_name'        => $KeyLengths,
                ));
            } else {
                $keypair = openssl_pkey_new(array(
                    'private_key_bits' => (int)$KeyLengths,
                    'private_key_type' => OpenSSLAlgo::getValue($algoOpen),
                ));
            }
            
            if ($keypair === false) {
                throw new \RuntimeException("Failed to generate key pair.");
            }
            
            openssl_pkey_export($keypair, $private_key);
            $key_details    = openssl_pkey_get_details($keypair);
            $public_key     = $key_details['key'];
            
            // Write keys to files
            if (file_put_contents($privateKeyFile, $private_key) === false) {
                throw new \RuntimeException("Failed to write private key to file.");
            }
            if (file_put_contents($publicKeyFile, $public_key) === false) {
                // Delete private key if writing public key fails
                unlink($privateKeyFile);
                throw new \RuntimeException("Failed to write public key to file.");
            }
            
            echo "Private key saved in: $privateKeyFile\n";
            echo "Public key saved in: $publicKeyFile\n";
        }

        public static function getPwsSalt(): array{
            $directory              = dirname(__DIR__, 6). self::PATH_FOLDER;
            $privateKey             = $directory . self::PRIVATE_KEY;
            $publicKey              = $directory . self::PUBLIC_KEY;
            
            $keyContent["pws"]      = hash_hmac_file('gost-crypto', 'file://' .$privateKey, 'test');
            $keyContent["salt"]     = hash_hmac_file('gost-crypto', 'file://' .$publicKey, 'test');
            
            return $keyContent;
        }
        
        public static function builderIndice($entity): string
        {
            $directory              = dirname(__DIR__, 6). self::PATH_FOLDER;
            $privateKey             = $directory . self::PRIVATE_KEY;
            $publicKey              = $directory . self::PUBLIC_KEY;
            $message                = $entity::class . substr($publicKey, 4, 4) . $entity->getId();
            return hash_hmac('gost-crypto', $message , $publicKey);
        }
        
        public static function getDirectoryOpenSSL(): string
        {
            $directory   = dirname(__DIR__, 6). self::PATH_FOLDER;
            
            if (!is_dir($directory)) {
                if (!mkdir($directory, 0777, true) && !is_dir($directory)) {
                    throw new \RuntimeException(sprintf('Directory "%s" was not created', $directory));
                }
            }
            
            return $directory;
        }
        
        public static function isBase64($string): bool
        {
//            $type = gettype($string); // to make sure that $string is a string
            if (DoctrineEncryptorService::callBackType($string, true)) {
                return false;
            }
            $decodedString = base64_decode($string, true);
            // Vérifie si le décodage a réussi et que la chaîne décodée est identique à la chaîne d'origine
            return $decodedString !== false && base64_encode($decodedString) === $string; // La chaîne n'est pas encodée en Base64
        }
    }