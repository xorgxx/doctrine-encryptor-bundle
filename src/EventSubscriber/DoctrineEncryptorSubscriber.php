<?php

    namespace DoctrineEncryptor\DoctrineEncryptorBundle\EventSubscriber;

    use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\DoctrineEncryptorService;
    use Doctrine\ORM\Event\PostLoadEventArgs;
    use Doctrine\ORM\Event\OnFlushEventArgs;
    use Doctrine\ORM\Event\PostFlushEventArgs;
    use Doctrine\ORM\Event\PostUpdateEventArgs;
    use Doctrine\ORM\Events;
    use JsonException;
    use ReflectionException;

    /**
     * Doctrine event subscriber which encrypt/decrypt entities
     */
    #[AsDoctrineListener( event: Events::onFlush, priority: 500, connection: 'default' )]
    #[AsDoctrineListener( event: Events::postLoad, priority: 500, connection: 'default' )]
    #[AsDoctrineListener( event: Events::postFlush, priority: 500, connection: 'default' )]
    #[AsDoctrineListener( event: Events::postUpdate, priority: 500, connection: 'default' )]
    class DoctrineEncryptorSubscriber
    {
        public function __construct( readonly DoctrineEncryptorService $doctrineEncryptorService )
        {

        }

        /**
         * !! THIS CODE IS USE FOR COMMAND LINE ONLY !!
         *
         * @throws ReflectionException
         * @throws JsonException
         */
        public function postUpdate( PostUpdateEventArgs $args ): void
        {
            $entity        = $args->getObject();
            $entityManager = $args->getObjectManager();

            if( $this->doctrineEncryptorService->encryptOFF() )
                return;
            
            if( DoctrineEncryptorService::isSupport( get_class( $entity ) ) ) {
                // Encrypt the fields of the entity | Perform encryption
                if( $this->doctrineEncryptorService->entityCurentState === "Decrypt" ) {
                    $this->doctrineEncryptorService->decrypt( $entity, "onFlush" );
                    $this->doctrineEncryptorService->entityCurentState = null;
                    /**
                     * TODO : not really sure to doing flush here is best practice ?
                     * but i need to think about it !!
                     */
                    //                        $entityManager->flush();
                }
            }

        }

        /**
         * Listens to the postLoad lifecycle event and decrypts entities' property values when loaded into the entity manager.
         *
         * @param PostLoadEventArgs $args The event arguments
         *
         * @throws ReflectionException
         * @throws JsonException
         */
        public function postLoad( PostLoadEventArgs $args ): void
        {
            // Get the entity being loaded
            $entity = $args->getObject();

            // turn off encryption
            if( $this->doctrineEncryptorService->encryptOFF() )
                return;

            // Check if the entity needs to be decrypted
            if( DoctrineEncryptorService::isSupport( $entity::class ) ) {
                // Perform decryption
                $this->doctrineEncryptorService->decrypt( $entity, "postLoad", false );
            }
        }

        /**
         * Listen to postFlush event and decrypt entities after being inserted into the database
         *
         * @param PostFlushEventArgs $postFlushEventArgs
         *
         * @throws ReflectionException
         * *@throws \JsonException
         * @throws JsonException
         */
        public function postFlush( PostFlushEventArgs $postFlushEventArgs ): void
        {
            // Get the identity map from the object manager
            $identityMap   = $postFlushEventArgs->getObjectManager()->getUnitOfWork()->getIdentityMap();
            $entityManager = $postFlushEventArgs->getEntityManager();

            // turn off encryption
            if( $this->doctrineEncryptorService->encryptOFF() )
                return;

            // Iterate through the identity map and check if the entity needs to be decrypted
            foreach( $identityMap as $entityMap ) {
                foreach( $entityMap as $entity ) {
                    if( DoctrineEncryptorService::isSupport( $entity::class ) ) {
                        // Encrypt the fields of the entity | Perform encryption
                        if( !$this->doctrineEncryptorService->neoxStats[ "wasaaaa" ] ) {
                            $this->doctrineEncryptorService->encrypt( $entity, "onFlush" );
                            $this->doctrineEncryptorService->neoxStats[ "wasaaaa" ] = true;

                            /**
                             * TODO : not realy shur to doing flush here is best practice ?
                             * but i need to think about it !!
                             */
                            $entityManager->flush( $entity );

                            // This is to return uncrypted value( to show front after create)
                            $this->doctrineEncryptorService->decrypt( $entity, "onFlush" );
                        }

                        /*
                                                  // OLD CODE NOT REFACTORING !!!
                                                  //Encrypt the fields of the entity | Perform encryption

                                                  if ($this->doctrineEncryptorService->neoxStats["wasaaaa"]) continue;
                                                  $this->doctrineEncryptorService->encrypt($entity, "onFlush");
                                                  $entityManager->flush();
                                                  // this is to return uncrypted value (to show front after create)
                                                  $this->doctrineEncryptorService->decrypt($entity, "onFlush");
                        */

                    }
                }
            }
        }

        /**
         * Listens to the onFlush event and encrypts entities that are inserted into the database.
         *
         * @param OnFlushEventArgs $onFlushEventArgs
         *
         * @throws ReflectionException
         * @throws JsonException
         */
        public function onFlush( OnFlushEventArgs $onFlushEventArgs ): void
        {
            // Get the UnitOfWork object from the ObjectManager
            $unitOfWork    = $onFlushEventArgs->getObjectManager()->getUnitOfWork();
            $entityManager = $onFlushEventArgs->getEntityManager();

            // turn off encryption
            if( $this->doctrineEncryptorService->encryptOFF() )
                return;

            // Iterate through the scheduled entity insertions
            foreach( $unitOfWork->getScheduledEntityInsertions() as $entity ) {

                // Check if the entity is eligible for encryption
                if( DoctrineEncryptorService::isSupport( $entity::class ) ) {
                    // Encrypt the fields of the entity | Perform encryption
                }
            }

            foreach( $unitOfWork->getScheduledEntityUpdates() as $entity ) {
                // Check if the entity is eligible for encryption
                if( DoctrineEncryptorService::isSupport( $entity::class ) ) {

                }
            }

            foreach( $unitOfWork->getScheduledEntityDeletions() as $entity ) {
                // Check if the entity is eligible for encryption
                if( DoctrineEncryptorService::isSupport( $entity::class ) ) {
                    // Encrypt the fields of the entity | Perform encryption
                    $this->doctrineEncryptorService->remove( $entity );
                }
            }


        }
    }