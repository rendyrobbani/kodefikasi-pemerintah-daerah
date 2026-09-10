<?php

namespace RendyRobbani\Kodefikasi\Pemda\Entity;

use RendyRobbani\Kodefikasi\Pemda\Comparator\StringComparator;
use RendyRobbani\PHP\Persistence\Check;
use RendyRobbani\PHP\Persistence\Checks;
use RendyRobbani\PHP\Persistence\Column;
use RendyRobbani\PHP\Persistence\Entity;
use RendyRobbani\PHP\Persistence\Id;

#[Entity(table: "fungsi_kabupaten")]
#[Checks([
	new Check("id = concat_ws('-', nomor_fungsi, nomor_subfungsi, nomor_urusan, nomor_bidang, nomor_program, nomor_kegiatan1, nomor_kegiatan2)"),
	new Check("nomor_fungsi is null or nomor_fungsi > 0"),
	new Check("nomor_subfungsi is null or nomor_subfungsi > 0"),
	new Check("nomor_urusan is null or nomor_urusan >= 0"),
	new Check("nomor_bidang is null or nomor_bidang >= 0"),
	new Check("nomor_program is null or nomor_program > 0"),
	new Check("nomor_kegiatan1 is null or nomor_kegiatan1 > 0"),
	new Check("nomor_kegiatan2 is null or nomor_kegiatan2 > 0"),
])]
class FungsiKabupatenEntity
{
	#[Id]
	#[Column]
	protected string|null $id = null;

	#[Column]
	protected int|null $nomorFungsi = null;

	#[Column]
	protected int|null $nomorSubfungsi = null;

	#[Column]
	protected int|null $nomorUrusan = null;

	#[Column]
	protected int|null $nomorBidang = null;

	#[Column]
	protected int|null $nomorProgram = null;

	#[Column]
	protected int|null $nomorKegiatan1 = null;

	#[Column]
	protected int|null $nomorKegiatan2 = null;

	#[Column]
	protected string|null $nama = null;

	#[Column(type: "date")]
	protected string|null $createdAt = null;

	#[Column]
	protected string|null $createdBy = null;

	protected bool|null $isUpdated = false;

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

	public function isEqual(FungsiKabupatenEntity|null $entity): bool
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

	public function log(): FungsiKabupatenLogEntity
	{
		$logEntity = new FungsiKabupatenLogEntity();
		$logEntity->setIdReference($this->id());
		$logEntity->setNomorFungsi($this->nomorFungsi());
		$logEntity->setNomorSubfungsi($this->nomorSubfungsi());
		$logEntity->setNomorUrusan($this->nomorUrusan());
		$logEntity->setNomorBidang($this->nomorBidang());
		$logEntity->setNomorProgram($this->nomorProgram());
		$logEntity->setNomorKegiatan1($this->nomorKegiatan1());
		$logEntity->setNomorKegiatan2($this->nomorKegiatan2());
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

	public function kodeUrusan(): string|null
	{
		if ($this->nomorUrusan === null) return null;
		return str_replace("0", "X", $this->nomorUrusan);
	}

	public function kodeBidang(): string|null
	{
		if ($this->nomorBidang === null) return null;
		return str_replace("00", "XX", str_pad($this->nomorBidang, 2, "0", STR_PAD_LEFT));
	}

	public function kodeProgram(): string|null
	{
		return $this->nomorProgram;
	}

	public function kodeKegiatan(): string|null
	{
		if ($this->nomorKegiatan1 === null) return null;
		if ($this->nomorKegiatan2 === null) return null;
		return $this->nomorKegiatan1 . "." . str_pad($this->nomorKegiatan2, 2, "0", STR_PAD_LEFT);
	}

	public function kode(): string|null
	{
		$kode = [];
		for ($level = 1; $level <= 6; $level++) {
			$value = match ($level) {
				1 => $this->kodeFungsi(),
				2 => $this->kodeSubfungsi(),
				3 => $this->kodeUrusan(),
				4 => $this->kodeBidang(),
				5 => $this->kodeProgram(),
				6 => $this->kodeKegiatan(),
			};
			if ($value === null) break;
			$kode[] = $value;
		}
		return implode(".", $kode);
	}

	public function id(): string|null
	{
		$id = [];
		for ($level = 1; $level <= 7; $level++) {
			$value = match ($level) {
				1 => $this->nomorFungsi,
				2 => $this->nomorSubfungsi,
				3 => $this->nomorUrusan,
				4 => $this->nomorBidang,
				5 => $this->nomorProgram,
				6 => $this->nomorKegiatan1,
				7 => $this->nomorKegiatan2,
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

	public function nomorUrusan(): int|null
	{
		return $this->nomorUrusan;
	}

	public function nomorBidang(): int|null
	{
		return $this->nomorBidang;
	}

	public function nomorProgram(): int|null
	{
		return $this->nomorProgram;
	}

	public function nomorKegiatan1(): int|null
	{
		return $this->nomorKegiatan1;
	}

	public function nomorKegiatan2(): int|null
	{
		return $this->nomorKegiatan2;
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

	public function isUpdated(): bool|null
	{
		return $this->isUpdated === true;
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

	public function setNomorUrusan(int|null $nomorUrusan): void
	{
		$this->nomorUrusan = $nomorUrusan;
	}

	public function setNomorBidang(int|null $nomorBidang): void
	{
		$this->nomorBidang = $nomorBidang;
	}

	public function setNomorProgram(int|null $nomorProgram): void
	{
		$this->nomorProgram = $nomorProgram;
	}

	public function setNomorKegiatan1(int|null $nomorKegiatan1): void
	{
		$this->nomorKegiatan1 = $nomorKegiatan1;
	}

	public function setNomorKegiatan2(int|null $nomorKegiatan2): void
	{
		$this->nomorKegiatan2 = $nomorKegiatan2;
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

	public function setIsUpdated(bool|null $isUpdated): void
	{
		$this->isUpdated = $isUpdated === true;
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