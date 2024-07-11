<?php

    namespace DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\Encryptor;

    use Doctrine\ORM\EntityManagerInterface;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Entity\NeoxEncryptor;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\CacheManagerService;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\EncryptorInterface;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\NeoxDoctrineTools;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\OpenSSL\OpenSSLTools;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\Halite\HaliteTools;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\SecureKey;
    use Gaufrette\Filesystem;
    use ParagonIE\Halite\Alerts\CannotPerformOperation;
    use ParagonIE\Halite\Alerts\InvalidDigestLength;
    use ParagonIE\Halite\Alerts\InvalidKey;
    use ParagonIE\Halite\Alerts\InvalidMessage;
    use ParagonIE\Halite\Alerts\InvalidSalt;
    use ParagonIE\Halite\Alerts\InvalidSignature;
    use ParagonIE\Halite\Alerts\InvalidType;
    use ParagonIE\Halite\Halite;
    use ParagonIE\Halite\KeyFactory;
    use ParagonIE\Halite\Symmetric\Crypto;
    use ParagonIE\Halite\Symmetric\EncryptionKey;
    use ParagonIE\Halite\Util;
    use ParagonIE\HiddenString\HiddenString;
    use SodiumException;
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
    use Knp\Bundle\GaufretteBundle\FilesystemMap;

    class HaliteEncryptor implements EncryptorInterface
    {
        private array  $secret;
        private ?EncryptionKey $secretKey;
        
        public function __construct( 
            readonly ParameterBagInterface $parameterBag,
            readonly EntityManagerInterface $entityManager,
            readonly NeoxDoctrineTools $neoxDoctrineTools,
            readonly SecureKey $secureKey,
        )
        {
            $this->setSecretKeys();
        }

        public function setSecretKeys(): void
        {
            // return give possibility to reste OpenSSL key !!
            $this->secretKey       = haliteTools::getSecretBin($this) ?? null;
            $this->privateKey      = haliteTools::getHaliteKey($this, haliteTools::PRIVATE_KEY) ?? null;
        }

        /**
         * @throws InvalidType
         * @throws InvalidSignature
         * @throws InvalidDigestLength
         * @throws SodiumException
         * @throws InvalidSalt
         * @throws InvalidKey
         * @throws InvalidMessage
         * @throws CannotPerformOperation
         */
        public function encrypt( $field ): string
        {
            $Halite = Halite::VERSION_PREFIX;
            // if it is already Encrypted then Decrypted
            if( !preg_match( "/^{$Halite}/", $field ) ) {

                $this->neoxDoctrineTools->setCountEncrypt(1);
                return Crypto::encrypt( HaliteTools::setEncContent($field), $this->privateKey, Halite::ENCODE_BASE64 );
            }
            return $field;
        }

        /**
         * @throws InvalidType
         * @throws InvalidSignature
         * @throws InvalidDigestLength
         * @throws SodiumException
         * @throws InvalidSalt
         * @throws InvalidKey
         * @throws InvalidMessage
         * @throws CannotPerformOperation
         */
        public function decrypt( $field ): string
        {
            $Halite = Halite::VERSION_PREFIX;

            // if it is already Encrypted then Decrypted
            if( preg_match( "/^{$Halite}/", $field ) ) {

                $this->neoxDoctrineTools->setCountDecrypt(1);
                return Crypto::decrypt( HaliteTools::setEncContent($field)->getString(), $this->privateKey, Halite::ENCODE_BASE64 )->getString();
            }
            return $field;
        }

        /**
         * @throws InvalidType
         * @throws InvalidKey
         * @throws SodiumException
         * @throws InvalidSalt
         */
        public function getEncryptionKey( string $msg = "" ): array
        {
            // ..
        }

        /**
         * @param $entity
         *
         * @return NeoxEncryptor|null
         */
        public function getEncryptorId( $entity ): ?NeoxEncryptor
        {
            // ff5d400f96d533dfda3018dc7dce45f5
            //            $indice = OpenSSLTools::builderIndice($entity); b097f088794521e49a6d52385d75456c
            //            $indice = HaliteTools::setBuildIndice( $entity );
//            $indice     = OpenSSLTools::builderIndice($entity);
            $indice     = OpenSSLTools::builderIndice($entity, $this->secretKey->getRawKeyMaterial());
            return $this->entityManager->getRepository(NeoxEncryptor::class)->findOneBy(['data' => $indice]) ?: (new NeoxEncryptor())->setData($indice);

            //            $indice = OpenSSLTools::builderIndice( $entity );
//            if( !$encryptor = $this->entityManager->getRepository( NeoxEncryptor::class )->findOneBy( [ 'data' => $indice ] ) ) {
//                $encryptor = new NeoxEncryptor();
//                $encryptor->setData( $indice );
//            };
//
//            return $encryptor;
        }
    }