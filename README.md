#  Laravel Migrator

# THIS IS AN EARLY BETA, DO NOT USE THIS.

We wanted to build a Laravel import package to help migrate other systems into Laravel and Filament.

Right now, this package connections to a remote connection, creates models, migrations, and resources for
every table. We do not have it transferring data.  We are trying to get this done as quick as possible, but in
an efficient and error free matter.

If you are actually interested in this package, please comment and we will put extra time into it.

### Release date
Until the code reaches a stable version, it will be updated regularly as part of a #buildinpublic campaign.
We always write for the latest stable version.  We are requiring Laravel 11, Filament 3, and PHP 8.2.


### Installation

`composer require crumbls/laravel-migrator`

`php artisan migrate`

### Usage

You shouldn't use this yet.  We have only used this internally and are looking to build it out 
so other parties can, but I don't recommend it yet.

2024-11-72 :: Converting it into a Filament support package. Visit Migrations within Filament once installed.