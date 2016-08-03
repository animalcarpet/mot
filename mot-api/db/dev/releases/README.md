# DB upgrade process

DB upgrades are applied in the dev environments by running all sql scripts in the releases directory

DB upgrades are applied in prod-like environments by running only the scripts for the next release with the db_upgrade.sh in the corresponding release directory


## Adding new DB scripts for an upcoming release

If your DB script is intended for dev and prod environments, add it to zz-release-next and the db_upgrade.sh runner (zz-release-next takes the place of 9999-current in the old upgrades folder structure)

If your DB script is intended only for dev environments, ensure it has 'NOT-FOR-PRODUCTION' in the filename, add it to zz-release-next and do not add it to the db_upgrade.sh runner


## Preparing for the upcoming release

Ensure that the scripts intended to be run in prod-like environments are referenced in the db_upgrade.sh in zz-release-next in the correct order.

Rename the folder zz-release-next to whichever release number has been determined for the deployment. *Make sure that the release folder has a leading zero in the middle version component in order to preserve folder ordering* e.g. release-2.02.0 instead of release-2.2.0 (this will ensure that 2.02.0 gets applied before 2.10.0).

Create a new empty zz-release-next folder and a db_upgrade.sh script with no sql script references for people to add new scripts.