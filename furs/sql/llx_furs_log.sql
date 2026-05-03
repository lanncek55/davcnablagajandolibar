CREATE TABLE llx_furs_log (
  rowid integer AUTO_INCREMENT PRIMARY KEY,
  fk_facture integer NOT NULL,
  zoi varchar(255) NOT NULL,
  eor varchar(255),
  date_creation datetime NOT NULL,
  request_xml text,
  response_xml text,
  status smallint NOT NULL DEFAULT 0
) ENGINE=InnoDB;