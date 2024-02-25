<?php
    
    namespace DoctrineEncryptor\DoctrineEncryptorBundle\Pattern;
    
    use ReflectionProperty;
    
    class Reflection
    {
        private string $type;
        private string $propertyName;
        private \UnitEnum|float|array|bool|int|string|null $value;
        private string $attributeProperty;
        private ReflectionProperty $property;

        
        public function getType(): string
        {
            return $this->type;
        }
        
        public function setType(string $type): Reflection
        {
            $this->type = $type;
            return $this;
        }
        
        public function getPropertyName(): string
        {
            return $this->propertyName;
        }
        
        public function setPropertyName(string $propertyName): Reflection
        {
            $this->propertyName = $propertyName;
            return $this;
        }
        
        public function getValue(): \UnitEnum|float|int|bool|array|string|null
        {
            return $this->value;
        }
        
        public function setValue(\UnitEnum|float|int|bool|array|string|null $value): Reflection
        {
            $this->value = $value;
            return $this;
        }
        
        public function getProperty(): ReflectionProperty
        {
            return $this->property;
        }
        
        public function setProperty(ReflectionProperty $property): Reflection
        {
            $this->property = $property;
            return $this;
        }
        
        public function getAttributeProperty(): string
        {
            return $this->attributeProperty;
        }
        
        public function setAttributeProperty(string $attributeProperty): Reflection
        {
            $this->attributeProperty = $attributeProperty;
            return $this;
        }
    }