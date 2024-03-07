<?php

    namespace DoctrineEncryptor\DoctrineEncryptorBundle\tests\Pattern\Encryptor;

    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\Encryptor\HaliteEncryptor;
    use ParagonIE\Halite\Alerts\CannotPerformOperation;
    use ParagonIE\Halite\Alerts\InvalidDigestLength;
    use ParagonIE\Halite\Alerts\InvalidKey;
    use ParagonIE\Halite\Alerts\InvalidMessage;
    use ParagonIE\Halite\Alerts\InvalidSalt;
    use ParagonIE\Halite\Alerts\InvalidSignature;
    use ParagonIE\Halite\Alerts\InvalidType;
    use PHPUnit\Framework\TestCase;
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
    use Doctrine\ORM\EntityManagerInterface;
    use ParagonIE\Halite\Halite;
    class HaliteEncryptorTest extends TestCase
    {
        public HaliteEncryptor $haliteEncryptor;

        public function setUp(): void
        {

            $mockEncryptorHalite = $this->createMock( HaliteEncryptor::class );
            // Mock ParameterBagInterface
            $parameterBagMock = $this->createMock(ParameterBagInterface::class);

            // Mock EntityManagerInterface
            $entityManagerMock = $this->createMock(EntityManagerInterface::class);

//            $parameterBagMock
//                //                ->expects($this->manyTimes())
//                ->method( 'buildEncryptor' )->willReturn( $mockEncryptorHalite );

            // Create an instance of YourClassName
            $this->haliteEncryptor = new HaliteEncryptor($parameterBagMock, $entityManagerMock);

        }

        public function testConstructorInjection(): void
        {


            // Verify that the injected dependencies are being used
            $this->assertInstanceOf(ParameterBagInterface::class, $this->haliteEncryptor->parameterBag);
            $this->assertInstanceOf(EntityManagerInterface::class, $this->haliteEncryptor->entityManager);}

        /**
         * @throws InvalidType
         * @throws InvalidSignature
         * @throws InvalidDigestLength
         * @throws InvalidSalt
         * @throws \SodiumException
         * @throws InvalidKey
         * @throws InvalidMessage
         * @throws CannotPerformOperation
         */
        public function testEncryptEncryptsFieldIfNotAlreadyEncrypted(): void
        {

            // Définir un champ non encrypté
            $field = 'plaintext';

            // Appeler la méthode encrypt avec le champ non encrypté
            $encryptedField = $this->haliteEncryptor->encrypt($field);

            // Vérifier que le champ a été encrypté
            $this->assertNotEquals($field, $encryptedField);

            // Vérifier que le champ encrypté commence par le préfixe de version de Halite
            $this->assertStringStartsWith(Halite::VERSION_PREFIX, $encryptedField);
        }

        /**
         * @throws InvalidType
         * @throws InvalidSignature
         * @throws InvalidDigestLength
         * @throws InvalidSalt
         * @throws \SodiumException
         * @throws InvalidKey
         * @throws InvalidMessage
         * @throws CannotPerformOperation
         */
        public function testEncryptDoesNotEncryptFieldIfAlreadyEncrypted(): void
        {
            // Créer une instance de votre classe, ou utilisez un mock si nécessaire
            $yourClass = new HaliteEncryptor();

            // Définir un champ déjà encrypté
            $encryptedField = Halite::VERSION_PREFIX . 'encrypted_data';

            // Appeler la méthode encrypt avec le champ déjà encrypté
            $result = $yourClass->encrypt($encryptedField);

            // Vérifier que le champ reste le même
            $this->assertEquals($encryptedField, $result);
        }
    }
