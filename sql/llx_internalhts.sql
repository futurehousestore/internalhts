-- Copyright (C) 2024 FutureHouse Store
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

CREATE TABLE llx_c_hts_codes(
  rowid             integer AUTO_INCREMENT PRIMARY KEY,
  code              varchar(20) NOT NULL,
  label             varchar(255) NOT NULL,
  description       text,
  duty_rate         decimal(5,4) DEFAULT 0,
  active            tinyint DEFAULT 1 NOT NULL,
  datec             datetime DEFAULT CURRENT_TIMESTAMP,
  tms               timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=innodb;

CREATE TABLE llx_internalhts_hts_mapping(
  rowid             integer AUTO_INCREMENT PRIMARY KEY,
  fk_product        integer NOT NULL,
  fk_hts_code       integer NOT NULL,
  country_origin    varchar(2),
  customs_value     decimal(24,8) DEFAULT 0,
  weight_kg         decimal(10,4) DEFAULT 0,
  active            tinyint DEFAULT 1 NOT NULL,
  datec             datetime DEFAULT CURRENT_TIMESTAMP,
  tms               timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  fk_user_creat     integer,
  fk_user_modif     integer,
  UNIQUE KEY uk_hts_mapping_product (fk_product)
) ENGINE=innodb;

CREATE TABLE llx_internalhts_invoice(
  rowid             integer AUTO_INCREMENT PRIMARY KEY,
  ref               varchar(30) NOT NULL,
  entity            integer DEFAULT 1 NOT NULL,
  ref_ext           varchar(255),
  fk_soc            integer,
  fk_shipment       integer,
  invoice_type      varchar(20) DEFAULT 'standard',
  date_creation     datetime DEFAULT CURRENT_TIMESTAMP,
  date_invoice      date,
  date_due          date,
  status            smallint DEFAULT 0 NOT NULL,
  total_ht          decimal(24,8) DEFAULT 0,
  total_ttc         decimal(24,8) DEFAULT 0,
  total_weight_kg   decimal(10,4) DEFAULT 0,
  total_packages    integer DEFAULT 0,
  note_private      text,
  note_public       text,
  model_pdf         varchar(255),
  last_main_doc     varchar(255),
  import_key        varchar(14),
  extraparams       varchar(255),
  datec             datetime DEFAULT CURRENT_TIMESTAMP,
  tms               timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  fk_user_author    integer,
  fk_user_modif     integer,
  fk_user_valid     integer,
  INDEX idx_internalhts_invoice_ref (ref),
  INDEX idx_internalhts_invoice_fk_soc (fk_soc),
  INDEX idx_internalhts_invoice_fk_shipment (fk_shipment),
  INDEX idx_internalhts_invoice_status (status)
) ENGINE=innodb;

CREATE TABLE llx_internalhts_invoice_line(
  rowid             integer AUTO_INCREMENT PRIMARY KEY,
  fk_internalhts_invoice integer NOT NULL,
  fk_product        integer,
  fk_hts_code       integer,
  product_type      smallint DEFAULT 0,
  description       text,
  hts_code          varchar(20),
  country_origin    varchar(2),
  qty               decimal(10,4) DEFAULT 1,
  unit_price        decimal(24,8) DEFAULT 0,
  customs_value     decimal(24,8) DEFAULT 0,
  weight_kg         decimal(10,4) DEFAULT 0,
  packages          integer DEFAULT 0,
  total_ht          decimal(24,8) DEFAULT 0,
  rang              integer DEFAULT 0,
  special_code      integer DEFAULT 0,
  fk_unit           integer,
  import_key        varchar(14),
  extraparams       varchar(255),
  datec             datetime DEFAULT CURRENT_TIMESTAMP,
  tms               timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_internalhts_invoice_line_fk_invoice (fk_internalhts_invoice),
  INDEX idx_internalhts_invoice_line_fk_product (fk_product),
  INDEX idx_internalhts_invoice_line_fk_hts (fk_hts_code)
) ENGINE=innodb;

INSERT INTO llx_c_hts_codes (code, label, description, duty_rate, active) VALUES
('0101.10.00', 'Live horses, asses, mules and hinnies - Horses - Pure-bred breeding animals', 'Live horses, pure-bred breeding animals', 0.0000, 1),
('8471.30.01', 'Portable automatic data processing machines, weighing not more than 10 kg', 'Laptops and portable computers under 10kg', 0.0000, 1),
('6203.42.40', 'Men\'s or boys\' trousers and shorts, of cotton, not knitted', 'Men\'s cotton trousers and shorts, woven', 0.1645, 1),
('9403.60.80', 'Other wooden furniture', 'Wooden furniture not elsewhere specified', 0.0000, 1);