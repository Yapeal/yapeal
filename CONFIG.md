Configuring Yapeal
==================

Older versions of Yapeal used a mix of 'ini' style and XML configurations files.
To make configuring Yapeal easier than XML configurations but allow more complex
configuration structures than 'ini' allowed Yapeal is now uses an 'yaml' file.

You can think of [Yaml](http://www.yaml.org/) as a super-set of
[Json](http://www.json.org/) but made to be more human friendly.
It also was made for things like configuration files where Json was made for
information transfer between computer applications like a web browser and a web
site.

NOTE:

    All of the following examples will be for Linux style command line
    interface. Windows user in most cases will simply need to change any '/'
    into '\' on paths if the command does NOT seem to work.

## yapeal.yaml

Yapeal's default configuration file is `config/yapeal.yaml` inside the directory
where it was installed. Additionally if it is install as a composer package
under the `vendor/` directory it will also look for a `config/yapeal.yaml` where
`config/` is a sibling directory of `vendor/`. Here an example directory
structure:

```
yourSuperApp/
  config/
    yapeal.yaml
  vendor/
    composer/
    yapeal/
      yapeal/
        config/
          yapeal.yaml
    autoload.php
...
```
Any settings in `yourSuperApp/config/yapeal.yaml` will override settings from
the `yourSuperApp/vendor/yapeal/yapeal/config/yapeal.yaml` if they are both used.

You will find a example configuration file in `config/yapeal-example.yaml` which
can be copied and used as a template if you want. You will see that the example
file has several sections named `Database`, `Error`, `Log`, and `Network` in it.
It may also have some additional sections added in the future if needed.

The example has comments for all the settings that you might need to change so
make sure to have a look at it but we will be going over the most common
settings that you will probably want to change below.

### Database Section

Located in the `Database` section of `yapeal.yaml` are the settings that most
people will need to change.

The first two settings we will talk about are the `userName` and `password` ones.
Normally the user and password Yapeal uses only needs typical insert, update,
delete, and select access to the data in the tables but during the
initialization of the database and it's tables Yapeal will need create and drop
access to both as well. It is recommended that the user added to the yapeal.yaml
file only has the table data access it needs during normal operation and that a
separate user be used only during initialization which has the required
additional access. Later I'll show how to override the userName and password
given in the config file during initialization of the database.

Next we have the `database` setting which gives the name of the database where
Yapeal will look for it's tables. This database table can contain additional
tables used else where in your application but you will need to take care NOT to
create tables that have the same names as the ones Yapeal uses to store the Eve
API data or any of it's admin tables.

If you find there is a conflict Yapeal has another setting called `tablePrefix`
that can be useful. Yapeal will prefix the string from this setting to all the
table names for all of it's operations automatically for you. If this setting is
going to be used it must be done during initialization as well as the tables
must be created with the prefix.

I'll give an example here to make it easier to understand how the settings work.

Let say you have a `config/yapeal.yaml` file that has these settings:

```
Yapeal:
  Database:
    database: yapeal
    password: secret
    tablePrefix: ''
    userName: YapealUser
...
```

And you have the following database table SQL:

```
DROP TABLE IF EXISTS "{database}"."{table_prefix}eveErrorList";
CREATE TABLE IF NOT EXISTS "{database}"."{table_prefix}eveErrorList" (
    "errorCode" SMALLINT(4) UNSIGNED NOT NULL,
    "errorText" TEXT,
    PRIMARY KEY ("errorCode")
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
```

The resulting SQL will like like this:

```
DROP TABLE IF EXISTS "yapeal"."eveErrorList";
CREATE TABLE IF NOT EXISTS "yapeal"."eveErrorList" (
    "errorCode" SMALLINT(3) UNSIGNED NOT NULL,
    "errorText" TEXT,
    PRIMARY KEY ("errorCode")
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
```

with the `{database}` and `{table_prefix}` replaced with the values. The user
name used for the connection will be `YapealUser` and the password `password`.

### Log Section

The only setting anyone is likely to change here is the `threshold` one. You may
during development or initial deployment want to see some additional logging
then switch to less later. Main thing here is to make sure this setting is
always below the setting in `Yapeal.Error.threshold`. For example if setting in
Error section is 400(ERROR) then the setting in Log must be 300(WARNING) or less.

### Network Section

Currently there is only a single setting here which allows you to use Yapeal
with the default main Eve API servers or the test server. It can be useful to
sometimes test things in the more control environment provided by the test
server.

## Command Line Tool

Yapeal comes with a simple command line tool that can initialise the database
and it's tables plus allow you to get a single XML file from the Eve API servers.
Additional features are expected to be added it as time goes on but it already
does what is expected to be its main use initializing the database. You can run
it with `php bin/yc` to access its help. Help for the individual commands can be
accessed as well for example ` php bin/yc help D:I` give you some addition
information about the Database:Init command which we'll be using below. In most
cases commands can be shortened init_Letter:init_Letter of the command as long
as there only one match.

### Database Initialization Using `yc D:I`

To keep the example simple I'll assume you have already set up
`config/yapeal.yaml` with the correct settings Yapeal will need to use during
normal operation. I'll assume that the user does not have `CREATE DATABASE`
and also may not have `CREATE TABLE`. If you looked at the help for the `yc D:I`
command you saw there were the options `-u` and `-p` that can be used. `-u` lets
you set the user name for the connection and `-p` lets you set the password.
Since command line options override the settings from the configuration files by
simple using them you can use a different user during database initialization.
Here an example of doing that:

```
php bin/yc D:I -u root -p superSecret
```

### `yc D:I` Explained

This will be a brief explanation to give you some idea what this command does.
It starts out by trying to find the default `config/yapeal.yaml` or the one
specified on the command line and reading the settings it finds it the file. It
will then override/set any settings that weren't in the config file and check
that the required settings are all available.

After connecting to the database server it will start looking for the
`Create*.sql` files in `bin/sql/` and process them. The order it processes them
in is:

  * CreateDatabase.sql
  * CreateAccountTables.sql
  * CreateApiTables.sql
  * CreateCharTable.sql
  * CreateCorpTables.sql
  * CreateEveTables.sql
  * CreateMapTables.sql
  * CreateServerTables.sql
  * CreateUtilTables.sql
  * CreateCustomTables.sql
 
The first one of course drops then recreates the database and the others do the
same for the tables in each section. The `CreateCustomTables.sql` is of special
note in that it's the only one that doesn't exist by default. It was added to
make it easier during development on Yapeal to add back in the test API keys we
use. You can use it in your application to do the same thing but what might be
more useful is you can add any other tables your application might need instead.

In all of the files when they are load any `{database}` and `{table_prefix}`
strings are replaced with the value from the settings and then it's broke up
into individual SQL statements and sent to the database server. Any bad
statements will cause it to abort processing all remaining SQL and return a
hopefully useful error message.

## Epilogue

Hopefully the above has been enough to get you start with using Yapeal. As
always if you have additional questions you can ask on the forum thread where
I'm sure someone will be willing to help out.

If you have suggestions on improving this please let me know or since Yapeal is
hosted on GitHub just fork it, update and do a pull request.
