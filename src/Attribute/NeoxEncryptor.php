<?php

    namespace DoctrineEncryptor\DoctrineEncryptorBundle\Attribute;

    use Attribute;
    use Doctrine\ORM\Mapping\MappingAttribute;

    /**
     * The `neoxEncryptor` class is a PHP attribute that can be applied to properties and is used as a
     * placeholder for encryption functionality.
     */
    #[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_CLASS)]
    class NeoxEncryptor implements MappingAttribute
    {
        /**
         * System to build encryption
         * build-in     : in | to encrypt the value in same field
         * build-in     : out | to encrypt the value on neoxEncryptor class "out"
         * encrypt      : halite | to use the halite library
         *
         * @param string|null $build
         * @param string|null $encrypt
         * @param mixed|null  $facker
         */
        public function __construct(
            readonly ?string $build = 'in',
            readonly ?string $encrypt = 'halite',
            readonly mixed $facker = null,

        ) {
        }
    }