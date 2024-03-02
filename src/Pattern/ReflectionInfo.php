<?php
    
    namespace DoctrineEncryptor\DoctrineEncryptorBundle\Pattern;
    
    use ReflectionProperty;
    
    class ReflectionInfo
    {
        private string             $type;
        private string             $propertyName;
        private mixed              $value;
        private string             $attributeProperty;
        private mixed              $attributeFacker;
        private ReflectionProperty $property;
        
        
        public function getType(): string
        {
            return $this->type;
        }
        
        public function setType(string $type): ReflectionInfo
        {
            $this->type = $type;
            return $this;
        }
        
        public function getPropertyName(): string
        {
            return $this->propertyName;
        }
        
        public function setPropertyName(string $propertyName): ReflectionInfo
        {
            $this->propertyName = $propertyName;
            return $this;
        }
        
        public function getValue(): mixed
        {
            return $this->value;
        }
        
        public function setValue(mixed $value): ReflectionInfo
        {
            $this->value = $value;
            return $this;
        }
        
        public function getProperty(): ReflectionProperty
        {
            return $this->property;
        }
        
        public function setProperty(ReflectionProperty $property): ReflectionInfo
        {
            $this->property = $property;
            return $this;
        }
        
        public function getAttributeProperty(): string
        {
            return $this->attributeProperty;
        }
        
        public function setAttributeProperty(string $attributeProperty): ReflectionInfo
        {
            $this->attributeProperty = $attributeProperty;
            return $this;
        }
        
        public function getAttributeFacker(): mixed
        {
            return $this->attributeFacker;
        }
        
        public function setAttributeFacker(mixed $attributeFacker): ReflectionInfo
        {
            $this->attributeFacker = $attributeFacker;
            return $this;
        }
    }