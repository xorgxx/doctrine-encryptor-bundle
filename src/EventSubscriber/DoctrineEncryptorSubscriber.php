<?php
    
    namespace DoctrineEncryptor\DoctrineEncryptorBundle\EventSubscriber;
    
    use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
    use Doctrine\ORM\Event\PostFlushEventArgs;
    use Doctrine\ORM\Event\PostLoadEventArgs;
    use Doctrine\ORM\Event\OnFlushEventArgs;
    use Doctrine\ORM\Event\PreUpdateEventArgs;
    use Doctrine\ORM\Event\PostUpdateEventArgs;
    use Doctrine\ORM\Event\PrePersistEventArgs;
    use Doctrine\ORM\Event\PostPersistEventArgs;
    use Doctrine\ORM\Events;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\DoctrineEncryptorService;
    use JsonException;
    use ReflectionException;
    
    /**
     * Doctrine event subscriber which encrypt/decrypt entities
     */
    #[AsDoctrineListener(event: Events::onFlush, priority: 500, connection: 'default')]
    #[AsDoctrineListener(event: Events::postLoad, priority: 500, connection: 'default')]
    #[AsDoctrineListener(event: Events::postFlush, priority: 500, connection: 'default')]
    #[AsDoctrineListener(event: Events::preUpdate, priority: 500, connection: 'default')]
    #[AsDoctrineListener(event: Events::postUpdate, priority: 500, connection: 'default')]
    #[AsDoctrineListener(event: Events::prePersist, priority: 500, connection: 'default')]
    #[AsDoctrineListener(event: Events::postPersist, priority: 500, connection: 'default')]
    class DoctrineEncryptorSubscriber
    {
        public function __construct(readonly DoctrineEncryptorService $doctrineEncryptorService)
        {
        
        }
        
        public function preUpdate(PreUpdateEventArgs $args): void
        {
            //.....
        }
        
        /**
         * @throws ReflectionException
         * @throws JsonException
         */
        public function postUpdate(PostUpdateEventArgs $args): void
        {
            $entity = $args->getObject();
            
            if ($this->doctrineEncryptorService->encryptOFF()) return;
            
            if (DoctrineEncryptorService::isSupport(get_class($entity))) {
                // Perform encryption
                $this->doctrineEncryptorService->encrypt($entity, "preUpdate");
            }
        }
        
        // Iterate over the scheduled entity insertions (NEW)
        
        /**
         * @throws ReflectionException
         * @throws JsonException
         */
        public function prePersist(PrePersistEventArgs $args): void
        {
            $entity = $args->getObject();
            
            if ($this->doctrineEncryptorService->encryptOFF()) return;
            
            if (DoctrineEncryptorService::isSupport(get_class($entity))) {
                // Perform encryption
               $this->doctrineEncryptorService->encrypt($entity, "prePersist");
            }
        }
        
        // Iterate over the scheduled entity insertions (NEW)
        
        /**
         * @throws ReflectionException
         * @throws JsonException
         */
        public function postPersist(PostPersistEventArgs $args): void
        {
            if ($this->doctrineEncryptorService->encryptOFF()) return;

            
            $entity = $args->getObject();

            if (DoctrineEncryptorService::isSupport(get_class($entity))) {
                // Perform encryption
                $this->doctrineEncryptorService->encrypt($entity, "postPersist");
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
        public function postLoad(PostLoadEventArgs $args): void
        {
            // Get the entity being loaded
            $entity = $args->getObject();
            
            if ($this->doctrineEncryptorService->encryptOFF()) return;
            
            // Check if the entity needs to be decrypted
            if (DoctrineEncryptorService::isSupport($entity::class)) {
                // Perform decryption
                $this->doctrineEncryptorService->decrypt($entity, "postLoad", false);
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
        public function postFlush(PostFlushEventArgs $postFlushEventArgs): void
        {
            // Get the identity map from the object manager
            $identityMap = $postFlushEventArgs->getObjectManager()->getUnitOfWork()->getIdentityMap();
            
            if ($this->doctrineEncryptorService->encryptOFF()) return;
            
            // Iterate through the identity map and check if the entity needs to be decrypted
            foreach ($identityMap as $entityMap) {
                foreach ($entityMap as $entity) {
                    if (DoctrineEncryptorService::isSupport($entity::class)) {
                            // Perform decryption normal
                            $this->doctrineEncryptorService->decrypt($entity, "postFlush");
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
        public function onFlush(OnFlushEventArgs $onFlushEventArgs): void
        {
            // Get the UnitOfWork object from the ObjectManager
            $unitOfWork = $onFlushEventArgs->getObjectManager()->getUnitOfWork();
            
            if ($this->doctrineEncryptorService->encryptOFF()) return;
            
            foreach ($unitOfWork->getScheduledEntityDeletions() as $entity) {
                // Check if the entity is eligible for encryption
                if (DoctrineEncryptorService::isSupport($entity::class)) {
                    // Encrypt the fields of the entity | Perform encryption
                    $this->doctrineEncryptorService->remove($entity);
                }
            }
        }
    }