alter table cdr add column `recordingfile` varchar(255) NOT NULL DEFAULT '';
alter table cdr add column `cnum`          varchar(40)  NOT NULL DEFAULT '';
alter table cdr add column `cnam`          varchar(40)  NOT NULL DEFAULT '';
alter table cdr add column `outbound_cnum` varchar(40)  NOT NULL DEFAULT '';
alter table cdr add column `outbound_cnam` varchar(40)  NOT NULL DEFAULT '';
alter table cdr add column `dst_cnam`      varchar(40)  NOT NULL DEFAULT '';
update cdr set recordingfile = replace(userfield,'audio:','');
