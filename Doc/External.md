# Build "out" | Advance read before you do anything 🚨🚨🚨

[![Untitled-Diagram-drawio-4.png](https://i.postimg.cc/9MRwLs29/Untitled-Diagram-drawio-4.png)](https://postimg.cc/gXp0JSnc)

## Process

In this mode, all data that exclusively has the #[neoxEncryptor] attribute in the source entity will be
moved to another entity to be encrypted. This process ensures that the data will no longer be visible in the source
entity.

## Link

The link between the source entity and the NeoxEncryptor entity is established by a standard algorithm, but you also
have the possibility to define it yourself, according to your needs and preferences.

## VERY IMPORTANT TO UNDERSTAND. 🚨

It's important to note that robust data encryption incurs additional costs in terms of computational processing. On
average, each encryption operation may require around 0.10 milliseconds (we have reduced be 70%) of processing per data line. Furthermore, when
converting data from one format to another, it's necessary to account for additional processing time. For instance,
processing 500 lines may take approximately 3 minutes.

Maintaining consistency in data format across all entities is also crucial to simplify operations. This ensures
uniformity in the encryption process and reduces overall system complexity.

During the conversion from one format to another, several steps are required:

    * Decrypt the data in the current format.
    * Make your change in entity, doctrine_encryptor.ymal ....
    * Encrypt the data in the new format.

Similarly, attempting to hydrate a table in the front end with an entity containing encrypted fields will take a
significant amount of time!

It is crucial to note that it will be relatively simple to switch from standalone mode (decryption) to external mode,
but that the reverse is not currently possible. This limitation mainly arises from the complexity associated with the
decryption process.

## Algorithm link

We want to enhance security by complicating the algorithm that automatically associates the source entity with the
encrypted entity in my Symfony application. Currently, this logic is exposed in the source code, which could pose a
security risk if the code is accessible to third parties. To make this association less obvious to a hacker, I would
like to make the algorithm more complex while also providing an easy customization option for users. Thus, even if the
source code is accessible, it would be challenging to easily determine which elements belong to which entities.

````
    /**
     * here secure by using salt and entity | your salt should be unique (Defined in doctrine_encryptor.yaml) and will renforce security
     * This ligne will create dynamically link between neoxEncryptor / entity
     * without using doctrine it will by match hard to link neoxEncryptor / entity !!!
     **/ 
    $indice                 = Util::keyed_hash($reflectionClass->getName(). substr($salt, 3, 5) . $entity->getId(), $encryptionKey,16);
````




