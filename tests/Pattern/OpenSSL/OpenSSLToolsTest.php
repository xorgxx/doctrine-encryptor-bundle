<?php

    namespace DoctrineEncryptor\DoctrineEncryptorBundle\tests\Pattern\OpenSSL;

    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\OpenSSL\OpenSSLTools;
    use PHPUnit\Framework\TestCase;
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

    class OpenSSLToolsTest extends TestCase
    {
        public function testConstructorInitializesParameterBagInterfaceCorrectly(): void
        {
            $parameterBagMock = $this->createMock( ParameterBagInterface::class );
            $instance         = new OpenSSLTools( $parameterBagMock );
            $this->assertInstanceOf( ParameterBagInterface::class, $instance->parameterBag );
        }


    }
