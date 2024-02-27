<?php
    
    namespace DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\OpenSSL;
    
    class OpenSSLTools
    {
        
        public function __construct(readonly ParameterBagInterface $parameterBag)
        {
        }
        
        public static function buildAsymetricKey(string $algoOpen, string $KeyLengths): void
        {
            $rootPath   = dirname(__DIR__, 6);
            $directory  = $rootPath . '/config/OpenSSL/';
            if (!file_exists($directory)) {
                mkdir($directory, 0777, true); 
            }
            if ($algoOpen === 'OPENSSL_KEYTYPE_EC') {
                $keypair    = openssl_pkey_new(array(
                    'private_key_type'  => OpenSSLAlgo::getValue($algoOpen),
                    'curve_name'        => $KeyLengths,
                ));
            }else{
                $keypair    = openssl_pkey_new(array(
                    'private_key_bits' => (int)$KeyLengths,
                    'private_key_type' => OpenSSLAlgo::getValue($algoOpen),
                ));
            }
            
            openssl_pkey_export($keypair, $private_key);
            
            $key_details    = openssl_pkey_get_details($keypair);
            $public_key     = $key_details['key'];
            
            file_put_contents($directory . 'neox_doctrine_private.pem', $private_key);
            file_put_contents($directory . 'neox_doctrine_public.pem', $public_key);
            
            echo "Private key saved in: " . $directory . "neox_doctrine_private.pem\n";
            echo "Public key stored in: " . $directory . "neox_doctrine_public.pem\n";
        }
    }