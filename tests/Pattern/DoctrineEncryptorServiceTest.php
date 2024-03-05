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

        public function testGetEncryptorSetsEncryptor(): void
        {
            $mockEncryptorOpenSSLSym = $this->createMock( OpenSSLSymEncryptor::class );
            $mockEncryptorHalite     = $this->createMock( HaliteEncryptor::class );

            $doctrineEncryptorService = $this->doctrineEncryptorService;
            $doctrineEncryptorService->getEncryptor();

            $this->assertEquals( $mockEncryptorHalite, $doctrineEncryptorService->encryptor );
            $this->assertNotEquals( $mockEncryptorOpenSSLSym, $doctrineEncryptorService->encryptor );
        }

        /**
         * @throws ReflectionException
         */
        public function testIsSupport(): void
        {
            $this->assertTrue( DoctrineEncryptorService::isSupport( Parameters::class ) );
            $this->assertFalse( DoctrineEncryptorService::isSupport( Params::class ) );
            $this->assertFalse( DoctrineEncryptorService::isSupport( Parames::class ) );
        }

        public function testCallBackType(): void
        {
            $this->assertEquals( "<enc>", DoctrineEncryptorService::callBackType( "string" ) );
            $this->asserttrue( DoctrineEncryptorService::callBackType( "<enc>", true ) );
            $this->assertNull( DoctrineEncryptorService::callBackType( "freding", true ) );
            $this->assertEquals(["freding"], DoctrineEncryptorService::callBackType( ["freding"], true ) );
            $this->assertEquals( 7, DoctrineEncryptorService::callBackType( "integer" ) );
            $this->assertTrue( DoctrineEncryptorService::callBackType( "boolean" ) );
            $this->assertNull( DoctrineEncryptorService::callBackType( "nonExistentType" ) );
        }

        public function testValidSerializedData(): void
        {
            $doctrineEncryptorService = $this->doctrineEncryptorService;
            $data                     = serialize( [ 'foo' => 'bar' ] );
            $result                   = $doctrineEncryptorService->isSerialized( $data );
            $this->assertEquals( [ 'foo' => 'bar' ], $result );
        }

        public function testInvalidSerializedData(): void
        {
            $doctrineEncryptorService = $this->doctrineEncryptorService;
            $data                     = 'invalid_serialized_data';
            $result                   = $doctrineEncryptorService->isSerialized( $data );
            $this->assertEquals( $data, $result );
        }

        public function testNonSerializedData(): void
        {
            $doctrineEncryptorService = $this->doctrineEncryptorService;
            $serializedData           = serialize( [ 'example' => 'data' ] );
            $nonSerializedData        = 'not serialized data';
            $p                        = $doctrineEncryptorService->isSerialized( $nonSerializedData );
            // Testez la méthode isSerialized avec des données non sérialisées
            $this->assertEquals( $nonSerializedData, $p );

            $p = $doctrineEncryptorService->isSerialized( $serializedData );
            // Testez la méthode isSerialized avec des données sérialisées
            $this->assertEquals( [ 'example' => 'data' ], $p );


        }

        /**
         * @throws ReflectionException
         */
        public function testGetReflection(): void
        {

            $entity = new Parameters(); // Remplacez Parameters par le nom de votre classe d'entité

            // Attribuer des données à l'objet d'entité
            $entity->setContent( "Freding" );
            $entity->setDescription( "away, one, one" );
            $entity->setInd( "10" );

            $reflection = $this->doctrineEncryptorService->getReflection( $entity );

            $this->assertIsArray( $reflection );
            $this->assertArrayHasKey( get_class( $entity ), $reflection );
            $this->assertNotEmpty( $reflection[ get_class( $entity ) ] );
            // test build
            foreach( $reflection[ get_class( $entity ) ] as $key => $value ) {
                $attributeProperty = $value->getAttributeProperty();
                $this->assertTrue( $attributeProperty === "in" || $attributeProperty === "out" );
            }
            // test facker
            foreach( $reflection[ get_class( $entity ) ] as $key => $value ) {
                $attributeFacker = $value->getAttributeFacker();
                $this->assertTrue( $attributeFacker === null || gettype( $attributeFacker ) === 'mixed' );
            }
        }

    }
