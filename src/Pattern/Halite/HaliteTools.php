<?php

    namespace DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\Halite;

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

        public function __construct( readonly ParameterBagInterface $parameterBag )
        {
        }


        public static function buildEncryptionKey(): void
        {
            $directory      = dirname( __DIR__, 6 ) . self::PATH_FOLDER;
            $privateKeyFile = $directory . self::PRIVATE_KEY;
            $publicKeyFile  = $directory . self::PUBLIC_KEY;
            $encryptKeyFile = $directory . self::ENCRYPT_KEY;

            // Verify that the directory is writable
            if( !is_writable( $directory ) ) {
                throw new \RuntimeException( "Directory $directory is not writable." );
            }
            // Check if the keystores already exist
            //            if( file_exists( $privateKeyFile ) || file_exists( $publicKeyFile ) ) {
            //                throw new \RuntimeException( "Key files already exist." );
            //            }

            $encKey = KeyFactory::generateEncryptionKey();
            KeyFactory::save( $encKey, $encryptKeyFile );

            $keypair = KeyFactory::generateEncryptionKeyPair();
            KeyFactory::save( $keypair->getSecretKey(), $privateKeyFile );
            KeyFactory::save( $keypair->getPublicKey(), $publicKeyFile );

        }

        public static function getEncryptionKey(): EncryptionKey
        {
            $directory = dirname( __DIR__, 6 ) . self::PATH_FOLDER . self::ENCRYPT_KEY;
            $u         = KeyFactory::loadEncryptionKey( $directory );
            return $u;
        }

        public static function getPrivateKey(): EncryptionKeyPair
        {
            $directory = dirname( __DIR__, 6 ) . self::PATH_FOLDER . self::PRIVATE_KEY;
            return KeyFactory::loadEncryptionKeyPair( $directory );
        }

        public static function getPublicKey(): EncryptionKeyPair
        {
            $directory = dirname( __DIR__, 6 ) . self::PATH_FOLDER . self::PUBLIC_KEY;
            return KeyFactory::loadEncryptionKeyPair( $directory );
        }

        public static function setEncryptionKey( string $msg = "" ): array
        {
            $enc_key       = self::getEncryptionKey();
            $key_hex       = KeyFactory::export( $enc_key )->getString();
            $key           = new HiddenString( $key_hex );
            $encryptionKey = KeyFactory::deriveEncryptionKey( $key, substr( $key->getString(), 11, 16 ) );
            $message       = new HiddenString( $msg );
            return [ $encryptionKey,
                $message ];
        }

        public static function setBuildIndice( $entity ): string
        {
            // Delete the namespace 'Proxies\__CG__\'
            $className     = str_replace( 'Proxies\__CG__\\', '', $entity::class );
            $enc_key       = self::getEncryptionKey();
            $key_hex       = KeyFactory::export( $enc_key )->getString();
            $key           = new HiddenString( $key_hex );
            $imput         = $className . substr( $key->getString(), 15, 4 ) . $entity->getId();
            $encryptionKey = KeyFactory::deriveEncryptionKey( $key, substr( $key->getString(), 11, 16 ) );
            return Util::keyed_hash( $imput, $encryptionKey, 16 );
        }
    }