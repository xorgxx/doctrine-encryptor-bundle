<?php
    
    namespace DoctrineEncryptor\DoctrineEncryptorBundle\tests\Pattern;
    
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\DoctrineEncryptorService;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\NeoxDoctrineFactory;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\NeoxDoctrineTools;
    use PHPUnit\Framework\TestCase;
    use DoctrineEncryptor\DoctrineEncryptorBundle\tests\Entity\Parameters;
    use DoctrineEncryptor\DoctrineEncryptorBundle\tests\Entity\Params;
    use Reflection;
    use ReflectionException;
    
    class DoctrineEncryptorServiceTest extends TestCase
    {
        /**
         * @throws ReflectionException
         */
        public function testIsSupportPositive(): void
        {
            $this->assertTrue(DoctrineEncryptorService::isSupport(Parameters::class));
        }
        
        /**
         * @throws ReflectionException
         */
        public function testIsSupportNegative(): void
        {
            $this->assertFalse(DoctrineEncryptorService::isSupport(Params::class));
        }
        
        public function testCallBackType()
        {
            $this->assertEquals("<enc>", DoctrineEncryptorService::callBackType("string"));
            $this->assertEquals(7, DoctrineEncryptorService::callBackType("integer"));
            $this->assertTrue(DoctrineEncryptorService::callBackType("boolean"));
            $this->assertNull(DoctrineEncryptorService::callBackType("nonExistentType"));
        }
        
        public function testGetReflection(): void
        {
            // Créez un objet simulé pour votre entité
            $entityMock = $this->getMockBuilder(Parameters::class)
                ->disableOriginalConstructor()
                ->getMock();
            
            // Créez un double (mock) pour la classe Reflection
            $reflectionMock = $this->createMock(Reflection::class);
            
            // Configurez le comportement du mock de la classe Reflection
            $reflectionMock->expects($this->once())
                ->method('setProperty');
            
            
            // Créez des doubles (mocks) pour les dépendances nécessaires
            $neoxDoctrineFactoryMock = $this->createMock(NeoxDoctrineFactory::class);
            $neoxDoctrineToolsMock = $this->createMock(NeoxDoctrineTools::class);
            
            // Instanciez la classe que vous voulez tester en passant les doubles en tant que dépendances
            $yourClassInstance = new DoctrineEncryptorService($neoxDoctrineFactoryMock, $neoxDoctrineToolsMock);
            
            
            // Appelez la méthode à tester
            $result = $yourClassInstance->getReflection($entityMock);
            
            // Effectuez des assertions sur le résultat pour vérifier son intégrité
            // Assurez-vous que le résultat est dans le format attendu et qu'il contient les bonnes données
            
            // Par exemple :
            $this->assertIsArray($result);
            $this->assertArrayHasKey(YourEntityClass::class, $result);
            $this->assertNotEmpty($result[YourEntityClass::class]);
            // Assurez-vous que les objets Reflection dans le résultat ont les propriétés correctement configurées
        }
    }
