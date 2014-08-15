# TODO #

## API Implementation (45 of 85) ##

### Account (3 of 3)


| Endpoint              | Implemented |
|-----------------------|-------------|
| account/AccountStatus | yes         |
| account/APIKeyInfo    | yes         |
| account/Characters    | yes via APIKeyInfo |

### Character (17 of 33)

| Endpoint                    | Implemented |
|-----------------------------|-------------|
| char/AccountBalance         | yes         |
| char/AssetList              | no          |
| char/Blueprints             | yes         |
| char/CalendarEventAttendees | no          |
| char/CharacterSheet         | yes         |
| char/ContactList            | yes         |
| char/ContactNotifications   | yes         |
| char/Contracts              | yes         |
| char/ContractItems          | no          |
| char/ContractBids           | no          |
| char/FacWarStats            | no          |
| char/IndustryJobs           | yes         |
| char/IndustryJobsHistory    | yes         |
| char/KillMails              | no          |
| char/Locations              | no          |
| char/MailBodies             | no          |
| char/MailingLists           | yes         |
| char/MailMessages           | yes         |
| char/MarketOrders           | yes         |
| char/Medals                 | no          |
| char/Notifications          | yes         |
| char/NotificationTexts      | no          |
| char/PlanetaryColonies      | no          |
| char/PlanetaryPins          | no          |
| char/PlanetaryRoutes        | no          |
| char/PlanetaryLinks         | no          |
| char/Research               | yes         |
| char/SkillInTraining        | no          |
| char/SkillQueue             | yes         |
| char/Standings              | yes         |
| char/UpcomingCalendarEvents | no          |
| char/WalletJournal          | yes         |
| char/WalletTransactions     | yes         |

### Corporation (14 of 30)

| Endpoint                  | Implemented                |
|---------------------------|----------------------------|
| corp/AccountBalance       | yes                        |
| corp/AssetList            | no                         |
| corp/Blueprints           | yes                        |
| corp/ContactList          | yes                        |
| corp/ContainerLog         | no                         |
| corp/ContractBids         | no                         |
| corp/ContractItems        | no                         |
| corp/Contracts            | yes                        |
| corp/CorporationSheet     | no                         |
| corp/Facilities           | yes                        |
| corp/FacWarStats          | no                         |
| corp/IndustryJobs         | yes                        |
| corp/IndustryJobsHistory  | yes                        |
| corp/KillMails            | no                         |
| corp/Locations            | no                         |
| corp/MarketOrders         | yes                        |
| corp/Medals               | no                         |
| corp/MemberMedals         | no                         |
| corp/MemberSecurity       | no                         |
| corp/MemberSecurityLog    | no                         |
| corp/MemberTracking       | yes (limited and extended) |
| corp/OutpostList          | no                         |
| corp/OutpostServiceDetail | no                         |
| corp/Shareholders         | no                         |
| corp/Standings            | yes                        |
| corp/StarbaseList         | yes                        |
| corp/StarbaseDetail       | yes                        |
| corp/Titles               | no                         |
| corp/WalletJournal        | yes                        |
| corp/WalletTransactions   | yes                        |

### Eve (5 of 13)

| Endpoint                   | Implemented |
|----------------------------|-------------|
| eve/AllianceList           | yes         |
| eve/CertificateTree        | no          |
| eve/CharacterAffiliation   | no          |
| eve/CharacterID            | no          |
| eve/CharacterInfo          | yes (public & private)      |
| eve/CharacterName          | no          |
| eve/ConquerableStationList | yes         |
| eve/ErrorList              | yes         |
| eve/FacWarStats            | no          |
| eve/FacWarTopStats         | no          |
| eve/RefTypes               | yes         |
| eve/SkillTree              | no          |
| eve/TypeName               | no          |

### Map (4 of 4)

| Endpoint          | Implemented |
|-------------------|-------------|
| map/FacWarSystems | yes         |
| map/Jumps         | yes         |
| map/Kills         | yes         |
| map/Sovereignty   | yes         |

### Server (1 of 1)

| Endpoint            | Implemented |
|---------------------|-------------|
| server/ServerStatus | yes         |

### API (1 of 1)

| Endpoint     | Implemented |
|--------------|-------------|
| api/CallList | yes         |
