<?php
    
    namespace DoctrineEncryptor\DoctrineEncryptorBundle\Pattern;
    
    use DoctrineEncryptor\DoctrineEncryptorBundle\Entity\NeoxEncryptor;
    
    interface EncryptorInterface
    {
        public function encrypt($field): string;
        
        public function decrypt($field): string;
        
        public function getEncryptionKey(string $msg = ""): array;
        
        public function getEncryptorId($entity): ?NeoxEncryptor;
    }