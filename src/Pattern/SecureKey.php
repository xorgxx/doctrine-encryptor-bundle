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
            $this->filesystem = $this->fileSystemMap->get('neox');
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
            $this->filesystem = $this->fileSystemMap->get('neox');
            $k                = $this->filesystem->get($name);
            return $this->filesystem->getadapter()->read($k);
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
            $conf    = $this->parameterBag->get('doctrine_encryptor.encryptor_storage');
            $adaptor = explode(':', $conf)[ 1 ]; // get go

            $this->filesystem = $this->fileSystemMap->get($adaptor);
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

    }