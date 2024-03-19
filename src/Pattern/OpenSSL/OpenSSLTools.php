<?php

    namespace DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\OpenSSL;

    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\DoctrineEncryptorService;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\EncryptorInterface;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\OpenSSLSymEncryptor;
    use ParagonIE\Halite\Halite;
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

    class OpenSSLTools
    {
        public const PRIVATE_KEY = 'openSSL_private.pem';
        public const PUBLIC_KEY  = 'openSSL_public.pem';
        public const PASS_KEY    = 'openSSL.key';
        public const ENCRYPT_BIN = 'openSSL.bin';
        public const PATH_FOLDER = '/config/doctrine-encryptor/';
        const        PREFIX      = 'doctrine_encryptor.encryptor_';
        private string        $SecretBin;
        private static string $SECRET;


        public function __construct(readonly ParameterBagInterface $parameterBag)
        {
        }

        public static function buildOpenSSLKey(EncryptorInterface $t, string $algoOpen, string $KeyLengths): void
        {
            // RESTE all key ======== !!!
            $t->secureKey->resteAllKey("openSSL");
            // RESTE all key ======== !!!


            // generate encryptBin
            $keyEncryptBin  = self::setKeyRandom32();
            // generate pass
            $keypass        = self::setKeyRandom32();

            if ($algoOpen === 'OPENSSL_KEYTYPE_EC') {
                $keypair = openssl_pkey_new(array(
                    'private_key_type' => OpenSSLAlgo::getValue($algoOpen),
                    'curve_name'       => $KeyLengths,
                    //                    'encrypt_key'               => true,
                    //                    'encrypt_key_cipher_string' => $keypass
                ));
            } else {
                $keypair = openssl_pkey_new(array(
                    'private_key_bits' => (int)$KeyLengths,
                    'private_key_type' => OpenSSLAlgo::getValue($algoOpen),
                    //                    'encrypt_key'               => true,
                    //                    'encrypt_key_cipher_string' => $keypass
                ));
            }

            if ($keypair === false) {
                throw new \RuntimeException("Failed to generate key pair.");
            }
            //            $keypass
            openssl_pkey_export($keypair, $private_key);
            $key_details = openssl_pkey_get_details($keypair);
            $public_key  = $key_details[ 'key' ];

            // generate encryptBin Asymc
            openssl_public_encrypt($keyEncryptBin, $encryptedAESKey, $public_key);

            if (self::setNameKey($t, self::ENCRYPT_BIN, $encryptedAESKey) === false) {
                throw new \RuntimeException("Failed to write encrypt key to file.");
            }

            if (self::setNameKey($t, self::PASS_KEY, $keypass) === false) {
                throw new \RuntimeException("Failed to write passphrase key to file.");
            }

            // Write keys to files
            if (self::setNameKey($t, self::PRIVATE_KEY, $private_key) === false) {
                throw new \RuntimeException("Failed to write private key to file.");
            }
            if (self::setNameKey($t, self::PUBLIC_KEY, $public_key) === false) {
                throw new \RuntimeException("Failed to write public key to file.");
            }
        }

        // depreciated
//        public static function getPwsSalt(): array
//        {
//            $directory  = dirname(__DIR__, 6) . self::PATH_FOLDER;
//            $privateKey = $directory . self::PRIVATE_KEY;
//            $publicKey  = $directory . self::PUBLIC_KEY;
//
//            $keyContent[ "pws" ]  = hash_hmac_file('gost-crypto', 'file://' . $privateKey, 'test');
//            $keyContent[ "salt" ] = hash_hmac_file('gost-crypto', 'file://' . $publicKey, 'test');
//
//            return $keyContent;
//        }

        // depreciated
//        public static function deleleteAsymetricKey()
//        {
//            $directory      = dirname(__DIR__, 6) . SELF::PATH_FOLDER;
//            $privateKeyFile = $directory . self::PRIVATE_KEY;
//            $publicKeyFile  = $directory . self::PUBLIC_KEY;
//
//            // Verify that the directory is writable
//            if (!is_writable($directory)) {
//                throw new \RuntimeException("Directory $directory is not writable.");
//            }
//
//            // Check if the keystores already exist
//            if (file_exists($privateKeyFile) || file_exists($publicKeyFile)) {
//                unlink($privateKeyFile);
//                unlink($publicKeyFile);
//                echo "Key files deleted\n";
//                return true;
//            }
//            return false;
//        }

        public static function builderIndice($entity, $sercretBin = null): string
        {
            // Delete the namespace 'Proxies\__CG__\'
            $className = str_replace('Proxies\__CG__\\', '', $entity::class);
            $sercetBin = $sercretBin; // self::getSecretBin() ;
            $message   = $className . substr($sercetBin, 4, 6) . $entity->getId();
            return hash_hmac('gost-crypto', $message, $sercetBin);
        }

        // depreciated
//        public static function getDirectoryOpenSSL(): string
//        {
//            $directory = dirname(__DIR__, 6) . self::PATH_FOLDER;
//
//            if (!is_dir($directory)) {
//                if (!mkdir($directory, 0777, true) && !is_dir($directory)) {
//                    throw new \RuntimeException(sprintf('Directory "%s" was not created', $directory));
//                }
//            }
//
//            return $directory;
//        }

        public static function isBase64($string, mixed $type = null): bool
        {
            $Halite = Halite::VERSION_PREFIX;
            // if it is already Encrypted then Decrypted
            if (is_string($string) && preg_match("/^{$Halite}/", $string)) {
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
            return $decodedString !== false && base64_encode(
                    $decodedString
                ) === $string; // La chaîne n'est pas encodée en Base64
        }

        public static function isSerialized($data)
        {
            $unserializedData = @unserialize($data);
            if ($unserializedData !== false && (is_array($unserializedData) || is_object($unserializedData))) {
                return $unserializedData;
            }
            return $unserializedData == false ? $data : $unserializedData;
        }

        private static function setKeyRandom32(): string
        {
            $keyBytes = openssl_random_pseudo_bytes(32, $cstrong);
            return bin2hex($keyBytes);
        }

        public static function getSecretBin($t): ?string
        {
            // TODO : not call all time but only if needed !!!
            // when we will intriduce external storiged it will not be optimze !!
            // charge ussing cache Wooooo
            if( $t->parameterBag->get('doctrine_encryptor.encryptor_cache') ){
                // read cache [redis]
                $encryptedAESKey = self::getNameKey($t, self::ENCRYPT_BIN);
                $privateKeyPEM   = self::getNameKey($t, self::PRIVATE_KEY);
                $publicKeyPEM    = self::getNameKey($t, self::PUBLIC_KEY);
                $passKey         = self::getNameKey($t, self::PASS_KEY );  // this line is add to avoid error with old version.
            }else{
                // read "abord" [gaufrette]
                $encryptedAESKey = self::getNameKeyGaufrette($t, self::ENCRYPT_BIN);
                $privateKeyPEM   = self::getNameKeyGaufrette($t, self::PRIVATE_KEY);
                $publicKeyPEM    = self::getNameKeyGaufrette($t, self::PUBLIC_KEY);
                $passKey         = self::getNameKeyGaufrette($t, self::PASS_KEY );
            };


            // return null if not found
            if ( !$encryptedAESKey || !$privateKeyPEM ) {
                return null;
            }

            // Decrypt the AES key with the RSA public key
            openssl_private_decrypt($encryptedAESKey, $aesKey, $privateKeyPEM);

            return $aesKey;
        }

        public static function getIv($t): string
        {
            $ivLength = openssl_cipher_iv_length($t->cipherAlgorithm);
            return substr(self::getSecretBin($t), 0, $ivLength);
        }

        private static function setNameKey(EncryptorInterface $t, string $key, string $content): ?string
        {
            return $t->secureKey->setKeyName($key, $content);
        }

        private static function getNameKey(EncryptorInterface $t, string $key): ?string
        {
            return $t->secureKey->getKeyName($key);
        }

        private static function getNameKeyGaufrette(EncryptorInterface $t, string $key): ?string
        {
            return $t->secureKey->getKeyNameGaufrette($key);
        }


    }