# Events TODO #

## Event List ##

- pre Eve API - Accures when per Eve API class is first called but before doing
    anything.
- pre retrieve - Same as above pre event except in account, char, corp sections
    where it accures for each key, char, corp, etc combo. 
- post retrieve - Accures once the raw XML data is received but before any
    processing has started.
- post modify - Accures after things like xslt have changed the raw XML.
- post validate - After XSD etc validated XML. Note: Can contain either XML data or
    error message at this point as both are considered a valid result.
- pre XML error - Event accures when Yapeal detrimines XML contains error message
    but has NOT preformed any action yet.
- pre preserve - Accures before any of the preservers are called. Note: Does not
    accure for XML errors only for data result.
- post preserve - Preservers have finished and Yapeal is ready to move on. Note:
    Like pre retrieve this can be per each combo.
- post Eve API - Event accures after all processing is done and per Eve API class
    is ready to return to the caller.