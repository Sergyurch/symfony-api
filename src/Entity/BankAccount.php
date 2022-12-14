<?php

namespace App\Entity;

use App\Repository\BankAccountRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BankAccountRepository::class)]
class BankAccount implements \JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 29)]
    private ?string $iban = null;

    #[ORM\Column]
    private ?float $balance = null;

    #[ORM\ManyToOne(inversedBy: 'bankAccounts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $client = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updated_at = null;

    #[ORM\OneToMany(mappedBy: 'from_account', targetEntity: Transfer::class, orphanRemoval: true)]
    private Collection $transfers_from;

    #[ORM\OneToMany(mappedBy: 'to_account', targetEntity: Transfer::class, orphanRemoval: true)]
    private Collection $transfers_to;

    public function __construct()
    {
        $this->transfers_from = new ArrayCollection();
        $this->transfers_to = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIban(): ?string
    {
        return $this->iban;
    }

    public function setIban(string $iban): self
    {
        $this->iban = $iban;

        return $this;
    }

    public function getBalance(): ?float
    {
        return $this->balance;
    }

    public function setBalance(float $balance): self
    {
        $this->balance = $balance;

        return $this;
    }

    public function getClient(): ?User
    {
        return $this->client;
    }

    public function setClient(?User $client): self
    {
        $this->client = $client;

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

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function jsonSerialize()
    {
        return [
            "iban" => $this->getIban(),
            "balance" => $this->getBalance(),
            "client" => $this->getClient(),
            "created_at" => $this->getCreatedAt(),
            "updated_at" => $this->getUpdatedAt(),
        ];
    }

    /**
     * @return Collection<int, Transfer>
     */
    public function getTransfersFrom(): Collection
    {
        return $this->transfers_from;
    }

    public function addTransfersFrom(Transfer $transfersFrom): self
    {
        if (!$this->transfers_from->contains($transfersFrom)) {
            $this->transfers_from->add($transfersFrom);
            $transfersFrom->setFromAccount($this);
        }

        return $this;
    }

    public function removeTransfersFrom(Transfer $transfersFrom): self
    {
        if ($this->transfers_from->removeElement($transfersFrom)) {
            // set the owning side to null (unless already changed)
            if ($transfersFrom->getFromAccount() === $this) {
                $transfersFrom->setFromAccount(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Transfer>
     */
    public function getTransfersTo(): Collection
    {
        return $this->transfers_to;
    }

    public function addTransfersTo(Transfer $transfersTo): self
    {
        if (!$this->transfers_to->contains($transfersTo)) {
            $this->transfers_to->add($transfersTo);
            $transfersTo->setToAccount($this);
        }

        return $this;
    }

    public function removeTransfersTo(Transfer $transfersTo): self
    {
        if ($this->transfers_to->removeElement($transfersTo)) {
            // set the owning side to null (unless already changed)
            if ($transfersTo->getToAccount() === $this) {
                $transfersTo->setToAccount(null);
            }
        }

        return $this;
    }

}
