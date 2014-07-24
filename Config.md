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
    into '\\' on paths if the command does NOT seem to work.

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
file has several sections named `Database`, `Log`, and `Error` in it. It may
also have some additional sections added in the future if needed.

The example has comments for all the settings that you might need to change so
make sure to have a look at it but we will be going over the most common
settings that you will probably want to change below.

### Database Section

The `Database` section in `yapeal.yaml` is where most of the settings are that
need most people will need to change.

The first two settings we will talk about are the `userName` and `password` ones.
Normally the user and password Yapeal uses only needs typical insert, update,
delete, and select access to the data in the tables but during the
initialization of the database and it's tables Yapeal will need create and drop
access to both as well. It is recommended that the user added to the yapeal.yaml
file only has the table data access it needs during normal operation and that a
separate user be used only during initialization which has the required
additional access. In a later step how to override the userName and password
given in the config file during initialization will be explained.

Next we have the `database` setting which gives the name of the database where
Yapeal will look for it's tables. This database table can contain additional
tables used else where in your application but you will need to take care NOT to
create tables that have the same names as the ones Yapeal uses to store the Eve
API data or any of it's admin tables.

If you find there is a conflict Yapeal has another setting called `tablePrefix`
that can be useful. Yapeal will prefix the string from this setting to all the
table names for all of it's operations automatically for you. If this setting is
going to be used it must be done during initialization as well as the tables
must but created with the prefix.

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

And you have
