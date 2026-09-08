drop table if exists urusan_provinsi_log;
drop table if exists urusan_provinsi;

create or replace table urusan_provinsi (
	id                varchar(255),
	nomor_urusan      tinyint,
	nomor_bidang      tinyint,
	nomor_program     tinyint,
	nomor_kegiatan1   tinyint,
	nomor_kegiatan2   tinyint,
	nomor_subkegiatan smallint,
	nama              varchar(255),
	keterangan        varchar(255),
	kinerja           varchar(255),
	indikator         varchar(255),
	satuan            varchar(255),
	created_at        date,
	created_by        varchar(255),
	updated_at        date,
	updated_by        varchar(255),
	is_deleted        bit,
	deleted_at        date,
	deleted_by        varchar(255),
	constraint ck_urusan_provinsi_01 check (id = concat_ws('-', nomor_urusan, nomor_bidang, nomor_program, nomor_kegiatan1, nomor_kegiatan2, nomor_subkegiatan)),
	constraint ck_urusan_provinsi_02 check (nomor_urusan is null or nomor_urusan >= 0),
	constraint ck_urusan_provinsi_03 check (nomor_bidang is null or nomor_bidang >= 0),
	constraint ck_urusan_provinsi_04 check (nomor_program is null or nomor_program > 0),
	constraint ck_urusan_provinsi_05 check (nomor_kegiatan1 is null or nomor_kegiatan1 > 0),
	constraint ck_urusan_provinsi_06 check (nomor_kegiatan2 is null or nomor_kegiatan2 > 0),
	constraint ck_urusan_provinsi_07 check (nomor_subkegiatan is null or nomor_subkegiatan > 0),
	primary key (id)
) engine = innodb
  charset = utf8mb4
  collate = utf8mb4_unicode_ci;

create or replace table urusan_provinsi_log (
	id                int auto_increment,
	id_reference      varchar(255),
	nomor_urusan      tinyint,
	nomor_bidang      tinyint,
	nomor_program     tinyint,
	nomor_kegiatan1   tinyint,
	nomor_kegiatan2   tinyint,
	nomor_subkegiatan smallint,
	nama              varchar(255),
	keterangan        varchar(255),
	kinerja           varchar(255),
	indikator         varchar(255),
	satuan            varchar(255),
	created_at        date,
	created_by        varchar(255),
	updated_at        date,
	updated_by        varchar(255),
	is_deleted        bit,
	deleted_at        date,
	deleted_by        varchar(255),
	constraint fk_urusan_provinsi_log_01 foreign key (id_reference) references urusan_provinsi (id),
	primary key (id)
) engine = innodb
  charset = utf8mb4
  collate = utf8mb4_unicode_ci;

drop table if exists urusan_kabupaten_log;
drop table if exists urusan_kabupaten;

create or replace table urusan_kabupaten (
	id                varchar(255),
	nomor_urusan      tinyint,
	nomor_bidang      tinyint,
	nomor_program     tinyint,
	nomor_kegiatan1   tinyint,
	nomor_kegiatan2   tinyint,
	nomor_subkegiatan smallint,
	nama              varchar(255),
	keterangan        varchar(255),
	kinerja           varchar(255),
	indikator         varchar(255),
	satuan            varchar(255),
	created_at        date,
	created_by        varchar(255),
	updated_at        date,
	updated_by        varchar(255),
	is_deleted        bit,
	deleted_at        date,
	deleted_by        varchar(255),
	constraint ck_urusan_kabupaten_01 check (id = concat_ws('-', nomor_urusan, nomor_bidang, nomor_program, nomor_kegiatan1, nomor_kegiatan2, nomor_subkegiatan)),
	constraint ck_urusan_kabupaten_02 check (nomor_urusan is null or nomor_urusan >= 0),
	constraint ck_urusan_kabupaten_03 check (nomor_bidang is null or nomor_bidang >= 0),
	constraint ck_urusan_kabupaten_04 check (nomor_program is null or nomor_program > 0),
	constraint ck_urusan_kabupaten_05 check (nomor_kegiatan1 is null or nomor_kegiatan1 > 0),
	constraint ck_urusan_kabupaten_06 check (nomor_kegiatan2 is null or nomor_kegiatan2 > 0),
	constraint ck_urusan_kabupaten_07 check (nomor_subkegiatan is null or nomor_subkegiatan > 0),
	primary key (id)
) engine = innodb
  charset = utf8mb4
  collate = utf8mb4_unicode_ci;

create or replace table urusan_kabupaten_log (
	id                int auto_increment,
	id_reference      varchar(255),
	nomor_urusan      tinyint,
	nomor_bidang      tinyint,
	nomor_program     tinyint,
	nomor_kegiatan1   tinyint,
	nomor_kegiatan2   tinyint,
	nomor_subkegiatan smallint,
	nama              varchar(255),
	keterangan        varchar(255),
	kinerja           varchar(255),
	indikator         varchar(255),
	satuan            varchar(255),
	created_at        date,
	created_by        varchar(255),
	updated_at        date,
	updated_by        varchar(255),
	is_deleted        bit,
	deleted_at        date,
	deleted_by        varchar(255),
	constraint fk_urusan_kabupaten_log_01 foreign key (id_reference) references urusan_kabupaten (id),
	primary key (id)
) engine = innodb
  charset = utf8mb4
  collate = utf8mb4_unicode_ci;