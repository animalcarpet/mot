SQL scripts
===========

Please ensure one of following 4 types of tags is applied to each SQL script in this folder:

- `[migration]` - Modifies data to unblock the data migration processes.
- `[story]` - Default one, but please ADD this.
- `[NOT-FOR-PRODUCTION]` - Adds or updates test data. Used only for the development environment.
- `[PRODUCTION-APPLIED]` - Records changes that were made directly to production environment that need reflected into development environments.

Examples
--------

- **Migration**: `2015-06-03-VM-10224-transition_status-update[migration].sql`
- **Story**: `2015-06-10-VM-10350-Transition-User-Profile-Edit[story].sql`
- **Not For Production**: `2015-06-12-VM-10375-remove-tester-active-check[NOT-FOR-PRODUCTION].sql`
- **Production Applied**: `2015-07-16-VM-1337-delete-all-prod-data[PRODUCTION-APPLIED].sql`