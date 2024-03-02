<?php
    
    namespace DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\OpenSSL;
    
    enum OpenSSLAlgo: string
    {
        case OPENSSL_KEYTYPE_RSA = 'OPENSSL_KEYTYPE_RSA';
        case OPENSSL_KEYTYPE_DSA = 'OPENSSL_KEYTYPE_DSA';
        case OPENSSL_KEYTYPE_DH  = 'OPENSSL_KEYTYPE_DH';
        case OPENSSL_KEYTYPE_EC  = 'OPENSSL_KEYTYPE_EC';
        
        public static function getListe(): array
        {
            return [
                self::OPENSSL_KEYTYPE_RSA,
                self::OPENSSL_KEYTYPE_DSA,
                self::OPENSSL_KEYTYPE_DH,
                self::OPENSSL_KEYTYPE_EC,
            ];
        }
        
        public static function getValue(string $algo): int
        {
            $values = [
                self::OPENSSL_KEYTYPE_RSA->name => OPENSSL_KEYTYPE_RSA,
                self::OPENSSL_KEYTYPE_DSA->name => OPENSSL_KEYTYPE_DSA,
                self::OPENSSL_KEYTYPE_DH->name  => OPENSSL_KEYTYPE_DH,
                self::OPENSSL_KEYTYPE_EC->name  => OPENSSL_KEYTYPE_EC,
            ];
            
            // Vérifie si l'algorithme existe dans le tableau des valeurs
            if (array_key_exists($algo, $values)) {
                return $values[$algo];
            } else {
                // Gérer le cas où l'algorithme n'est pas trouvé
                throw new InvalidArgumentException("Invalid OpenSSL algorithm: $algo");
            }
        }
    }
    