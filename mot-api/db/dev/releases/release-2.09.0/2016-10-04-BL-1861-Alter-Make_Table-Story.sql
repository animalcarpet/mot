# Add a new column which allows the list of makes to be restricted to the ones that
# the MOT2 testers will need to see when editing or creating vehicles.
# This is an enhancement since the is_verified set has grown to about 490 records and this is too many for the current drop down list.
# The product owner has selected a sub-set of these (70 makes) that need to be available when creating / editing vehicles.
# Anything that exists outside of these common ones (i.e. unverified / user entered make)  will be displayed as is,
# but these will not be available for selection when editing other vehicles.

ALTER TABLE `make` ADD COLUMN `is_selectable` TINYINT NOT NULL DEFAULT 0 AFTER `is_verified`;

