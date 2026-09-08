<?php

namespace RendyRobbani\Kodefikasi\Pemda\Entity;

use RendyRobbani\Kodefikasi\Pemda\Comparator\StringComparator;
use RendyRobbani\PHP\Persistence\Check;
use RendyRobbani\PHP\Persistence\Checks;
use RendyRobbani\PHP\Persistence\Column;
use RendyRobbani\PHP\Persistence\Entity;
use RendyRobbani\PHP\Persistence\Id;

#[Entity(table: "fungsi")]
#[Checks([
	new Check("id = concat_ws('-', nomor_fungsi, nomor_subfungsi)"),
	new Check("nomor_fungsi is null or nomor_fungsi > 0"),
	new Check("nomor_subfungsi is null or nomor_subfungsi > 0"),
])]
class FungsiEntity
{
	#[Id]
	#[Column]
	protected string|null $id = null;

	#[Column]
	protected int|null $nomorFungsi = null;

	#[Column]
	protected int|null $nomorSubfungsi = null;

	#[Column]
	protected string|null $nama = null;

	#[Column(type: "date")]
	protected string|null $createdAt = null;

	#[Column]
	protected string|null $createdBy = null;

	#[Column(type: "date")]
	protected string|null $updatedAt = null;

	#[Column]
	protected string|null $updatedBy = null;

	#[Column]
	protected bool|null $isDeleted = false;

	#[Column(type: "date")]
	protected string|null $deletedAt = null;

	#[Column]
	protected string|null $deletedBy = null;

	public function isEqual(FungsiEntity|null $entity): bool
	{
		if ($entity === null) return false;

		$compare = 0;
		$isEqual = 0;

		$compare++;
		if (StringComparator::isEqual(
			$this->id(),
			$entity->id(),
		)) $isEqual++;

		$compare++;
		if (StringComparator::isEqual(
			$this->nama(),
			$entity->nama(),
		)) $isEqual++;

		return $compare === $isEqual;
	}

	public function log(): FungsiLogEntity
	{
		$logEntity = new FungsiLogEntity();
		$logEntity->setIdReference($this->id());
		$logEntity->setNomorFungsi($this->nomorFungsi());
		$logEntity->setNomorSubfungsi($this->nomorSubfungsi());
		$logEntity->setNama($this->nama());
		$logEntity->setCreatedAt($this->createdAt());
		$logEntity->setCreatedBy($this->createdBy());
		$logEntity->setUpdatedAt($this->updatedAt());
		$logEntity->setUpdatedBy($this->updatedBy());
		$logEntity->setIsDeleted($this->isDeleted());
		$logEntity->setDeletedAt($this->deletedAt());
		$logEntity->setDeletedBy($this->deletedBy());
		return $logEntity;
	}

	public function kodeFungsi(): string|null
	{
		if ($this->nomorFungsi === null) return null;
		return str_pad($this->nomorFungsi, 2, "0", STR_PAD_LEFT);
	}

	public function kodeSubfungsi(): string|null
	{
		if ($this->nomorSubfungsi === null) return null;
		return str_pad($this->nomorSubfungsi, 2, "0", STR_PAD_LEFT);
	}

	public function kode(): string|null
	{
		$kode = [];
		for ($level = 1; $level <= 2; $level++) {
			$value = match ($level) {
				1 => $this->kodeFungsi(),
				2 => $this->kodeSubfungsi(),
			};
			if ($value === null) break;
			$kode[] = $value;
		}
		return implode(".", $kode);
	}

	public function id(): string|null
	{
		$id = [];
		for ($level = 1; $level <= 2; $level++) {
			$value = match ($level) {
				1 => $this->nomorFungsi,
				2 => $this->nomorSubfungsi,
			};
			if ($value === null) break;
			$id[] = $value;
		}
		return $this->id = implode("-", $id);
	}

	public function nomorFungsi(): int|null
	{
		return $this->nomorFungsi;
	}

	public function nomorSubfungsi(): int|null
	{
		return $this->nomorSubfungsi;
	}

	public function nama(): string|null
	{
		return $this->nama;
	}

	public function createdAt(): string|null
	{
		return $this->createdAt;
	}

	public function createdBy(): string|null
	{
		return $this->createdBy;
	}

	public function updatedAt(): string|null
	{
		return $this->updatedAt;
	}

	public function updatedBy(): string|null
	{
		return $this->updatedBy;
	}

	public function isDeleted(): bool|null
	{
		return $this->isDeleted === true;
	}

	public function deletedAt(): string|null
	{
		return $this->deletedAt;
	}

	public function deletedBy(): string|null
	{
		return $this->deletedBy;
	}

	public function setNomorFungsi(int|null $nomorFungsi): void
	{
		$this->nomorFungsi = $nomorFungsi;
	}

	public function setNomorSubfungsi(int|null $nomorSubfungsi): void
	{
		$this->nomorSubfungsi = $nomorSubfungsi;
	}

	public function setNama(string|null $nama): void
	{
		$this->nama = $nama;
	}

	public function setCreatedAt(string|null $createdAt): void
	{
		$this->createdAt = $createdAt;
	}

	public function setCreatedBy(string|null $createdBy): void
	{
		$this->createdBy = $createdBy;
	}

	public function setUpdatedAt(string|null $updatedAt): void
	{
		$this->updatedAt = $updatedAt;
	}

	public function setUpdatedBy(string|null $updatedBy): void
	{
		$this->updatedBy = $updatedBy;
	}

	public function setIsDeleted(bool|null $isDeleted): void
	{
		$this->isDeleted = $isDeleted === true;
	}

	public function setDeletedAt(string|null $deletedAt): void
	{
		$this->deletedAt = $deletedAt;
	}

	public function setDeletedBy(string|null $deletedBy): void
	{
		$this->deletedBy = $deletedBy;
	}
}