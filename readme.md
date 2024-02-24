
##
# DoctrineEncryptorBundle { Symfony 6/7 } 
This bundle provides Encrypt/Decrypt data sensible in a Db system in your application.
Its main goal is to make it simple for you to manage encrypt & decrypt sensible data into Db!
This bundle is to refresh the old bundle [DoctrineEncryptBundle](https://github.com/absolute-quantum/DoctrineEncryptBundle)

The aim of this bundle is to establish, in an automatic and transparent manner, a robust encryption and decryption
system by externalizing data, in strict compliance with European recommendations and the directives of the General Data
Protection Regulation (GDPR).

[![Doctrineencryptor-schema.png](https://i.postimg.cc/NG408N8j/Doctrineencryptor-schema.png)](https://postimg.cc/0rzxP0HT)

## Installation BETA VERSION !!

Install the bundle for Composer !! as is still on a beta version !!

````
  composer require xorgxx/doctrine-encryptor-bundle
  or 
  composer require xorgxx/doctrine-encryptor-bundle:0.* or dev-master
````
🚨 Add in your project this bundles. 🚨
````
  composer require paragonie/halite
````
.env file
````
  ....
  NEOX_ENCRY_SALT="**@#$#*#&%&@$&^@"    # 16 bit
  NEOX_ENCRY_PWS="03~é][a6{1;a7a^e2d"   # password your want (more long, more secure, more time to process)
  ....
````
doctrine_encryptor.yaml file
````
  doctrine_encryptor:
    # (default)false or true | it will turn off the bundle. by aware that it will render nothing !! field on front will by empty!!
    # this is only for testing purpose in Development mode !!!
    nencryptor_off: false
    encryptor_pws: "%env(NEOX_ENCRY_PWS)%"
    encryptor_salt: "%env(NEOX_ENCRY_SALT)%"
    encryptor_system: halite # halite or | openssl -> future
  
````
🚨 You will have to make migration to add NeoxEncryptor in your entities. 🚨
````
  symfony make:migration
  symfony doctrine:migrations:migrate
````
**We have only implemented Halite service to Crypt / unCrypt**

**NOTE:** _You may need to use [ symfony composer dump-autoload ] to reload autoloading_

 ..... Done 🎈

## Usage !
In entity, you want to secure field (data) 
````php

  use DoctrineEncryptor\DoctrineEncryptorBundle\Attribute\neoxEncryptor;
  ....
  
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[neoxEncryptor(build: "out")]
    private ?string $content = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[neoxEncryptor(build: "in")]
    private ?string $description = null;
  
  ** note that by default #[neoxEncryptor] is build: "in". You can now mixed in your entity
  ....
````
## Important !
Consider the size / length of field you want to crypt when you chose "in" !! ex: length:20
````php

  #[neoxEncryptor]
  #[ORM\Column(length: 20)]
  private ?string $name = null;
  
  "john doe" <- decrypt (length:8)  / (length: +20!!) encrypt -> "MUIFAOpLp21iX1Dy2ZNkYbby6zo7ADYgVs-hGkNaWR2OF5AbQUMcBKZHigtFVxZiIFWyOTV8Ts-9q_pNAHBxCKcAPZNJjfPgVQglMLAKi0bZicmPlCQKJpRpX2k5IAjAqawOlFsPpD9KikIEFRhuy"
  
````
Now if you encrypt or decrypt much time, it will just be crypt or decrypt much time. Data will still be manage.

## 🚨 🚨 Danger 🚨🚨
**!!! Now if you change salt or pws !!!**
1.  Decrypt all before 
2.  Change salt / pws / attribute "in"/"out" ...
3.  Encrypt ALL


## Tools power
Occasionally, we may require access to a full range of data (4000 lines or more) for various checks or analyses. However, waiting for hours due to the conversion of encrypted data is not desirable. In such cases, disabling the EventListener is imperative.
````
  use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\NeoxDoctrineTools;  
  ...
  // this will stop eventlistener to decrypt
  $neoxDoctrineTools->EventListenerPostLoad();
  
  $entity = $parametersRepository->findAll()
  
  // this will restart eventlistener to decrypt
  $neoxDoctrineTools->EventListenerPostLoad(true);
  
  // data on the field encrypted will be empty
````


[🚨🚨 **FEATURE ADVANCE** in the box in a future version](Doc/External.md)

## Contributing
If you want to contribute \(thank you!\) to this bundle, here are some guidelines:

* Please respect the [Symfony guidelines](http://symfony.com/doc/current/contributing/code/standards.html)
* Test everything! Please add tests cases to the tests/ directory when:
    * You fix a bug that wasn't covered before
    * You add a new feature
    * You see code that works but isn't covered by any tests \(there is a special place in heaven for you\)
## Todo
~~* Add a Remote system for storage Hash => Key~~
~~* to be able to encrypt/decrypt, according to a propriety type | int, string, phone ....~~
* Custom provider class Encrypt/decrypt.
* Dispatcher to custom code.
* Command wasaaaa : to manage more easily status, encrypt, decrypt ....

## Thanks