# YaaS (Yapeal as a service) #

Below are some instructions for securing and setting up an Yapeal Amazon EC2
cloud instance. Understand that it's still a WIP but should get you up and
going.

## Why cloud version?

Since I've started Yapeal the most common problems people seem to have is
finding good hosting with up to date software that don't over commit their
servers which can cause multiple issues for something like Yapeal which doesn't
have the same requirements as a normal webserver. Many people try to use the
bottom teir web server services which put anywhere from 2-5x more web sites per
server as they can truly handle. They also configure them for high output
network bandwidth but little incoming as most sites spend most of their time
serving web pages and not downloading new data. Yapeal downloads a lot of data
from the API servers and uses very little outgoing bandwidth since it more like
a client then a server.

## About the instance

The EC2 image is made to run in a micro instance but could be used in larger
ones if needed. A micro instance should be enough for testing and even
production use by a single user with a few accounts or even a small player
corporation. Larger corporations and alliances may need larger instances. Yapeal
itself uses very little resources outside of some light CPU needs and some
heavier network bandwidth use. The default is to use MySQL database but
switching to MariaDB is recommended and isn't used by default only do to some
limitations in the build tools at this time.

## Getting instance

It is expected that the EC2 image will be made available in the future on
SUSE Studio

## Securing database

The first thing you need to do is secure the MySQL (MariaDB) server by setting a
root pasword, removing the anonumous users, disallow remote root logins, remove
the test database and access to it. This can all be done by the Linux root user
running:

 `mysql_secure_installation`
INSERT INTO `utilRegisteredKey` VALUES (268435455,1,NULL,52,NULL,'I0Z2knzpCbwkTMzkX9IjHJQz6kdkoOPe5wnA4Kv1TyYdcgg4BJpjpoCYufA7t28G'),(8388608,1,NULL,1156,NULL,'abc123'),(67108863,1,NULL,1837,NULL,'m90SLg1WUnDf3v2S6zhTl0vaFOpbmV8O6wAi9Yj14BhKI2jj2gnyKaaxHHE04WYG'),(134217727,1,NULL,18056,NULL,'etpPq2aItwWPyvVCd7ofqX1ce7x9EahFhRZONP3Z5i0ijBSg5BDRnSZ5WEbfwDLN'),(16777215,1,NULL,18061,NULL,'fSKJVaZUmivmZATZMeMV9vugmodr2Ro1fX4tL0WkydKztsyTgVrAa4VKPOqPSfIt');
Enter current password for root (enter for none):
OK, successfully used password, moving on...

Setting the root password ensures that nobody can log into the MySQL
root user without the proper authorisation.

Set root password? [Y/n]
New password:
Re-enter new password:
Password updated successfully!
Reloading privilege tables..
 ... Success!
