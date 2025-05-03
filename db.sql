create database udhiram;
use udhiram;
create table patient(attender varchar(30),phone varchar(12),patient varchar(20),blood varchar(5),doctor varchar(30),regno varchar(30),hospital varchar(30), address varchar(200));
alter table patient add column report BLOB;
alter table patient add column id int AUTO_INCREMENT PRIMARY KEY;
create table blood_bank(bank varchar(30),phone varchar(12),regno varchar(20),address varchar(20),address1 varchar(20),address2 varchar(20),proof blob);
alter table blood_bank add column id int AUTO_INCREMENT PRIMARY KEY;
CREATE TABLE tracks (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    patient_id INT(11) DEFAULT NULL,
    bank_id INT(11) DEFAULT NULL,
    bank_name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    bank_address VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    donating_units INT(11) NOT NULL,
    action VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
