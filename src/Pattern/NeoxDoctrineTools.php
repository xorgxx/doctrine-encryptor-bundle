<?php
    
    namespace DoctrineEncryptor\DoctrineEncryptorBundle\Pattern;
    
    use Doctrine\Common\EventManager;
    use DoctrineEncryptor\DoctrineEncryptorBundle\EventSubscriber\DoctrineEncryptorSubscriber;
    use Doctrine\Persistence\ManagerRegistry;
    use Doctrine\ORM\Events;
    
    class NeoxDoctrineTools
    {
        private EventManager $eventManager;
        
        public function __construct(readonly ManagerRegistry $doctrine)
        {
            $this->eventManager = $this->doctrine->getManager()->getEventManager();
        }
        
        public  function EventListenerPostLoad(bool $stat = false): void
        {
            if ($stat) {
                $this->eventManager->addEventListener([Events::postLoad], DoctrineEncryptorSubscriber::class);
            }else{
                $this->eventManager->removeEventListener([Events::postLoad], DoctrineEncryptorSubscriber::class);
            }
        }
        
        public  function EventListenerOnFlush(bool $stat = false): void
        {
            if ($stat) {
                $this->eventManager->addEventListener([Events::onFlush], DoctrineEncryptorSubscriber::class);
            }else{
                $this->eventManager->removeEventListener([Events::onFlush], DoctrineEncryptorSubscriber::class);
            }
        }
        
        public  function EventListenerPostFlush(bool $stat = false): void
        {
            if ($stat) {
                $this->eventManager->addEventListener([Events::postFlush], DoctrineEncryptorSubscriber::class);
            }else{
                $this->eventManager->removeEventListener([Events::postFlush], DoctrineEncryptorSubscriber::class);
            }
        }
    }