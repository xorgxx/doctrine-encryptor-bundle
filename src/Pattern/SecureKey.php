<?php

    namespace DoctrineEncryptor\DoctrineEncryptorBundle\Pattern;

    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
    use Gaufrette\Filesystem;
    use Knp\Bundle\GaufretteBundle\FilesystemMap;
    use Psr\Cache\CacheItemPoolInterface;
    use Symfony\Contracts\Cache\ItemInterface;

    class SecureKey
    {
        const LIFETIME = 7200;
        public Filesystem $filesystem;
        public array      $filesBag;

        public function __construct(
            readonly ParameterBagInterface $parameterBag,
            readonly FilesystemMap $fileSystemMap,
            readonly CacheManagerService $cacheManager
        ) {
            $this->initialize();
        }

        public function setKeyName(string $name, string $content)
        {
            
            $adaptator = $this->getConfigGaufrette();
            echo "[OK] Gaufrette - filesystems : $adaptator -> create new key : $name  \n";
            return $this->filesystem->getadapter()->write($name, $content);
        }

        public function getKeyName(string $name)
        {
            $cache = $this->cacheManager->cache->getitem($name);
            if ($cache->isHit()) {
                return $cache->get();
            }
            return null;
        }

        public function getKeyNameGaufrette(string $name)
        {
            $this->getConfigGaufrette();
            return $this->filesystem->read($name);
//            $k = $this->filesystem->get($name);
//            return $this->filesystem->getadapter()->read($k);
        }

        public function resteAllKey(string $filter)
        {
            $this->filesystem = $this->fileSystemMap->get('neox');
            $this->filesBag   = $this->filesystem->listKeys("");
            $this->cacheManager->cache->delete('KEYS');

            foreach ($this->filesBag[ "keys" ] as $k => $v) {
                // Check if the file name matches your filter criteria
                $this->filesystem->getAdapter()->delete($v);
                $this->cacheManager->cache->delete($v);
            }
        }

        private function initialize()
        {
            // get config gaufrette
            $this->getConfigGaufrette();
            $this->filesBag   = $this->cacheManager->cache->get("KEYS", function (ItemInterface $item) {
                $this->filesBag = $this->filesystem->listKeys("");
                $item->expiresAfter(self::LIFETIME);
                $item->set($this->filesBag);
                return $this->filesBag;
            });
            $this->setVirtualSecretKeys();
        }

        private function setVirtualSecretKeys()
        {
            foreach ($this->filesBag[ "keys" ] as $k => $v) {
                $this->cacheManager->cache->get($v, function (ItemInterface $item) use ($k, $v) {
                    // gaufrette read files "abrod" 😉
                    $r = $this->filesystem->getadapter()->read($v);
                    $item->expiresAfter(self::LIFETIME);
                    $item->set($r);
                    return $r;
                });
            }
        }

        /**
         * @return string
         */
        private function getConfigGaufrette(): string
        {
            $conf = $this->parameterBag->get('doctrine_encryptor.encryptor_storage');
            if (!$conf) {
                throw new \Exception(
                    'Configuration doctrine_encryptor.encryptor_storage not found. Check your config\\doctrine_encryptor.yml'
                );
            }
            $adaptor          = explode(':', $conf)[ 1 ];
            if (!$adaptor) {
                throw new \Exception(
                    "No filesystem is registered for name $adaptor"
                );
            }
            try {
                $this->filesystem = $this->fileSystemMap->get($adaptor);
            } catch (\Exception $e) {
                throw new \Exception(
                    "No filesystem is registered for name $adaptor. Check your gaufrette.yaml"
                );
            }
            return $adaptor; // get go
        }
    }