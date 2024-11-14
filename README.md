# DateClutch
Grab the date you want.

# Installation

1. Download the DateClutch source code.
2. Have Composer download PHP package requirements with `composer update`.
3. Configure the **database connection** and **email server settings** in php/config.php (copy php/config_template.php)
4. Ensure that the apache2 rewrite module is installed with `sudo a2enmod rewrite` and then restart apache `systemctl restart apache2`.
5. Create the database tables in `../sql/make_tables.sql`.
6. You may require installing a connector between MariaDB and PHP.

Go to the home page /start and create the administrator account.
