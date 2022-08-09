<?php

namespace App\Entity;

use App\Repository\TransferRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransferRepository::class)]
class Transfer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'transfers_from')]
    #[ORM\JoinColumn(nullable: false)]
    private ?BankAccount $from_account = null;

    #[ORM\ManyToOne(inversedBy: 'transfers_to')]
    #[ORM\JoinColumn(nullable: false)]
    private ?BankAccount $to_account = null;

    #[ORM\Column]
    private ?float $amount = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFromAccount(): ?BankAccount
    {
        return $this->from_account;
    }

    public function setFromAccount(?BankAccount $from_account): self
    {
        $this->from_account = $from_account;

        return $this;
    }

    public function getToAccount(): ?BankAccount
    {
        return $this->to_account;
    }

    public function setToAccount(?BankAccount $to_account): self
    {
        $this->to_account = $to_account;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }
}
