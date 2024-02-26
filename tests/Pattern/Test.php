<?php
    
    namespace DoctrineEncryptor\DoctrineEncryptorBundle\tests\Pattern;
    
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\DoctrineEncryptorService;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Attribute\neoxEncryptor;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Entity\NeoxEncryptor as data;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\EncryptorInterface;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\NeoxDoctrineTools;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\NeoxDoctrineFactory;
    use DoctrineEncryptor\DoctrineEncryptorBundle\tests\Entity\Parameters;
    use DoctrineEncryptor\DoctrineEncryptorBundle\tests\Entity\Params;
    use PHPUnit\Framework\TestCase;
    
    class Test extends TestCase
    {
        /**
         * @throws \ReflectionException
         */
        public function testIsSupportWithNeoxEncryptorAttribute(): void
        {
            
            $this->assertTrue(DoctrineEncryptorService::isSupport(Parameters::class));
        }
        
        /**
         * @throws \ReflectionException
         */
        public function testIsSupportWithoutNeoxEncryptorAttribute(): void
        {
            
            $this->assertFalse(DoctrineEncryptorService::isSupport(Params::class));
        }
        

    }

