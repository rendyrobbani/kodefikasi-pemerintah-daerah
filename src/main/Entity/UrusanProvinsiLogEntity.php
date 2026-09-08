<?php

namespace RendyRobbani\Kodefikasi\Pemda\Entity;

use RendyRobbani\Kodefikasi\Pemda\Peraturan\Peraturan;
use RendyRobbani\PHP\Persistence\Column;
use RendyRobbani\PHP\Persistence\Entity;
use RendyRobbani\PHP\Persistence\ForeignKey;
use RendyRobbani\PHP\Persistence\ForeignKeys;
use RendyRobbani\PHP\Persistence\Id;

#[Entity(table: "urusan_provinsi_log")]
#[ForeignKeys(values: [
	new ForeignKey(
		columns: [
			"id_reference",
		],
		referenceTable: "urusan_provinsi",
		referenceColumns: [
			"id",
		],
	)
])]
class UrusanProvinsiLogEntity
{
	#[Id(isGeneratedValue: true)]
	#[Column]
	protected int|null $id = null;

	#[Column]
	protected string|null $idReference = null;

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
	protected int|null $nomorSubkegiatan = null;

	#[Column]
	protected string|null $nama = null;

	#[Column]
	protected string|null $keterangan = null;

	#[Column]
	protected string|null $kinerja = null;

	#[Column]
	protected string|null $indikator = null;

	#[Column]
	protected string|null $satuan = null;

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

	public function kodeSubkegiatan(Peraturan $peraturan): string|null
	{
		if ($this->nomorSubkegiatan === null) return null;
		return str_pad($this->nomorSubkegiatan, match ($peraturan) {
			Peraturan::PERMENDAGRI_TAHUN_2019_NOMOR_90,
			Peraturan::KEPMENDAGRI_TAHUN_2020_NOMOR_050_3708,
			Peraturan::KEPMENDAGRI_TAHUN_2021_NOMOR_050_5889 => 2,
			default => 4,
		}, "0", STR_PAD_LEFT);
	}

	public function kode(Peraturan $peraturan): string|null
	{
		$kode = [];
		for ($level = 1; $level <= 5; $level++) {
			$value = match ($level) {
				1 => $this->kodeUrusan(),
				2 => $this->kodeBidang(),
				3 => $this->kodeProgram(),
				4 => $this->kodeKegiatan(),
				5 => $this->kodeSubkegiatan($peraturan),
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

	public function nomorSubkegiatan(): int|null
	{
		return $this->nomorSubkegiatan;
	}

	public function nama(): string|null
	{
		return $this->nama;
	}

	public function keterangan(): string|null
	{
		return $this->keterangan;
	}

	public function kinerja(): string|null
	{
		return $this->kinerja;
	}

	public function indikator(): string|null
	{
		return $this->indikator;
	}

	public function satuan(): string|null
	{
		return $this->satuan;
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

	public function setNomorSubkegiatan(int|null $nomorSubkegiatan): void
	{
		$this->nomorSubkegiatan = $nomorSubkegiatan;
	}

	public function setNama(string|null $nama): void
	{
		$this->nama = $nama;
	}

	public function setKeterangan(string|null $keterangan): void
	{
		$this->keterangan = $keterangan;
	}

	public function setKinerja(string|null $kinerja): void
	{
		$this->kinerja = $kinerja;
	}

	public function setIndikator(string|null $indikator): void
	{
		$this->indikator = $indikator;
	}

	public function setSatuan(string|null $satuan): void
	{
		$this->satuan = $satuan;
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