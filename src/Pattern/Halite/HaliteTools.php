<?php

    namespace DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\Halite;

    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\EncryptorInterface;
    use ParagonIE\HiddenString\HiddenString;
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
    use ParagonIE\Halite\KeyFactory;
    use ParagonIE\Halite\Symmetric\EncryptionKey;
    use ParagonIE\Halite\EncryptionKeyPair;
    use ParagonIE\Halite\Util;

    class HaliteTools
    {
        const ENCRYPT_KEY = 'halite_encrypt.key';
        const PRIVATE_KEY = 'halite_private.pem';
        const PUBLIC_KEY  = 'halite_public.pem';
        const PATH_FOLDER = '/config/doctrine-encryptor/';
        const PREFIX      = 'NEOX';

        public function __construct(readonly ParameterBagInterface $parameterBag)
        {
        }


        public static function buildEncryptionKey(EncryptorInterface $t): void
        {
            // RESTE all key ======== !!!
            $t->secureKey->resteAllKey("halite");
            // RESTE all key ======== !!!

            $encKey = KeyFactory::generateEncryptionKey();
            $encKey = KeyFactory::export($encKey)->getString();
            self::setNameKey($t, self::ENCRYPT_KEY, $encKey);

            $keypair = KeyFactory::generateEncryptionKeyPair();

            $encKey  = KeyFactory::export($keypair->getSecretKey())->getString();
            self::setNameKey($t, self::PRIVATE_KEY, $encKey);

            $encKey = KeyFactory::export($keypair->getPublicKey())->getString();
            self::setNameKey($t, self::PUBLIC_KEY, $encKey);
        }

        public static function getEncryptionKey(): EncryptionKey
        {
            //            $directory = dirname(__DIR__, 6) . self::PATH_FOLDER . self::ENCRYPT_KEY;
            //            $u         = KeyFactory::loadEncryptionKey($directory);
            //            return $u;
        }

        public static function getSecretBin($t): ?EncryptionKey
        {
            // TODO : not call all time but only if needed !!!
            // when we will intriduce external storiged it will not be optimze !!
            // charge ussing cache Wooooo
            if ($t->parameterBag->get('doctrine_encryptor.encryptor_cache')) {
                // read cache [redis]
                $encryptedAESKey = self::getNameKey($t, self::ENCRYPT_KEY);
                $privateKeyPEM   = self::getNameKey($t, self::PRIVATE_KEY);
                $publicKeyPEM    = self::getNameKey($t, self::PUBLIC_KEY);
            } else {
                // read "abord" [gaufrette]
                $encryptedAESKey = self::getNameKeyGaufrette($t, self::ENCRYPT_KEY);
                $privateKeyPEM   = self::getNameKeyGaufrette($t, self::PRIVATE_KEY);
                $publicKeyPEM    = self::getNameKeyGaufrette($t, self::PUBLIC_KEY);
            };


            // return null if not found
            if (!$encryptedAESKey || !$privateKeyPEM) {
                return null;
            }

            // Decrypt the AES key with the RSA public key
            $encryptionKey = KeyFactory::deriveEncryptionKey(
                $encryptedAESKey,
                substr($encryptedAESKey->getstring(), 11, 16)
            );
            return $encryptionKey;
        }

        public static function setEncContent(string $msg = ""): ?HiddenString
        {
            return new HiddenString($msg);
        }

        public static function getHaliteKey($t, string $key = self::PRIVATE_KEY): ?EncryptionKey
        {
            return self::getNameKey($t, $key) ? KeyFactory::importEncryptionKey(
                self::getNameKey($t, $key)
            ) : null;
        }

        //        public static function getPublicKey(): EncryptionKeyPair
        //        {
        //            $directory = dirname(__DIR__, 6) . self::PATH_FOLDER . self::PUBLIC_KEY;
        //            return KeyFactory::loadEncryptionKeyPair($directory);
        //        }

        public static function setEncryptionKey(string $msg = ""): array
        {
            //            $enc_key       = self::getEncryptionKey();
            //            $key_hex       = KeyFactory::export($enc_key)->getString();
            //            $key           = new HiddenString($key_hex);
            //            $encryptionKey = KeyFactory::deriveEncryptionKey($key, substr($key->getString(), 11, 16));
            //            $message       = new HiddenString($msg);
            //            return [
            //                $encryptionKey,
            //                $message
            //            ];
        }

        public static function setBuildIndice($entity): string
        {
            // Delete the namespace 'Proxies\__CG__\'
            $className = str_replace('Proxies\__CG__\\', '', $entity::class);
            $enc_key = self::getEncryptionKey();
            $key_hex = KeyFactory::export($enc_key)->getString();
            $key = new HiddenString($key_hex);
            $imput = $className . substr($key->getString(), 15, 4) . $entity->getId();
            $encryptionKey = KeyFactory::deriveEncryptionKey($key, substr($key->getString(), 11, 16));
            return Util::keyed_hash($imput, $encryptionKey, 16);
        }

        private static function setNameKey(EncryptorInterface $t, string $key, string $content): ?string
        {
            return $t->secureKey->setKeyName($key, $content);
        }

        private static function getNameKey(EncryptorInterface $t, string $key): ?HiddenString
        {
            return new HiddenString($t->secureKey->getKeyName($key));
        }

        private static function getNameKeyGaufrette(EncryptorInterface $t, string $key): ?HiddenString
        {
            return new HiddenString($t->secureKey->getKeyNameGaufrette($key));
        }
    }