<?php

    namespace DoctrineEncryptor\DoctrineEncryptorBundle\tests\Pattern;

    use DoctrineEncryptor\DoctrineEncryptorBundle\Attribute\neoxEncryptor;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\DoctrineEncryptorService;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\Encryptor\OpenSSLSymEncryptor;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\NeoxDoctrineFactory;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\EncryptorInterface;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\NeoxDoctrineTools;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\Encryptor\HaliteEncryptor;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\ReflectionInfo;
    use DoctrineEncryptor\DoctrineEncryptorBundle\tests\Entity\Parames;
    use PHPUnit\Framework\TestCase;
    use DoctrineEncryptor\DoctrineEncryptorBundle\tests\Entity\Parameters;
    use DoctrineEncryptor\DoctrineEncryptorBundle\tests\Entity\Params;
    use Reflection;
    use ReflectionAttribute;
    use ReflectionClass;
    use ReflectionException;
    use ReflectionProperty;

    class DoctrineEncryptorServiceTest extends TestCase
    {

        private DoctrineEncryptorService $doctrineEncryptorService;

        public function setUp(): void
        {

            $mockEncryptorHalite = $this->createMock( HaliteEncryptor::class );
            // Créer des mocks pour les dépendances nécessaires
            $neoxDoctrineFactoryMock = $this->createMock( NeoxDoctrineFactory::class );
            $neoxDoctrineToolsMock   = $this->createMock( NeoxDoctrineTools::class );

            $neoxDoctrineFactoryMock
                //                ->expects($this->manyTimes())
                ->method( 'buildEncryptor' )->willReturn( $mockEncryptorHalite );

            $this->doctrineEncryptorService = new DoctrineEncryptorService( $neoxDoctrineFactoryMock, $neoxDoctrineToolsMock );

        }

        /**
         * Test to ensure that the getEncryptor method sets the encryptor correctly
         */
        public function testGetEncryptorSetsEncryptor(): void
        {
            // Create mock instances of encryptors
            $mockEncryptorOpenSSLSym = $this->createMock( OpenSSLSymEncryptor::class );
            $mockEncryptorHalite     = $this->createMock( HaliteEncryptor::class );

            // Get the encryptor from doctrineEncryptorService
            $doctrineEncryptorService = $this->doctrineEncryptorService;
            $doctrineEncryptorService->getEncryptor();

            // Assert that the encryptor is set as mockEncryptorHalite
            // in contractor $this->doctrineEncryptorService it' build with HaliteEncryptor::class
            $this->assertEquals( $mockEncryptorHalite, $doctrineEncryptorService->encryptor );

            // Assert that the encryptor is not set as mockEncryptorOpenSSLSym
            // it as to return false null
            $this->assertNotEquals( $mockEncryptorOpenSSLSym, $doctrineEncryptorService->encryptor );
        }

        /**
         * Test the isSupport method of the DoctrineEncryptorService class.
         *
         * @throws ReflectionException
         */
        public function testIsSupport(): void
        {
            // Assert that the isSupport method returns true for the Parameters class
            // use DoctrineEncryptor\DoctrineEncryptorBundle\Attribute\neoxEncryptor;
            $this->assertTrue( DoctrineEncryptorService::isSupport( Parameters::class ) );

            // Assert that the isSupport method returns false for the Params class
            // | // use DoctrineEncryptor\DoctrineEncryptorBundle\Attribute\neoxEncryptor;
            $this->assertFalse( DoctrineEncryptorService::isSupport( Params::class ) );

            // Assert that the isSupport method returns false for the Parames class
            $this->assertFalse( DoctrineEncryptorService::isSupport( Parames::class ) );
        }

        /**
         * Test the callBackType method of DoctrineEncryptorService.
         */
        public function testCallBackType(): void
        {
            // Test for string input
            $this->assertEquals( "<enc>", DoctrineEncryptorService::callBackType( "string" ) );

            // Test for true boolean input
            $this->assertTrue( DoctrineEncryptorService::callBackType( "<enc>", true ) );

            // Test for true boolean input with non-existent type
            $this->assertNull( DoctrineEncryptorService::callBackType( "freding", true ) );

            // Test for array input
            $this->assertEquals( [ "freding" ], DoctrineEncryptorService::callBackType( [ "freding" ], true ) );

            // Test for integer input
            $this->assertEquals( 7, DoctrineEncryptorService::callBackType( "integer" ) );

            // Test for boolean input
            $this->assertTrue( DoctrineEncryptorService::callBackType( "boolean" ) );

            // Test for non-existent type input
            $this->assertNull( DoctrineEncryptorService::callBackType( "nonExistentType" ) );
        }

        /**
         * Test for valid serialized data
         */
        public function testValidSerializedData(): void
        {
            // Access the doctrine encryptor service
            $doctrineEncryptorService = $this->doctrineEncryptorService;
            // Create serialized data
            $data  = serialize( [ 'foo' => 'bar' ] );
            $data2 = 'invalid_serialized_data';
            $data3 = '';

            // Check if the data is serialized
            $result = $doctrineEncryptorService->isSerialized( $data );
            // Assert that the result matches the original data
            $this->assertEquals( [ 'foo' => 'bar' ], $result );

            // Act string
            $result = $doctrineEncryptorService->isSerialized( $data2 );
            // Assert
            $this->assertEquals( $data2, $result );

            // Act null
            $result = $doctrineEncryptorService->isSerialized( $data3 );
            // Assert
            $this->assertEquals( null, $result );
        }

        /**
         * Tests the getReflection method of DoctrineEncryptorService.
         *
         * @throws ReflectionException
         */
        public function testGetReflection(): void
        {
            // Create a new instance of the entity class
            $entity = new Parameters(); // Replace Parameters with the name of your entity class

            // Set data for the entity object
            $entity->setContent( "Freding" );
            $entity->setDescription( "away, one, one" );
            $entity->setInd( "10" );

            // Call the getReflection method of DoctrineEncryptorService
            $reflection = $this->doctrineEncryptorService->getReflection( $entity );

            // check that the reflection is an array
            $this->assertIsArray( $reflection );

            // check that the reflection array has the entity class as a key
            $this->assertArrayHasKey( get_class( $entity ), $reflection );

            // check that the reflection array for the entity class is not empty
            $this->assertNotEmpty( $reflection[ get_class( $entity ) ] );

            // Test the attributeProperty of each reflection value
            // it as to have always either "in" or "out" cant be null or ....
            foreach( $reflection[ get_class( $entity ) ] as $key => $value ) {
                $attributeProperty = $value->getAttributeProperty();
                $this->assertTrue( $attributeProperty === "in" || $attributeProperty === "out" );
                $this->assertFalse( $attributeProperty === "foo" || $attributeProperty === null );
            }

            // Test the attributeFacker of each reflection value
            foreach( $reflection[ get_class( $entity ) ] as $key => $value ) {
                $attributeFacker = $value->getAttributeFacker();
                $this->assertTrue( $attributeFacker === null || gettype( $attributeFacker ) === 'mixed' );
            }
        }
    }
