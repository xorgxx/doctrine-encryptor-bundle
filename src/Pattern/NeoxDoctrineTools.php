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
        
        public function EventListenerPostLoad(bool $stat = false): void
        {
            $method = $this->getAction($stat);
            $this->eventManager->$method([Events::postLoad], DoctrineEncryptorSubscriber::class);
        }
        
        public function EventListenerOnFlush(bool $stat = false): void
        {
            $method = $this->getAction($stat);
            $this->eventManager->$method([Events::onFlush], DoctrineEncryptorSubscriber::class);
        }
        
        public function EventListenerPostUpdate(bool $stat = false): void
        {
            $method = $this->getAction($stat);
            $this->eventManager->$method([Events::postUpdate], DoctrineEncryptorSubscriber::class);
        }

        public function EventListenerAll(bool $stat = false): void
        {
            $method = $this->getAction($stat);
            $this->eventManager->$method([Events::postLoad, Events::onFlush, Events::postUpdate, Events::postFlush], DoctrineEncryptorSubscriber::class);
        }
        
        public function EventListenerPostFlush(bool $stat = false): void
        {
            $method = $this->getAction($stat);
            $this->eventManager->$method([Events::postFlush], DoctrineEncryptorSubscriber::class);
        }
        
        /**
         * @param bool $stat
         *
         * @return string
         */
        public function getAction(bool $status): string
        {
            return $status ? 'addEventListener' : 'removeEventListener';
        }
    }