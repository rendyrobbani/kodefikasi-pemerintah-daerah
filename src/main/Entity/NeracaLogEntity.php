<?php

namespace RendyRobbani\Kodefikasi\Pemda\Entity;

use RendyRobbani\Kodefikasi\Pemda\Peraturan\Peraturan;
use RendyRobbani\PHP\Persistence\Column;
use RendyRobbani\PHP\Persistence\Entity;
use RendyRobbani\PHP\Persistence\ForeignKey;
use RendyRobbani\PHP\Persistence\ForeignKeys;
use RendyRobbani\PHP\Persistence\Id;

#[Entity(table: "neraca_log")]
#[ForeignKeys(values: [
	new ForeignKey(
		columns: [
			"id_reference",
		],
		referenceTable: "neraca",
		referenceColumns: [
			"id",
		],
	)
])]
class NeracaLogEntity
{
	#[Id(isGeneratedValue: true)]
	#[Column]
	protected int|null $id = null;

	#[Column]
	protected string|null $idReference = null;

	#[Column]
	protected int|null $nomorRekening1 = null;

	#[Column]
	protected int|null $nomorRekening2 = null;

	#[Column]
	protected int|null $nomorRekening3 = null;

	#[Column]
	protected int|null $nomorRekening4 = null;

	#[Column]
	protected int|null $nomorRekening5 = null;

	#[Column]
	protected int|null $nomorRekening6 = null;

	#[Column]
	protected string|null $nama = null;

	#[Column]
	protected string|null $keterangan = null;

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

	public function kodeRekening1(): string|null
	{
		if ($this->nomorRekening1 === null) return null;
		return $this->nomorRekening1;
	}

	public function kodeRekening2(): string|null
	{
		if ($this->nomorRekening2 === null) return null;
		return $this->nomorRekening2;
	}

	public function kodeRekening3(): string|null
	{
		if ($this->nomorRekening3 === null) return null;
		return str_pad($this->nomorRekening3, 2, "0", STR_PAD_LEFT);
	}

	public function kodeRekening4(): string|null
	{
		if ($this->nomorRekening4 === null) return null;
		return str_pad($this->nomorRekening4, 2, "0", STR_PAD_LEFT);
	}

	public function kodeRekening5(Peraturan $peraturan): string|null
	{
		if ($this->nomorRekening5 === null) return null;
		return str_pad($this->nomorRekening5, match ($peraturan) {
			Peraturan::PERMENDAGRI_TAHUN_2019_NOMOR_90,
			Peraturan::KEPMENDAGRI_TAHUN_2020_NOMOR_050_3708,
			Peraturan::KEPMENDAGRI_TAHUN_2021_NOMOR_050_5889,
			Peraturan::KEPMENDAGRI_TAHUN_2023_NOMOR_900_1_15_5_1317,
			Peraturan::KEPMENDAGRI_TAHUN_2024_NOMOR_900_1_15_5_3406,
			Peraturan::KEPMENDAGRI_TAHUN_2025_NOMOR_900_1_2850 => 2,
			Peraturan::KEPMENDAGRI_TAHUN_2026_NOMOR_900_1_861 => 3
		}, "0", STR_PAD_LEFT);
	}

	public function kodeRekening6(Peraturan $peraturan): string|null
	{
		if ($this->nomorRekening6 === null) return null;
		return str_pad($this->nomorRekening6, match ($peraturan) {
			Peraturan::PERMENDAGRI_TAHUN_2019_NOMOR_90 => 3,
			Peraturan::KEPMENDAGRI_TAHUN_2020_NOMOR_050_3708,
			Peraturan::KEPMENDAGRI_TAHUN_2021_NOMOR_050_5889,
			Peraturan::KEPMENDAGRI_TAHUN_2023_NOMOR_900_1_15_5_1317,
			Peraturan::KEPMENDAGRI_TAHUN_2024_NOMOR_900_1_15_5_3406,
			Peraturan::KEPMENDAGRI_TAHUN_2025_NOMOR_900_1_2850 => 4,
			Peraturan::KEPMENDAGRI_TAHUN_2026_NOMOR_900_1_861 => 5
		}, "0", STR_PAD_LEFT);
	}

	public function kode(Peraturan $peraturan): string|null
	{
		$kode = [];
		for ($level = 1; $level <= 6; $level++) {
			$value = match ($level) {
				1 => $this->kodeRekening1(),
				2 => $this->kodeRekening2(),
				3 => $this->kodeRekening3(),
				4 => $this->kodeRekening4(),
				5 => $this->kodeRekening5($peraturan),
				6 => $this->kodeRekening6($peraturan),
			};
			if ($value === null) break;
			$kode[] = $value;
		}
		return implode(".", $kode);
	}

	public function id(): int|null
	{
		return $this->id;
	}

	public function idReference(): string|null
	{
		return $this->idReference;
	}

	public function nomorRekening1(): int|null
	{
		return $this->nomorRekening1;
	}

	public function nomorRekening2(): int|null
	{
		return $this->nomorRekening2;
	}

	public function nomorRekening3(): int|null
	{
		return $this->nomorRekening3;
	}

	public function nomorRekening4(): int|null
	{
		return $this->nomorRekening4;
	}

	public function nomorRekening5(): int|null
	{
		return $this->nomorRekening5;
	}

	public function nomorRekening6(): int|null
	{
		return $this->nomorRekening6;
	}

	public function nama(): string|null
	{
		return $this->nama;
	}

	public function keterangan(): string|null
	{
		return $this->keterangan;
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

	public function setId(int|null $id): void
	{
		$this->id = $id;
	}

	public function setIdReference(string|null $idReference): void
	{
		$this->idReference = $idReference;
	}

	public function setNomorRekening1(int|null $nomorRekening1): void
	{
		$this->nomorRekening1 = $nomorRekening1;
	}

	public function setNomorRekening2(int|null $nomorRekening2): void
	{
		$this->nomorRekening2 = $nomorRekening2;
	}

	public function setNomorRekening3(int|null $nomorRekening3): void
	{
		$this->nomorRekening3 = $nomorRekening3;
	}

	public function setNomorRekening4(int|null $nomorRekening4): void
	{
		$this->nomorRekening4 = $nomorRekening4;
	}

	public function setNomorRekening5(int|null $nomorRekening5): void
	{
		$this->nomorRekening5 = $nomorRekening5;
	}

	public function setNomorRekening6(int|null $nomorRekening6): void
	{
		$this->nomorRekening6 = $nomorRekening6;
	}

	public function setNama(string|null $nama): void
	{
		$this->nama = $nama;
	}

	public function setKeterangan(string|null $keterangan): void
	{
		$this->keterangan = $keterangan;
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