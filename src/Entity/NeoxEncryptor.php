<?php

    namespace DoctrineEncryptor\DoctrineEncryptorBundle\Entity;

    use DoctrineEncryptor\DoctrineEncryptorBundle\Repository\NeoxEncryptorRepository;
    use Doctrine\DBAL\Types\Types;
    use Doctrine\ORM\Mapping as ORM;

    #[ORM\Entity(repositoryClass: NeoxEncryptorRepository::class)]
    class NeoxEncryptor
    {
        #[ORM\Id]
        #[ORM\GeneratedValue]
        #[ORM\Column]
        private ?int $id = null;

        #[ORM\Column(length: 255)]
        private ?string $data = null;

        #[ORM\Column(type: Types::TEXT)]
        private ?string $content = null;

        public function getId(): ?int
        {
            return $this->id;
        }

        public function getData(): ?string
        {
            return $this->data;
        }

        public function setData(string $data): static
        {
            $this->data = $data;

            return $this;
        }

        public function getContent(): ?string
        {
            return $this->content;
        }

        public function setContent(string $content): static
        {
            $this->content = $content;

            return $this;
        }

        public function serialize(): string
        {
            return serialize([
                'id'      => $this->id,
                'data'    => $this->data,
                'content' => $this->content
            ]);
        }

        public function toArray(): array
        {
            return [
                'id'      => $this->id,
                'data'    => $this->data,
                'content' => $this->content
            ];
        }
    }
