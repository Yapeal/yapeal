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
