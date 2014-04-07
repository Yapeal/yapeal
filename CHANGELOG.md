# CHANGELOG #

## 2012-02-19

(default) Fixed issue with walking for KillLog APIs assuming kills are sorted.

Updated *.md files to reflect moving everything over to Source Forge.

## 2012-01-25

(default) Fixed extra commas that were missed in conversion of utilAccessMask to
using multiple inserts.

Deleted bogus class/api/corp/corpCalendarEventAttendees.php as that API does not
exist. Everyone should delete everything in their class/api/corp/ directory
before re-installing to insure old file is removed completely so the old file
does not continue to cause errors.

Added and un-added extended parameter for corp/MemberTracking API until CCP
fixes issues with it not working correctly. Since players can't make or update
keys to allow extended data but API has been changed to provide only limited
data Yapeal will report invalid API XMLs and log many missing required field
errors for the API. Deactivate the API on your corps for now to prevent the
extra error logging as the API data in the database isn't being updated either
at this time until CCP fixes issues on their side.

Converted CHANGELOG to CHANGELOG.md.

Converted COPYING and COPYING-LESSER to markdown format as well.

Updated README.md to reflect new file names.

## 2011-12-17

(default) Updated char/ContactList to work with updated API that now includes
alliance and corporation rowsets. A database update is required so everyone
needs to run install/createMySQLTables.php.

## 2011-12-12

(default) Started work on adding a new feature to MarketOrder APIs to allow
Yapeal to optionally upload data to market sites like EMK, etc.

## 2011-12-11

(default) Updates to all of the install/*.xml files to include reference to the
xmlschema03.xsd so editors that understand XSDs can validate the xml files and
offer syntax help when editing. It took several tries but finally got it right.

## 2011-12-10

(default) Added 'singleton' column database tables etc to work with the new
attribute added to KillLog APIs. Make sure to run install/createMySQLTables.php
since there is a database update required.

Finally found and fixed error that was stopping WalletJournals,
WalletTransactions, and KillLogs that was keep them from walking correctly and
cause near infinite loop in those API classes. Everyone needs to do update as
this bug does cause a large CPU usage issue and greatly slows down Yapeal.

## 2011-11-06

(default) Updated ADOdb to version 5.14.

Merged, reverted, remerge, ... I'm really not sure what I ended up doing but
Yapeal now has a new logging system based on log4php. It's configure with the
new config/logger.xml file.

class/YapealErrorHandler.php now is just sets up the new logger and does hook
for standard PHP errors. Yapeal use to make a lot of changes and overriding of
the local error handling in PHP which meant it didn't always play nice with
others. It still makes some changes but is a little less heavy handed about it
now.

I'd like to thank Zaepho for letting me know about log4php as it kept me from
having to roll something myself that wouldn't have been nearly as easy of use
when I tried doing some other things with Yapeal. To find out more about it
check out the web site http://logging.apache.org/log4php/.

## 2011-11-03

(default) Changed class/YapealQueryBuilder.php to not just track number of rows
in inserts/upserts but also size in bytes so it limits packet size when sending
them to MySQL to help people work around the default low level server is set to.

Turned off autoStore in YapealApiCache::cacheXmlDatabase() to prevent NOTICE: No
rows for utilXmlCache error.

## 2011-11-01

(default) Fixing WARNING error many people are having with long options in
yapeal.php since it seems even in Linux it was uncommon to be able to use them
before PHP 5.3.

## 2011-10-31

(/) Switch to Mercurial is done and all development will continue in it.

## 2011-10-17

(trunk/) Added new 'status' column to utilAccessMask table. This column can be
used to filter APIs by how well they are supported by Yapeal. This column is a
bitmap with the following defined values:
NOT_WORKING = 1, XSD_ONLY = 2, WIP = 4, TESTING = 8, COMPLETE = 16

NOT_WORKING - Means the API is know but nothing works in Yapeal yet.

XSD_ONLY - The XSD has been done but nothing else works yet. Yapeal will cache
the XML but does nothing with it and you may still receive some error messages.

WIP - Someone is actively working on the API but the code is incomplete and lots
of errors are to be expected and data may or may not make it into the database.

TESTING - Whomever has been working on the code for this API believes it is
working but it needs more testing to track down any remaining bugs. Data will be
in database but it is possible some edge cases may still cause errors and
missing data.

COMPLETE - This API should be working fully and no errors are expected.

Added new methods to class/util/AccessMask.php to allow getting list of section
APIs or a section mask. The new methods use the new Yapeal support status column
that has been added to table.

Updated ActiveAPIMask in install/*.xml to include APIs with status of COMPLETE.

## 2011-10-16

(trunk/) Added inc/usage.php to replace the per script functions in the install/
scripts.

Cleaned up install/ by deleting all the old scripts that were only used by the
old GUI installer.

Fixed name on pic/yapeagreen.png to yapealgreen.png.

Moved many of the functions from inc/common_backend.php into the other assocated
classes as static functions and the last couple to yapeal.php. Deleting
inc/common_backend.php as it has out lived it's time. Its one of the last old
timers.

Added 'deny from all' .htaccess files to everything but root directory and
pics/. Yapeal still isn't meant to be installed in a web server accessible
location but reality is that's where many people try to put it or have to.

Yapeal now understands two environmental settings: YAPEAL_BASE and YAPEAL_INI.

YAPEAL_BASE can be used to set the base directory where yapeal can be found. If
it isn't set Yapeal will do it's best to find the base directory on it's own.

YAPEAL_INI can be used to set the location and name of yapeal.ini. The command
line options -c, --config can be used to override it and if it isn't set Yapeal
tries to use inc/yapeal.ini just below yapeal base.

Noticed many of the path constants were not use or only used once or twice so
refactored everything and dropped many of them from inc/common_paths.php.

Deleted unused class/CurlRequest.php.

## 2011-10-12

(trunk/) Moved getSettingsFromIniFile() into own file in install and refactored
the other scripts to start using it.

Moved parseCommandLineOptions() into own file in install and refactored
the other scripts to start using it.

changed inc/common_backend.php to use new install/getSettingsFromIniFile.php

moved new function scripts into inc/ and updated everything to use at new
location.

## 2011-10-10

(trunk/) Updated INSTALL.txt to reflect changes to the scripts in install/

## 2011-10-09

(trunk/) An addition refinement on issue 76 to take care of double '/'s.

Changed install/createMySQLTables.php to be human friendly script and not
just a quick background hack to work with the old GUI frontend installer. You
can find out more about using it with createMySQLTables.php -h.

createMySQLTables.php also now saves the SQL files to cache/ADOdb/ instead of
directly in cache/

Updated install/createMySQLDatabase.php, install/testForMySQLDatabasePrivs.php
like the other scripts. WIP on the other ones not used in install instructions.

## 2011-10-08

(trunk/) Added new parserOptions() function to yapeal.php and updated usage().
Yapeal now should be better at handling command line options add give version
information even when exported from SVN. Yapeal.php now accepts long options on
most OSes if running newer versions of PHP.

Changed install/checkForRequirements.php to be human friendly script and not
just a quick background hack to work with the old GUI frontend installer. You
can find out more about using it with checkForRequirements.php -h.

Updated class/LoggingExceptionObserver.php, class/PrintingExceptionObserver.php,
and yapeal.php to make 'Code:' part of exception message optional.

Updated class/YapealNetworkConnection.php to make error messages for timeouts
etc go to notice log instead.

Replaced YAPEAL_CURL_TIMEOUT with class constant in
ext/eac_httprequest/eac_httprequest.curl.php.

Function-alized inc/common_backend.php to make code clearer and to aid in any
future moves to making into class.

## 2011-10-06

(trunk/) Did some clean up on error message formatting in
class/YapealErrorHandler.php.

Updated YapealErrorHandler::print_on_command() to work with new formatting and
dropping extra parameters since always used defaults anyway.

Updated YapealErrorHandler::elog() to work with new formatting.

Updated yapeal.php for new error message formatting.

Updated class/LoggingExceptionObserver.php for new error message formatting.

Updated class/PrintingExceptionObserver.php for new error message formatting.

Added new is_callable() guard to attach() and detach() in
class/YapealApiException.php and class/ADODB_Exception.php.

## 2011-10-05

(trunk/) Fix for issue 86. Changed to text column instead.

Another try at fixing issue 76.

## 2011-09-22

(trunk/) Additional changes for issue 83.

## 2011-09-21

(trunk/) Fix for issue 83. addActiveAPI() and deleteActiveAPI() had not been
updated for CAK.

## 2011-09-18

(trunk/) Fixed many bugs in the class/util/Registered* classes. They have also
been made a little smarter and will try to fill in columns from other database
tables when making a new row if the info is available in the API tables etc.

Fixed typo in install/util.xml which caused unknown API in char for
UpcomingCalendarEvents in cache code.

Made a few updates to INSTALL.txt and README.

## 2011-09-17

(trunk/) Fixed missing columns from query in SectionAccount::getAPIQuery() for
the ignored mode.

Updated all the *.php with improved include guard which works for all PHP_SAPI
types.

Updated yapeal.php to use new don't include guard and better PHP_SAPI detection.

Did some refactoring on yapeal.php inc/common_*.php to simplify things and move
many of the tests done in inc/common_backend.php over to
install/checkForRequirements.php instead. These changes will be needed for some
planned future changes to Yapeal but were also just some long overdue code
cleanups.

Fixed several errors in the install/*.xml files mostly having to do with the new
APIs.

Fixed util* tables so they no longer try to use ENUMs to fix fails during
database updates with install/createMySQLTables.php. Will try to add them back
once xmlschema project can be finished and used in Yapeal. May need to drop all
util* tables except for the utilRegistered* tables to get tables to update
correctly the first time.

## 2011-09-16

(trunk/) Fixed notice in SectionCorp.php and SectionChar.php about undefined
$mask variable.

## 2011-09-15

(trunk/) Fixed issue 81 in install/util.xml.

## 2011-09-14

(trunk/) merged branches/keys/ into trunk/

(branches/keys/) Has now been merged with trunk/ and being deleted along with
some of the other branches from keys.

## 2011-09-13

(branches/keys/) Fixed errors in contract and notificationTexts XSDs.

## 2011-09-12

(branches/keys/) Added support for contracts. contractItems and ContractBids still needs to be added.

## 2011-09-11

(branches/contracts/) Deleted unneeded prepareTables() that was throwing away
history.

(branches/notification) Deleted branch as it's already fully integrated into
keys now and unneeded.

(branches/keys/) Refactored the per section parserAPI() methods into a single
one in class/api/AApiRequest class instead. Had to refactor some things in the
per account APIs as well but should help make code much clearer.

Added my public CAK to the SQL in install/util.xml for the utilRegisteredKey
table and changed account section to be active in utilSection.

Did some clean up on some of the SQL in install/util.xml which allows less
manual SQL to be used.

Started working on several of the install/*.php scripts to convert them for CAK.
addTestCharacter.php and addTestCorporation.php should now work but the new
addTestKey.php is still a WIP. addTestUser.php has been deleted.

## 2011-09-03

(branches/keys/) Updated the utilRegistered* classes to use new AccessMask class.

Finished updating error handling in AAccount, AChar, and ACorp classes to handle
the new errors added for custom keys. Also update char and corp error handling
to work with the register mode setting from config/yapeal.ini.

Made some changes to the Section* classes to try making notice log quieter
again.

## 2011-08-30

(branches/keys/) Updated class/util/CachedInterval.php to be less buggy and more
useful.

Added new AccessMask wrapper class to handle new table in util.

Updated YapealAPICache class to use CachedInterval class.

install/util.xml Fixed typo in SkillInTraining row.

## 2011-08-25

(branches/keys/) Merged branches/notification/ into keys.

(branches/keys/) Updated AccountStatus API files to reflect removal of userID.
Now uses keyID instead of userID.

Updated SQL in class/api/charMailBodies.php to avoid asking for bodies that are
already in the database.

(admin/) Updated phing/build.xml with some new test targets and re-structured
some of the existing ones.

## 2011-08-24

(branches/notification/) Made updates to some of the SQL to better use primary
keys.

## 2011-08-22

(branches/notification/) Added new branch to integrate new notificationTexts API
from patrick at nospam ch.tario.org into Yapeal. Thanks for the help.

## 2011-08-20

(branches/keys/) Update util.xml to set assetLists for 6 hours instead of 24.

Dropped non-API legecy column 'changed' from MarketOrder tables.

## 2011-08-16

(branches/keys/) Did some refactoring on yapeal.php, ASection.php, and
Section*.php files to make them less interdependent.

## 2011-08-14

(branches/keys/) Fixed a couple of errors one of my alpha testers found.

install/char.xml added accountKeyBridge table and updated accountCharacters.
APIKeyInfo table added default to expires.

cache/account/APIKeyInfo.xsd updated type to allow 'Account'.

First try at conversion for corp/ section.
Fixed missed $crp to $chr conversion in class/SectionCorp.php.

Fixed error in cache/corp/ContainerLog.xsd.

Updated masks in utilSections table to reflect APIs that haven't been
implemented in Yapeal yet so notice log doesn't fill up with meaningless noise.

## 2011-08-13

(branches/keys/) Lots of work on getting char section working. It's working
without problems in testing now.

Added what should be a fix for the error reported in thread with
charAttributeEnhancers table from install/char.xml. You can read more about the
error in
http://www.eveonline.com/ingameboard.asp?a=topic&threadID=904182&page=23#669

Have some bugs with how account APIs work but does get APIs just gets them
multiple times with each character in the 'account' type keys. With the changes
to APIKeyInfo to add type="account" to it I'll be having to go back over it
anyway.

Added new rawQuantity column to AssetList APIs. Found out about that change when
I got invalid XML errors during testing. CCP had said something about it in
contracts but didn't know it was being added to assets as well.

## 2011-08-01

(branches/keys/) Lots of work done on new key stuff after taking first week to
form ideas and some kind of plan. Things that have been changed in random order:
utilRegisteredKey table added
class/util/RegisteredKey.php added
accountAPIKeyInfo table added
accountCharacters now sub-table of APIKeyInfo with schema changes.
cache/account/APIKeyInfo.xsd added and cache/account/Character.xsd removed.
utilAccessMask table added
utilCachedInterval table update for API changes.
utilRegisteredCharacter table updated for custom keys
utilRegisteredCorporation table updated for custom keys
utilRegisteredUser table dropped as no longer use with custom keys
class/util/RegisteredUser.php removed
class/ASection.php added
class/Section*.php changed to use ASection class (WIP)

Currently account, eve, map, server sections have been converted to work with
new custom key and activeAPIMask system. Work is still in progress on char and
corp stuff. The new APIs that aren't part of custom key changes are being
ignored for now until everything is working again.

## 2011-07-23

(branches/keys/) Created new branch from trunk/ to start work on new custom API
key stuff.

## 2011-07-11

(trunk/) Updated WalletTransactions for new journalTransactionID field that was
add. Thanks for the heads up on that CCP :P

(trunk/) Replaced config/cacert.pem with new config/eveonline.crt to see if
it'll work better for people. The old file had general certs for all CAs vs new
one has just the chain needed for Eve APIs. Hopefully this will reduce the
number of errors being seen.

## 2011-04-19

(trunk/) Fixed for issue 71 where transactions involving 2 wallets in the same
corporation would only show once in DB table when corporation is buying and
selling stuff to itself etc do to table primary key not including accountKey.

## 2011-04-11

(trunk/) Updated cache/char/CharacterSheet.xsd to reflect change in skills
rowset from using 'unpublished' to using 'published'.

(trunk/) Updated install/char.xml to reflect change from 'unpublished' to
'published'.

(trunk/) Updated class/api/charCharacterSheet.php for change from 'unpublished'
to 'published'.

(trunk/) Updated many of the class/api/*.php files to take advantage of the new
insert only functionality of class/YapealQueryBuilder.php.

(trunk/) Add pics/Yapeal-256x80.png contributed by Eonra.

(branches/cloud/) Saving some work done.

## 2011-02-26

(trunk/) Changed default for proxy in A*::getProxy() to use https.

(trunk/) Relaxed curl timing to allow it the time it needs to fully check certs.

(trunk/) Updated cache/char/Medals.xsd to allow for same medal being rewarded
more than once.

(trunk/) Updated cache/char/Medals.xsd so second <rowset/> for otherCorporations
is allowed.

(trunk/) Fixed minor formatting problem in per class constructors.

## 2011-02-21

(trunk/) Updated AccountStatus.xsd to allow for GTC offers.

## 2011-02-19

(trunk/) Take out some old test messages that had mistakenly been left in
WalletJournal and WalletTransactions API classes.

## 2011-02-17

Fixed an interdependency between class/api/A*.php and the per api instance
classes where error messages in constructors on parent were dependent on
$this->api which was defined in child but dependent on $this->section from
parent. The error message never are actually used in Yapeal since section file
always passed correct params but could have tripped up anyone trying to modify
or replace them.

(branches/incursion/) Merged in branches/https/ changes.

## 2011-02-05

(branches/https/) Made changes to convert Yapeal to use https. HTTP proxies will
no longer work with Yapeal but HTTPS proxies should. Future versions will allow
for per user/char/corp configuring of certificate file to use for now
self-signed ones need to be either added to config/cacert.pem or replace it if
using proxy for all connections.

(branches/incursion/) Merged some changes from trunk/ into incrusion branch like
the fix for issue 65 and issue 66.

(trunk/) Changed all references to eve-online.com over to eveonline.com as first
step in getting ready for https conversion.

(trunk/) Merged in change from branches/incrusion/ for bad hash that was being
used in class/YapealApiCache.php.

Users should clear their XML cache(s) file, database, or both depending on which
they use. Linux users can clear their file cache by doing the following:

rm -R cache/*.xml

Windows users can select the XML files (not XSDs) on a folder by folder basis
and delete them from the cache/* folders.

All users will also need to clear the utilXMLCache database table using:
truncate utilXMLCache;

Another try at fully fixing issue 65.

## 2011-02-04

(branches/incursion/) Fixed bug in class/YapealApiCache.php where hash being
used wasn't always unique for all cached XML files because the owner was not
being included in it.

Users should clear their XML cache(s) file, database, or both depending on which
they use. Linux users can clear their file cache by doing the following:

rm -R cache/*.xml

Windows users can select the XML files (not XSDs) on a folder by folder basis
and delete them from the cache/* folders.

All users will also need to clear the utilXMLCache database table using:
truncate utilXMLCache;

## 2011-01-31

(branches/incursion/) Changed all references to eve-online.com over to
eveonline.com as first step in getting ready for https conversion.

## 2011-01-27

(branches/incursion/) Copied over some of the changes from WalletJournal into
WalletTransactions as well.

## 2011-01-25

(branches/incursion/) Made several changes to WalletJournal for char and corp to
work better with new caching. Starting with I pulled in revision 1146 fix from
trunk/.

Fixed error where corp APIs were check char cachedUntil times in utilCachedUntil.
This was a nasty little bug that took a while to spot in class/SectionCorp.php.
It should cut down on a few API errors plus get corp APIs at correct times again.

## 2011-01-24

(branches/incursion/) Updated corp/, eve/, map/, and /server/ XSDs to do more
checks on the XML. Can now catch more error cases.

## 2011-01-23

(branches/incursion/) Updated account/ and char/ XSDs to do more checks on the
XML. Can now catch more error cases. Work is continuing on doing the same for
the others.

## 2011-01-22

Branched trunk/ to work on some additions/fixes for Incursion.

(branches/incursion/) Added option to YapealQueryBuilder::store() to allow pure
insert instead of just upsert.

Actually committed possible fix for (issue 65). Sorry it didn't get put out a
couple days ago like I thought it had.

Added fix for 'bad behaviour with limited key requesting fullkey apipages'
(issue 66)

## 2011-01-15

Fixed error message when install/createMySQLTables.php was run without parameters.

## 2011-01-11 (rev 1137)

Did some code cleanup on class/ADODB_Exception.php

Cleared out some old code that was used when trying to make install/*.php
scripts run in CGI.

Did some updates to DocBlocks to move classes into correct subpackages.

Updated copyright (c) to reflect 2011.

Added check in class/api/AChar.php and ACorp.php to insure that new cacheUntil
returned during API errors conditions is always in the future. In some cases
with API errors 101, 103, 115-117, 119 the new cachedUntil date/time can be in
the past which caused repeated tries to get API before servers are ready.

## 2011-01-10 (rev 1136)

Added new utilGraphic table and removed graphic and graphicType columns from
utilRegisteredCharacter and utilRegisteredCorporation tables. New class to
manage table can be found in class/util/Graphic.php

Removed corporationID and corporationName from utilRegisteredCharacter as it was
not needed and could cause record mismatches with both utilRegisteredCorporation
and accountCharacters tables. I'm try to really optimize the util* tables as
Yapeal spends most of it's time accessing them. I've been considering remove the
name columns from character and corporation as well but feel that the small
improvements in speed seen does not seems to warrant make manual table
management that much more difficult.

Updated all the install/*.xml to use ENGINE = InnoDB in <opt> instead of the
varies versions with different case and spacing.

Made changes to several of the scripts in install/ to make them more user
friendly for anyone using them for manual install from archives or SVN.

Updated README and added new INSTALL file with instructions for installing
Yapeal manually.

Changed name of INSTALL to INSTALL.txt because one OS (Windows c$!p) can't
figure out how to use case differences to tell files apart from directories.

## 2011-01-09 (rev 1136)

Adding this changelog in hopes that I might actually use it.

Replaced constant YAPEAL_MAX_UPSERT with class constant
YapealQueryBuilder::MAX_UPSERT.

YapealApiCache::cacheXml() now handles adding some randomness to the cachedUntil
date/time stored in utilCachedUntil table. This randomness use to be decided in
CachedUntil::cacheExpired() but it was felt that with new system for coming up
with cachedUntil using cachedInterval table and ignoring cachedUntil from API
server the old system of randomly decided to delay getting the API was wasteful
of database resources. The random delay can now be pre-calculated and added to
the stored value instead. This should also make designing a service to replace
running yapeal.php from crontab easier.

Added new static methods getList() and setKeep() to YapealErrorHandler class.
These new methods will let application developers to turn on an internal logging
of all errors triggered while Yapeal is running. They can then access the list
and display or otherwise use it however they choose. Care should be taken when
using this as the list can become very large quickly in cases where Yapeal is
mis-configured or API servers return lots of errors.

Fixed mixed line endings in ext/eac_httprequest/ files which caused Yapeal to
fail without any errors when trying to get XML from APIs for some developers.

Updated usage() in yapeal.php to reflect -d command line optional no longer
available.
