ALTER TABLE `oxorder`
ADD `BZTRANSACTION` INT( 11 ) NOT NULL DEFAULT 0,
ADD `BZSTATE` ENUM( 'pending', 'paid', 'expired' ) NOT NULL,
ADD `BZREFUNDS` MEDIUMTEXT NOT NULL;

INSERT INTO `oxpayments` (`OXID`, `OXACTIVE`, `OXDESC`, `OXADDSUM`, `OXADDSUMTYPE`, `OXFROMBONI`, `OXFROMAMOUNT`, `OXTOAMOUNT`, `OXVALDESC`, `OXCHECKED`, `OXDESC_1`, `OXVALDESC_1`, `OXDESC_2`, `OXVALDESC_2`, `OXDESC_3`, `OXVALDESC_3`, `OXLONGDESC`, `OXLONGDESC_1`, `OXLONGDESC_2`, `OXLONGDESC_3`, `OXSORT`) VALUES
('oxidbarzahlen', 1, 'Barzahlen', 0, 'abs', 0, 0, 1000, '', 0, 'Barzahlen', '', '', '', '', '', '', '', '', '', -1);