<?php

namespace DoctrineEncryptor\DoctrineEncryptorBundle\tests\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
//use DoctrineEncryptor\DoctrineEncryptorBundle\Attribute\neoxEncryptor;
use Symfony\Component\Validator\Constraints as Assert;

class Params
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $ind = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
//    #[neoxEncryptor(build: "out")]
    private ?string $content = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
//    #[neoxEncryptor(build: "in")]
    private ?string $description = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInd(): ?string
    {
        return $this->ind;
    }

    public function setInd(string $ind): self
    {
        $this->ind = $ind;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }
}