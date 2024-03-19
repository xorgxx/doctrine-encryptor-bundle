<?php

    namespace DoctrineEncryptor\DoctrineEncryptorBundle\Pattern;
    
    use DateTime;
    use Psr\Cache\CacheItemInterface;
    use Psr\Cache\CacheItemPoolInterface;
    use Psr\Cache\InvalidArgumentException;
    use Symfony\Contracts\Cache\CacheInterface;
    
    class CacheManagerService
    {
        public CacheInterface $cache;
        private CacheItemInterface $ItemPool;
        private CacheItemPoolInterface $cacheItemPool;
        
        public function __construct(CacheItemPoolInterface $cacheItemPool, CacheInterface $cache)
        {
            $this->cache            = $cache;
            $this->cacheItemPool    = $cacheItemPool;
        }
        
        /**
         * @throws InvalidArgumentException
         */
        public function getCache(string $Key): self
        {
            $this->Item     = $this->cache->get($Key);
            return $this;
        }
        
        /**
         * @throws InvalidArgumentException
         */
        public function getCacheItem(string $Key): self
        {
            $this->ItemPool     = $this->cacheItemPool->getItem($Key);
            return $this;
        }
        
        /**
         * @return DateTime | null
         * @throws \Exception
         */
        public function getItemExpire(): null|DateTime
        {
            if ($this->ItemPool->isHit() && $this->ItemPool->getMetadata()) {
                return new DateTime("@". $this->ItemPool->getMetadata()['expiry']);
            }
            return null;
        }
        
        public function getItem(): null| CacheItemInterface
        {
            return $this->ItemPool;
        }
    
    }