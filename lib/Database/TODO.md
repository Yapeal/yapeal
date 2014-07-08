# TODO #

Things that need done to fix database code.

- ADOdb must died of course.
- Re-write using PDO instead.

## PDO conversion ##

Things needed to replace ADOdb:

- Metadata queries to get column names, defaults etc using information_schema
tables.
- Make Interface and wrapper for PDO that include just the special queries that
Yapeal uses.
- Come up with list of common queries that Yapeal will need.
    - getOneCell()
    - getOneRow()
    - getAll()
    - getColumnMetadata()
    - getColumnsMetadata()


## API Implementation (32 of 80) ##

### Accounts (2 of 3)


| Endpoint              | Implemented |
|-----------------------|-------------|
| Account/AccountStatus | yes         |
| Account/APIKeyInfo    | yes         |
| Account/Characters    | no          |

### Character (12 of 31)

| Endpoint                    | Implemented |
|-----------------------------|-------------|
| Char/AccountBalance         | yes         |
| Char/AssetList              | no          |
| Char/CalendarEventAttendees | no          |
| Char/CharacterSheet         | yes         |
| Char/ContactList            | yes         |
| Char/ContactNotifications   | yes         |
| Char/Contracts              | no          |
| Char/ContractItems          | no          |
| Char/ContractBids           | no          |
| Char/FacWarStats            | no          |
| Char/IndustryJobs           | no          |
| Char/KillMails              | no          |
| Char/Locations              | no          |
| Char/MailBodies             | no          |
| Char/MailingLists           | yes         |
| Char/MailMessages           | yes         |
| Char/MarketOrders           | yes         |
| Char/Medals                 | no          |
| Char/Notifications          | yes         |
| Char/NotificationTexts      | no          |
| Char/PlanetaryColonies      | no          |
| Char/PlanetaryPins          | no          |
| Char/PlanetaryRoutes        | no          |
| Char/PlanetaryLinks         | no          |
| Char/Research               | yes         |
| Char/SkillInTraining        | no          |
| Char/SkillQueue             | yes         |
| Char/Standings              | no          |
| Char/UpcomingCalendarEvents | no          |
| Char/WalletJournal          | yes         |
| Char/WalletTransactions     | yes         |

### Corporation (8 of 27)

| Endpoint                  | Implemented                |
|---------------------------|----------------------------|
| Corp/AccountBalance       | yes                        |
| Corp/AssetList            | no                         |
| Corp/ContactList          | yes                        |
| Corp/ContainerLog         | no                         |
| Corp/ContractBids         | no                         |
| Corp/ContractItems        | no                         |
| Corp/Contracts            | yes                        |
| Corp/CorporationSheet     | no                         |
| Corp/FacWarStats          | no                         |
| Corp/IndustryJobs         | no                         |
| Corp/KillMails            | no                         |
| Corp/Locations            | no                         |
| Corp/MarketOrders         | yes                        |
| Corp/Medals               | no                         |
| Corp/MemberMedals         | no                         |
| Corp/MemberSecurity       | no                         |
| Corp/MemberSecurityLog    | no                         |
| Corp/MemberTracking       | yes (limited and extended) |
| Corp/OutpostList          | no                         |
| Corp/OutpostServiceDetail | no                         |
| Corp/Shareholders         | no                         |
| Corp/Standings            | no                         |
| Corp/StarbaseList         | yes                        |
| Corp/StarbaseDetail       | yes                        |
| Corp/Titles               | no                         |
| Corp/WalletJournal        | no                         |
| Corp/WalletTransactions   | yes                        |

### Eve (4 of 13)

| Endpoint                   | Implemented |
|----------------------------|-------------|
| EVE/AllianceList           | no          |
| EVE/CertificateTree        | no          |
| EVE/CharacterAffiliation   | no          |
| EVE/CharacterID            | no          |
| EVE/CharacterInfo          | yes (public & private)      |
| EVE/CharacterName          | no          |
| EVE/ConquerableStationList | yes         |
| EVE/ErrorList              | yes         |
| EVE/FacWarStats            | no          |
| EVE/FacWarTopStats         | no          |
| EVE/RefTypes               | yes         |
| EVE/SkillTree              | no          |
| EVE/TypeName               | no          |

### Map (4 of 4)

| Endpoint          | Implemented |
|-------------------|-------------|
| Map/FacWarSystems | yes         |
| Map/Jumps         | yes         |
| Map/Kills         | yes         |
| Map/Sovereignty   | yes         |

### Server (1 of 1)

| Endpoint            | Implemented |
|---------------------|-------------|
| Server/ServerStatus | yes         |

### API (1 of 1)

| Endpoint     | Implemented |
|--------------|-------------|
| API/CallList | yes         |
