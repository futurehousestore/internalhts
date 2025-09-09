-- Copyright (C) 2024 
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see https://www.gnu.org/licenses/.

CREATE TABLE llx_internalhts_hts(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	country varchar(3) NOT NULL, 
	code varchar(20) NOT NULL, 
	description text,
	date_creation datetime NOT NULL, 
	tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
	fk_user_creat integer NOT NULL, 
	fk_user_modif integer,
	last_main_doc varchar(255),
	import_key varchar(14),
	model_pdf varchar(255),
	status smallint NOT NULL DEFAULT 1
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;

-- Add unique constraint on country+code
ALTER TABLE llx_internalhts_hts ADD UNIQUE INDEX uk_internalhts_hts_country_code (country, code);

CREATE TABLE llx_internalhts_productmap(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	fk_product integer NOT NULL,
	fk_hts integer NOT NULL,
	date_creation datetime NOT NULL, 
	tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
	fk_user_creat integer NOT NULL, 
	fk_user_modif integer,
	import_key varchar(14),
	status smallint NOT NULL DEFAULT 1
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;

-- Add foreign key constraints
ALTER TABLE llx_internalhts_productmap ADD CONSTRAINT fk_internalhts_productmap_product FOREIGN KEY (fk_product) REFERENCES llx_product(rowid);
ALTER TABLE llx_internalhts_productmap ADD CONSTRAINT fk_internalhts_productmap_hts FOREIGN KEY (fk_hts) REFERENCES llx_internalhts_hts(rowid);

CREATE TABLE llx_internalhts_doc(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	ref varchar(128) NOT NULL DEFAULT '(PROV)', 
	label varchar(255),
	fk_soc integer,
	fk_projet integer,
	description text,
	note_public text,
	note_private text,
	date_creation datetime NOT NULL, 
	tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
	fk_user_creat integer NOT NULL, 
	fk_user_modif integer,
	fk_user_valid integer,
	last_main_doc varchar(255),
	import_key varchar(14),
	model_pdf varchar(255),
	status smallint NOT NULL DEFAULT 0,
	incoterm varchar(50),
	value_source varchar(50),
	apportionment_mode varchar(50),
	total_ht double(24,8) DEFAULT 0,
	total_customs_value double(24,8) DEFAULT 0,
	date_valid datetime,
	-- END MODULEBUILDER FIELDS
	-- Additional fields for commercial invoice
	ship_from_country varchar(3),
	ship_to_country varchar(3),
	currency varchar(3) DEFAULT 'USD',
	exchange_rate double(24,8) DEFAULT 1
) ENGINE=innodb;

-- Add unique constraint on ref
ALTER TABLE llx_internalhts_doc ADD UNIQUE INDEX uk_internalhts_doc_ref (ref);

CREATE TABLE llx_internalhts_docline(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	fk_internalhts_doc integer NOT NULL,
	fk_product integer,
	fk_hts integer,
	description text,
	sku varchar(128),
	country_of_origin varchar(3),
	qty double(24,8) NOT NULL DEFAULT 1,
	unit_price double(24,8) DEFAULT 0,
	customs_unit_value double(24,8) DEFAULT 0,
	total_ht double(24,8) DEFAULT 0,
	total_customs_value double(24,8) DEFAULT 0,
	date_creation datetime NOT NULL, 
	tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
	fk_user_creat integer NOT NULL, 
	fk_user_modif integer,
	import_key varchar(14),
	rang integer DEFAULT 0,
	status smallint NOT NULL DEFAULT 1
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;

-- Add foreign key constraints
ALTER TABLE llx_internalhts_docline ADD CONSTRAINT fk_internalhts_docline_doc FOREIGN KEY (fk_internalhts_doc) REFERENCES llx_internalhts_doc(rowid) ON DELETE CASCADE;
ALTER TABLE llx_internalhts_docline ADD CONSTRAINT fk_internalhts_docline_product FOREIGN KEY (fk_product) REFERENCES llx_product(rowid);
ALTER TABLE llx_internalhts_docline ADD CONSTRAINT fk_internalhts_docline_hts FOREIGN KEY (fk_hts) REFERENCES llx_internalhts_hts(rowid);