<?php
    
    namespace DoctrineEncryptor\DoctrineEncryptorBundle\Pattern;
    
    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
    
    class NeoxDoctrineFactory
    {
        
        public function __construct(readonly ParameterBagInterface $parameterBag, readonly EntityManagerInterface $entityManager, readonly NeoxDoctrineTools $neoxDoctrineTools)
        {
        }
        
        public function buildEncryptor(): mixed
        {
            // Get classe encryptor from .yaml
            $customEncryptor = $this->getEncryptorClass(); // $this->parameterBag->get('doctrine_encryptor.encryptor_system');>
            if (!class_exists($customEncryptor)) {
                throw new \RuntimeException(sprintf("What fuck !! 🫤 Class '%s' not found", $customEncryptor));
            }
            return (new $customEncryptor($this->parameterBag, $this->entityManager, $this->neoxDoctrineTools));
        }
        
        private function getEncryptorClass(): string
        {
            // Recover the default encryption service
            $encryptorSystem = $this->parameterBag->get("doctrine_encryptor.encryptor_system");
            // If the encryption system is "OpenSSL" or "Halite", use the getBuildInEncryptor method, otherwise keep the current value.
            // preg_match("/^openSSL/", $encryptorSystem)
            if ( $encryptorSystem === "openSSLSym" || $encryptorSystem === "halite") {
                $encryptorSystem = $this->getBuildInEncryptor($encryptorSystem);
            }
            return $encryptorSystem;
        }
        
        private function getBuildInEncryptor(string $name): string
        {
            return "DoctrineEncryptor\\DoctrineEncryptorBundle\\Pattern\\Encryptor\\" . ucfirst($name) . "Encryptor";
        }
    }