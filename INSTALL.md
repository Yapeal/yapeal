# INSTALLATION #

Below are the instructions for installing [Yapeal][1] from the archives but you
can find instructions about installing from the [Mercurial][2] as well after
them. Only the first couple of steps are different.

## Installing From Archives

If you are installing from one of the [archives][3] (tar.bz2, zip) the following
instructions should be helpful. In most of the general text I will mostly use
unix standards paths which uses "/" and not "\" like is common in Windows. If
you are do an install to Windows you may need to change the paths but the
general idea is the same.

1. Download archive

    These instructions are for the latest archives in archives/ on SourceForge but
    can also be used for any of the branches by simply using the correct
    download in the next couple steps.

    Windows:

    <http://sourceforge.net/projects/yapeal/files/archives/yapeal-latest.zip/download>

    Linux:

    <http://sourceforge.net/projects/yapeal/files/archives/yapeal-latest.tar.bz2/download>

   Which you choose doesn't really matter as both contain the same code and was
   done to make it more convenient for the user when extracting but the
   'tar.bz2' version is smaller.

2. Extract archive

    Windows:
    Use the built-in zip handling of Vista or Win7's Windows Explorer to extract
    Yapeal where you want to put it. A path without any spaces in it is
    recommended as it saves you having to quote things when working from the
    command line. For example `C:\yapeal\` is better than "My Documents" but
    either should work.

    Linux:

    `cd /where/I/want/to/put/it/`

    `tar -jxvf /where/I/downloaded/archive/yapeal-latest.tar.bz2`

3. **Update:** Start cmd.exe (Windows only)

    Since it's assumed from the last step that Windows users aren't at a
    command line yet I'll add in some real short instructions here on starting
    one and getting to the correct directory.

    If you are using classic menus you should have a Run option on the Start
    menu you can click on and just type into the text box:

    cmd &lt;enter>

    This should open a command line window in you default directory. If you
    aren't using classic menus there are ways to add it but they are to long to
    go into here you'll need to do a quick search online for "Adding Command
    Prompt" or "Command Prompt here" which should get you started.

    Now I'm going to assume you extracted Yapeal to `C:\yapeal\`. To move to
    that directory at the command prompt do `cd c:\yapeal\` and you should be
    able to do a dir command and see the files and directories from Yapeal.

    You should now be caught up to the Linux folks.

4. Copy yapeal-example.ini

    At this point you'll want to make a copy of config/yapeal-example.ini to
    config/yapeal.ini.

    Windows cmd.exe:

    `copy config\yapeal-example.ini config\yapeal.ini`

    OR

    In Windows Explorer drag and drop into same folder to make a copy and rename
    it.

    Linux:

    `cp config/yapeal-example.ini config/yapeal.ini`

5. Edit yapeal.ini

    Now you'll need to edit config/yapeal.ini. In Windows use NotePad or another
    editor that works well for code editing (Not WordPad or MS Word). You should
    be able to start NotePad from cmd.exe with the file open by using `notepad
    config\yapeal.ini`. In Linux most editors should work but the more basic the
    better with Vim or Emacs both being good options. A Vim example: `vim
    config/yapeal.ini`

    For most people you'll only need to change the settings in the `[Database]`
    section like database, username, password. Changing `[Cache]`
    `cache_output="both"` maybe useful as would changing `log_level=E_ALL` in
    `[Logging]` during testing. For more information about the settings read the
    comments found in the file.

    Make sure you save your changed and get back to the command line for the
    next step.

6. Update: Easier paths

      To make running the following scripts easier let's change to the install/
      directory.

      Linux and Windows:

      `cd install`

      You should now be in the install/ directory below where you installed
      Yapeal.

7. Check requirements

    Now you can check the settings you just did in the last step by running the
    `install/checkForRequirements.php` script.

    Windows cmd.exe:

    `php.exe -f .\checkForRequirements.php`

    Linux:

    `php -f ./checkForRequirements.php`

    OR

    You can try the following if PHP is in your system path:

    Windows cmd.exe:

    `.\checkForRequirements.php`

    Linux:

    `./checkForRequirements.php`

    If all is well you should get a message about all the tests passing. If not
    than correct any problems found and re-run the script until no more problems
    are found. For help on correcting problems found try the Yapeal wiki and the
    Yapeal thread on eve online forums for help. You can find URLs for them both
    in the README file.

8. **Update:**

    Most of the scripts have been updated to automatically find all
    the settings they need from config/yapeal.ini on their own. I'm going to
    show in the following examples how to include the required parameters on the
    command line but you should be able to run all of them without any
    parameters now.

    I will also assume that you could run the script in the last step without
    use php.exe -f or php -f. If you could not do so you'll need to add "--"
    after the script name as well in the following examples so PHP passes them
    on to the script and does not try using them as options for itself. Here
    is a Linux example to show how it looks:

    `php -f ./checkForRequirements.php -- -V`

    You should get a few lines of output containing the version information from
    `install/checkForRequirements.php`

9. Check MySQL user access

    For this and the following steps that deal with MySQL the examples will use
    the defaults from `config/yapeal-example.ini` for server name, database
    name, user name, and password (localhost, yapeal, YapealUser, secret). Make
    sure to use your settings from `config/yapeal.ini` that you set in step 4
    above.

    You should check the database user has the correct access for the database
    by running the following.

    Windows cmd.exe:

    `.\testForMySQLDatabasePrivs.php -s localhost -u YapealUser -p secret -d yapeal`

    Linux:

    `./testForMySQLDatabasePrivs.php -s localhost -u YapealUser -p secret -d yapeal`

    It should return something this:

    _YapealUser has the needed privileges on the yapeal database._

    If not make sure the user exists in MySQL and has the required access to the
    database you are going to use for Yapeal. The database does not have to
    exist but the user does need the privileges to create it.

10. Create MySQL database (optional)

    Next we'll create the database to be used with Yapeal. This is an optional
    step you can skip if you already have a database ready for Yapeal to use.

    Windows cmd.exe:

    `.\createMySQLDatabase.php -s localhost -u YapealUser -p secret -d yapeal`

    Linux:

    `./createMySQLDatabase.php -s localhost -u YapealUser -p secret -d yapeal`

    Result should be:

    _Database yapeal created successfully!_

    If not re-check the parameters you used and retry steps 7 and 9 again.

11. Add tables to database

    To add all the tables need by Yapeal we'll run install/createMySQLTables.php

    Windows cmd.exe:

    `.\createMySQLTables.php -s localhost -u YapealUser -p secret --database=yapeal`

    Linux:

    `./createMySQLTables.php -s localhost -u YapealUser -p secret --database=yapeal`

    Result should be:

    _All database tables have been installed or updated as needed._

    If you get an error message about problems with the 'util' section try
    dropping the `utilAccessMask` and `utilCachedInterval` tables and run it
    again.

    In this example you should have noticed I use a so called long option
    "--database". They have worked for awhile on Linux but have only been added
    for Windows starting with PHP 5.3.0. The reason I'm pointing it out is if
    you decided to use them and they don't seem to be doing anything or you get
    errors you might try using the short version instead and see if it helps.
    Talking about help you can get some quick help instructions by using the -h
    or --help parameter with any of these commands.

After the above steps everything should be in place to run Yapeal. You'll
probably want to active more of the APIs as only a few are active by default
you'll also need to add key, and optionally character, and corporation
information to the utilRegistered* tables in the database for any of the
account, char, or corp APIs to work. More information about adding information
and activating APIs can be found in the wiki and on the forum thread.

## Installing From Mercurial

To install from the Mercurial repository on GoogleCode or SourceForge first
setup a working copy. This shouldn't be where you plan on run Yapeal from but be
a convenient place like your My Documents (Documents on Vista/Win7) or some
place like it on Linux. The reason to not put your working copy where you plan
to run Yapeal is it will make upgrading it easier without all the extra .hg/
directories, etc. you have with a working copy. For Linux on the command line
you can use:

  `cd /directory/just/above/where/I/want/my/wc/`

  Then:

  `hg clone https://code.google.com/p/yapeal/`

  OR

  `hg clone http://yapeal.hg.sourceforge.net:8000/hgroot/yapeal/yapeal`

Windows users should refer to the documentation for your Mercurial client.
TortoiseHg which you can get from <http://tortoisehg.bitbucket.org/> is IMHO the
best GUI for Windows. You can also get command line versions of Mercurial for
Windows from <http://mercurial.selenic.com/downloads/>. If you are using one of
the command line versions of the Mercurial client you can try these commands
from cmd.exe prompt:

  `cd %userprofile%\"My Documents"` (`%userprofile%\Documents` for Vista/Win7)

  Then:

  `hg clone http://hg.code.sf.net/p/yapeal/code`

Now that you have a working copy you can export from it to where you plan on
running Yapeal.

  `cd yapeal`

  `hg archive /directory/where/yapeal/should/be/installed/`

Now that you have Yapeal in place you can follow the instructions beginning at
step 4 in Installing From Archives to finish install.

Have fun, Dragonaire

[1]: http://code.google.com/p/yapeal/ "Yapeal Home Page"
[2]: http://mercurial.selenic.com/ "Mercurial Home Page"
[3]: https://sourceforge.net/projects/yapeal/files/ "Yapeal Downloads"
