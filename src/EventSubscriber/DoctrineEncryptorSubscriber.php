<?php
    
    namespace DoctrineEncryptor\DoctrineEncryptorBundle\EventSubscriber;
    
    use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
    use Doctrine\ORM\Event\PostFlushEventArgs;
    use Doctrine\ORM\Event\PostLoadEventArgs;
    use Doctrine\ORM\Event\OnFlushEventArgs;
    use Doctrine\ORM\Events;
    use DoctrineEncryptor\DoctrineEncryptorBundle\Pattern\DoctrineEncryptorService;
    use JsonException;
    use ReflectionException;
    
    /**
     * Doctrine event subscriber which encrypt/decrypt entities
     */
    #[AsDoctrineListener(event: Events::postLoad, priority: 10, connection: 'default')]
    #[AsDoctrineListener(event: Events::onFlush, priority: 500, connection: 'default')]
    #[AsDoctrineListener(event: Events::postFlush, priority: 500, connection: 'default')]
//    #[AsDoctrineListener(event: Events::preRemove, priority: 10, connection: 'default')]
    class DoctrineEncryptorSubscriber
    {
        public function __construct( readonly DoctrineEncryptorService $doctrineEncryptorService)
        {
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
            
            // Check if the entity needs to be decrypted
            if (DoctrineEncryptorService::isSupport( $entity::class)) {
                // Perform decryption
                $this->doctrineEncryptorService->decrypt($entity, "postLoad", false);
            }
        }
        
        /**
         * Listen to postFlush event and decrypt entities after being inserted into the database
         *
         * @param PostFlushEventArgs $postFlushEventArgs
         *
         * @throws ReflectionException*@throws \JsonException
         * @throws JsonException
         */
        public function postFlush(PostFlushEventArgs $postFlushEventArgs): void
        {
            // Get the identity map from the object manager
            $identityMap = $postFlushEventArgs->getObjectManager()->getUnitOfWork()->getIdentityMap();
            
            // Iterate through the identity map and check if the entity needs to be decrypted
            foreach ($identityMap as $entityMap) {
                foreach ($entityMap as $entity) {
                    if (DoctrineEncryptorService::isSupport($entity::class)) {
                        if (isset($this->doctrineEncryptorService->encryptors[$entity::class])) {
                            // Perform encryption entity having neoxEncryptor waiting
                            $this->doctrineEncryptorService->encrypt($entity, "postFlush");
                        }else{
                            // Perform decryption normal
                            $this->doctrineEncryptorService->decrypt($entity, "postFlush");
                        }
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
            
            // Iterate over the scheduled entity insertions
            foreach ($unitOfWork->getScheduledEntityInsertions() as $entity) {
                // Check if the entity is eligible for encryption
                if (DoctrineEncryptorService::isSupport( $entity::class)) {
                    // Perform encryption
                    $this->doctrineEncryptorService->encrypt($entity, "insert", true);
                    // Recompute the change set for the entity
                    $unitOfWork->recomputeSingleEntityChangeSet(
                        $onFlushEventArgs->getObjectManager()->getClassMetadata(get_class($entity)),
                        $entity
                    );
                }
            }
            
            // Iterate over the scheduled entity updates
            foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
                // Check if the entity is eligible for encryption
                if (DoctrineEncryptorService::isSupport($entity::class)) {
                    // Encrypt the fields of the entity | Perform encryption
                    $this->doctrineEncryptorService->encrypt($entity, "update");
                }
            }
            
            foreach ($unitOfWork->getScheduledEntityDeletions() as $entity) {
                // Check if the entity is eligible for encryption
                if (DoctrineEncryptorService::isSupport($entity::class)) {
                    // Encrypt the fields of the entity | Perform encryption
                    $this->doctrineEncryptorService->remove($entity);
                }
            }
        }
        public static function getSubscribedEvents(): array
        {
            return [
                Events::postLoad,
                Events::onFlush,
                Events::postFlush,
            ];
        }
        
        private function getEncryptorFactory()
        {
//            $off = $this->parameterBag->get("neox_doctrine_secure.neox_off");
//            if ($off) {
//                return null;
//            }
//            return $this->neoxCryptorService;
        }
        
    }