CREATE DATABASE adhocHomeworkTmp;

USE adhocHomeworkTmp;

CREATE TABLE zips (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    zipcode INT(5) NOT NULL,
    state CHAR(2) NOT NULL,
    county_code CHAR(5) NOT NULL,
    name VARCHAR(20) NOT NULL,
    rate_area INT(2) NOT NULL,
    INDEX zip_index (zipcode)
);

CREATE TABLE plans (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    plan_id CHAR(14) NOT NULL,
    state CHAR(2) NOT NULL,
    metal_level VARCHAR(12) NOT NULL,
    rate DECIMAL(5,2) NOT NULL,
    rate_area INT(3) NOT NULL,
    INDEX state_area (state, rate_area)
);

CREATE USER 'adhocTmpUser@%' IDENTIFIED BY 'Homework';

GRANT ALL ON adHocHomeworkTmp TO 'adhocTmpUser@%';

EXIT;
 
