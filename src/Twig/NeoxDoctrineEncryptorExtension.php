<?php

    namespace DoctrineEncryptor\DoctrineEncryptorBundle\Twig;


    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\DoctrineEncryptorService;
    use JsonException;
    use ReflectionException;
    use Twig\Extension\AbstractExtension;
    use Twig\TwigFunction;
    use Twig\TwigFilter;
    
    class NeoxDoctrineEncryptorExtension extends AbstractExtension
    {
        
      
        public function __construct(readonly DoctrineEncryptorService $doctrineEncryptorService)
        {

        }
        
        /**
         * @return array
         */
        public function getFunctions(): array
        {
            return [
                new TwigFunction('doctrtrineEncryptorDecrypt', [$this, 'doctrtrineEncryptorDecrypt'], ['is_safe' => ['html']]),                
            ];
            
        }
        public function getFilters()
        {
            return [
                new TwigFilter('doctrineDecrypt', [$this, 'doctrineDecrypt']),
            ];
        }

        /**
         * @throws ReflectionException|JsonException
         */
        public function doctrineDecrypt(mixed $entity, string $field): string
        {
            // Delete the namespace 'Proxies\__CG__\'
            $className = str_replace('Proxies\__CG__\\', '', $entity::class);
            if( DoctrineEncryptorService::isSupport( $className ) ) {
                // Perform decryption
                return $this->doctrineEncryptorService->getTwigDecrypt( $entity, $field, false );
            }
            $p   = "get{$field}";
            return $entity->$p();
        }
        
        /**
         * @throws ReflectionException
         */
        public function doctrtrineEncryptorDecrypt(string $html): string
        {
            return true;
        }
        
    }