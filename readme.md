
##
# DoctrineEncryptorBundle { Symfony 6/7 } 
This bundle provides Encrypt/Decrypt data sensible in a Db system in your application.
Its main goal is to make it simple for you to manage encrypt & decrypt sensible data into Db!
This bundle is to refresh the old bundle [DoctrineEncryptBundle](https://github.com/absolute-quantum/DoctrineEncryptBundle)

The aim of this bundle is to establish, in an automatic and transparent manner, a robust encryption and decryption
system by externalizing data, in strict compliance with European recommendations and the directives of the General Data
Protection Regulation (GDPR).

PSR-12


[![Doctrineencryptor-schema.png](https://i.postimg.cc/NG408N8j/Doctrineencryptor-schema.png)](https://postimg.cc/0rzxP0HT)

## Installation DEV-MASTER VERSION is not stable yet ! to use as sandBox !!

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


## Doctrine migrations
🚨 You will have to make migration to add NeoxEncryptor in your entities. 🚨
````
  symfony make:migration
  symfony doctrine:migrations:migrate
````
## Install, setup, folder, .pem .key

* You may have to create manual folder: config/doctrine-encryptor
* `php bin/console neox:encryptor:install` follow instruction (this command will setup config files : doctrine-encryptor & gaufrette for you). 
* `php bin/console neox:encryptor:openssl` follow instruction.

**NOTE:** _You may need to use [ symfony composer dump-autoload ] to reload autoload_

## ..... Done 🎈

## Config file
doctrine_encryptor.yaml file
````
  doctrine_encryptor:
    # (default)false or true | it will turn off the bundle. by aware that it will render nothing !! field on front will by empty!!
    # this is only for testing purpose in Development mode !!!
    nencryptor_off: false
    encryptor_cipher_algorithm: AES-256-CBC  # AES-256-CBC | !!! Camellia-256-CBC !!!
    encryptor_system: halite # halite | openSSLSym | !!! DEPRECIATED openSSLAsym !!! (das not support advance typing (obejt, array, ...) yet) 
    encryptor_storage: 'gaufrette:local' # name of filesystems in you config/gaufrette.yaml
    encryptor_cache: false # use true for ussing cache system. you will have to setup your application before  
````
To setup your cache system [symfony cache](https://symfony.com/doc/current/components/cache.html)

## More security getting key form external store
🚨 We use  [KnpGaufrette](https://github.com/KnpLabs/KnpGaufretteBundle) .
In this setup, all keys are stored externally and are not accessible from within your website. This means that even if
someone gains access to your code, they won't be able to access the keys, providing an additional layer of security for
your encryption system.
In order you have to configure at liste one "adapter":
config/gaufrette.yaml
````
  knp_gaufrette:
      adapters:
          local_adapter:
              local:
                  directory: '%kernel.project_dir%/config/doctrine-encryptor/'
  
      filesystems:
          local:
              adapter: local_adapter
````

    /** 
    * ===== openSSLSym is match faster !! | ======
    * openSSLAsym because is Asymetric we cant put macth data in encrypte SO it's not working well yet!!
    * 🚨 Due to instability issues (after ~100 caractes), it is advisable not to use 
    * the openSSLAsym encryptor for handling advanced data typing (obejt, array, ...).!!
    **/

* [Cipher Algorithm list](doc/cipherAlgorithm.md)
* [Encryptor list](doc/encryptor)
* [Logs & reversing](doc/logger)

**because it'd not realised yet, before making update bundle, we recommend decrypting all your sandbox data**

## Usage !
In entity, you want to secure field (data) 
````php

  use DoctrineEncryptor\DoctrineEncryptorBundle\Attribute\NeoxEncryptor;
  ....
  
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[neoxEncryptor(build: "out", facker : )]
    private ?string $content = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[neoxEncryptor(build: "in")]
    private ?string $description = null;
 
    
    /** =======   note that by default #[neoxEncryptor] 
    * Attribute : build: "in". be default  in / out
    * Attribute : facker: PhoneFacker::class. be default This give possibility to customize the "facker" for ex: type phoneNumber it's not buildin bundle, but hee you can make service.
    **/
 
  ....
````
## Custom facker 
This is special to manage typing and want to be shown in a database.
Most of the attributes are recognized be the bundle string, int, date ...., But in some cas as PhoneNumber, [...] bundle will not recognized! So you will need to add service.

````
    <?php
      namespace App\Services;
      use libphonenumber\PhoneNumber;
      class PhoneFacker implements neoxFackerInterface
      {
          public function create( ): PhoneNumber
          {
              return  (new PhoneNumber())
                  ->setCountryCode(33)
                  ->setNationalNumber("14155552671")
              ;
          }
      }
````
Then is Entity file add attribute [facker: PhoneFacker::class] 
````
    #[AssertPhoneNumber(type: [AssertPhoneNumber::MOBILE], defaultRegion: 'FR')]
    #[ORM\Column(type: "phone_number", nullable: true)]
    #[neoxEncryptor(build: "out", facker: PhoneFacker::class)]
    private ?PhoneNumber $phoneNumber = null;
    
````

## TWIG 
To manage on template twig to decrypt field
````
  {{ health.profile | doctrineDecrypt("firstName") }}
  first put entity and the field name to decrypt
````

## Important !
Consider the size / length of field you want to crypt when you chose "in" !! ex: length:20
````php

  #[neoxEncryptor]
  #[ORM\Column(length: 20)]
  private ?string $name = null;
  
  "john doe" <- decrypt (length:8)  / (length: +20!!) encrypt -> "MUIFAOpLp21iX1Dy2ZNkYbby6zo7ADYgVs-hGkNaWR2OF5AbQUMcBKZHigtFVxZiIFWyOTV8Ts-9q_pNAHBxCKcAPZNJjfPgVQglMLAKi0bZicmPlCQKJpRpX2k5IAjAqawOlFsPpD9KikIEFRhuy"
  
````
## Beware !!
  * **NO possibility to make index or search on field encrypted**
## 🚨 🚨 Danger 🚨🚨
**!!! Before you change anything key, attribute "in"/"out" ... !!!**
1. Decrypt all before
2. Change what you want ex: attribute from "in" to "out"
3. Encrypt ALL


## [CLI] Command build-in
  * `php bin/console neox:encryptor:wasaaaa` // command line to crypt/decrypt
  * `php bin/console neox:encryptor:openssl` // command to create .pem & .key
  * `php bin/console neox:encryptor:halite`  // command to create .pem & .key
  * `php bin/console neox:encryptor:switch`  // command to switch to new encryptor: ex: halite to openSSLSym
  * ~~php bin/console neox:encryptor:renew   // command to change all .pem & .key files. mainly to change cryptage.~~

####  For exemple | php bin/console neox:encryptor:switch | Process automatique will do this : ex: halite to openSSLSym
* Decrypt all with the current encryptor halite
* Modify in doctrine_encryptor.yaml |-> encryptor_system: halite >>> openSSLSym
* Clear the cache 
* Then you can encrypt by using `php bin/console neox:encryptor:wasaaaa` as normally.

## <a href="https://www.google.com/search?client=firefox-b-d&q=wasaaa" target="_blank"> !! ??? WASAAAA ? 😉</a>

❔Now if you encrypt or decrypt much time, it will just be crypt or decrypt much time. Data will still be manage.




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


[🚨🚨 **FEATURE ADVANCE** in the box in a future version](doc/External.md)

## Contributing
If you want to contribute \(thank you!\) to this bundle, here are some guidelines:
* ## I'm not an expert in Pest/PHPUnit testing, so I haven't been able to complete the tests. If anyone can offer assistance, please reach out to me. Thank you!
* Please respect the [Symfony guidelines](http://symfony.com/doc/current/contributing/code/standards.html)
* Test everything! Please add tests cases to the tests/ directory when:
    * You fix a bug that wasn't covered before
    * You add a new feature
    * You see code that works but isn't covered by any tests \(there is a special place in heaven for you\)
## Todo
* ~~History/log of command line to have protection when encrypt/decrypt !!~~
* Testing App !! Need help for this.
* IT DAS NOT CONVERT ADVANCE TYPE (objet, array, date ....)
* ~~Add a Remote system for storage Hash => Key~~
* ~~to be able to encrypt/decrypt, according to a propriety type | int, string, phone ....~~
* ~~Custom provider class Encrypt/decrypt.~~
* ~~Dispatcher to custom code.~~
* ~~Command wasaaaa : to manage more easily status, encrypt, decrypt ....~~

## Thanks