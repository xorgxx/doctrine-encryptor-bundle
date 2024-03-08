<?php
    
    namespace DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\OpenSSL;
    
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\DoctrineEncryptorService;
    use ParagonIE\Halite\Halite;
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
    
    class OpenSSLTools
    {
        const PRIVATE_KEY = 'openSSL_private.pem';
        const PUBLIC_KEY  = 'openSSL_public.pem';
        const PATH_FOLDER = '/config/doctrine-encryptor/';
        const PREFIX      = 'NEOX';
        
        public function __construct(readonly ParameterBagInterface $parameterBag)
        {
        }
        
        public static function buildAsymetricKey(string $algoOpen, string $KeyLengths): void
        {
            $directory      = dirname(__DIR__, 6) . SELF::PATH_FOLDER;
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
                    'private_key_type' => OpenSSLAlgo::getValue($algoOpen),
                    'curve_name'       => $KeyLengths,
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
            $key_details = openssl_pkey_get_details($keypair);
            $public_key  = $key_details['key'];
            
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
        
        public static function getPwsSalt(): array
        {
            $directory  = dirname(__DIR__, 6) . self::PATH_FOLDER;
            $privateKey = $directory . self::PRIVATE_KEY;
            $publicKey  = $directory . self::PUBLIC_KEY;
            
            $keyContent["pws"]  = hash_hmac_file('gost-crypto', 'file://' . $privateKey, 'test');
            $keyContent["salt"] = hash_hmac_file('gost-crypto', 'file://' . $publicKey, 'test');
            
            return $keyContent;
        }

        public static function deleleteAsymetricKey(){
            $directory      = dirname(__DIR__, 6) . SELF::PATH_FOLDER;
            $privateKeyFile = $directory . self::PRIVATE_KEY;
            $publicKeyFile  = $directory . self::PUBLIC_KEY;

            // Verify that the directory is writable
            if (!is_writable($directory)) {
                throw new \RuntimeException("Directory $directory is not writable.");
            }

            // Check if the keystores already exist
            if (file_exists($privateKeyFile) || file_exists($publicKeyFile)) {
                unlink($privateKeyFile);
                unlink($publicKeyFile);
                echo "Key files deleted\n";
                return true;
            }
            return false;
        }
        
        public static function builderIndice($entity): string
        {
            // Delete the namespace 'Proxies\__CG__\'
            $className = str_replace('Proxies\__CG__\\', '', $entity::class);
            
            $directory  = dirname(__DIR__, 6) . self::PATH_FOLDER;
            $privateKey = $directory . self::PRIVATE_KEY;
            $publicKey  = $directory . self::PUBLIC_KEY;
            $message    = $className . substr($publicKey, 4, 4) . $entity->getId();
            return hash_hmac('gost-crypto', $message, $publicKey);
        }
        
        public static function getDirectoryOpenSSL(): string
        {
            $directory = dirname(__DIR__, 6) . self::PATH_FOLDER;
            
            if (!is_dir($directory)) {
                if (!mkdir($directory, 0777, true) && !is_dir($directory)) {
                    throw new \RuntimeException(sprintf('Directory "%s" was not created', $directory));
                }
            }
            
            return $directory;
        }

        public static function isBase64( $string, mixed $type = null): bool
        {
            if (base64_decode($string, true) === false)
            {
                return $string;
            }

            $Halite = Halite::VERSION_PREFIX;
            // if it is already Encrypted then Decrypted
            if (is_string($string)  && preg_match("/^{$Halite}/", $string)) {
                return true;
            }
            
            if ($string === null) {
                return false;
            }

            $decodedString = false;
            if (is_string($string) && base64_decode($string, true) !== false) {
                $decodedString = base64_decode($string, true);
            }
            
            // Vérifie si le décodage a réussi et que la chaîne décodée est identique à la chaîne d'origine
            return $decodedString !== false && base64_encode($decodedString) === $string; // La chaîne n'est pas encodée en Base64
        }
        
        public static function isSerialized($data)
        {
            $unserializedData = @unserialize($data);
            if ($unserializedData !== false && (is_array($unserializedData) || is_object($unserializedData))) {
                return $unserializedData;
            }
            return $unserializedData == false ? $data : $unserializedData;
        }
    }