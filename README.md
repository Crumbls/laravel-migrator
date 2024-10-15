#  Laravel Migrator

# THIS IS AN EARLY BETA, DO NOT USE THIS.

We wanted to build a Laravel import package to help migrate other systems into Laravel.

We want to release our overall importer and pepper in our WordPress drivers.

Right now, this package connections to a remote connection, creates models, migrations, and resources for
every table. 

### Release date
Until the code reaches a stable version, it will be updated regularly as part of a #buildinpublic campaign.
We always write for the latest stable version.  We are requiring Laravel 11, Filament 3, and PHP 8.2.

### Installation

`composer require crumbls/laravel-migrator`

### Usage

You shouldn't use this yet.  We have only used this internally and are looking to build it out 
so other parties can, but I don't recommend it yet.

Right now, the only driver we are including is the connection driver.

This is how we currently do it.  There's a command in the works to bridge this.

```
	$migrator = app('crumbls-migrator');

	$migrator
		->driver('database')
		->initialize([])
		->connection('remoteconnection')
		->parse()
		->generateModels(force: false)
		->generateMigrations(force: false)
		->generateFilamentResources(force: true)
	;
```