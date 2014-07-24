Installing Yapeal
=================

There are several ways to install Yapeal but in all cases you'll need to have a
copy of [Composer](https://getcomposer.org/) installed to install and update
Yapeal and it's dependencies.

NOTE:

    All of the following examples will be for Linux style command line
    interface. Windows user in most cases will simply need to change any '/'
    into '\\' on paths if the command does NOT seem to work.

## Install - Composer

If you are using Composer in your own project then add require line for Yapeal
to your `composer.json` file like any other package:

```
require: "yapeal/yapeal": "master"
```

And do an update in Composer on command line:

```
composer update -o
```

Next you will want to read the configure instructions in [CONFIG.md][1].

## Install - Git

If you are NOT using Composer in your project you can clone Yapeal using
[Git](http://git-scm.com/) from the main project page:

https://github.com/Dragonrun1/yapeal

The following instruction assume you are manually running the commands from a
command line interface like Bash (Linux) or CMD (Windows).

An example Git clone command:

```
git clone https://github.com/Dragonrun1/yapeal.git
```

This will create a `yapeal/` directory in the current path where it is run so
make sure `cd` to the path just above where you want Yapeal.

Now `cd` into the new directory with:

```
cd yapeal
```

All the following instructions will be run from there.

After cloning the project the master branch is checked out by default so if you
are needing one of the other branches first you will wanted to list the remote
branches with:

```
git branch -r
```

Then you will need to checkout the correct branch with:

```
git checkout the-branch
```

Now you need to use Composer to install Yapeal's dependencies by running:

```
php /path/to/composer.phar install -o
```

This will create a vendor/ directory inside of Yapeal and auto-update Composer's
auto-loader.

Next you will want to read the configure instructions in [CONFIG.md][1].

## Install - Zip

You can download a zp file for Yapeal from the main project page at:

https://github.com/Dragonrun1/yapeal

After un-zipping where you want Yapeal installed you will need to run Composer
to install Yapeal's dependencies.

The following instruction assume you are manually running the commands from a
command line interface like Bash (Linux) or CMD (Windows).

```
cd /path/to/yapeal/
```

Now you need to use Composer to install Yapeal's dependencies by running:

```
php /path/to/composer.phar install -o
```

This will create a vendor/ directory inside of Yapeal and auto-update Composer's
auto-loader.

Next you will want to read the configure instructions in [CONFIG.md][1].

[1]: CONFIG.md "CONFIG.md"
