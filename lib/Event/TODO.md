# Events TODO #

__NOTE: The event system in Yapeal is still a WIP.__

## Event List ##

Below is the list of standard events that you can expect Yapeal to emit when
running.

- eve_api.start - Occurs when per Eve API class is first called but before doing
    anything. Can be used for once per Eve API initializing etc.
- eve_api.pre_retrieve - Different from above start event for account, char, and
    corp sections where it occurs on each key, char, corp, etc combo.
- eve_api.pre_transform - Occurs once the raw XML data is actually retrieved but
    before any xslt or other transforms have been applied. Can also be used as
    `eve_api.post_retrieve`.
- eve_api.pre_validate - Occurs after things like xslt transforms have changed
    the raw XML but before the transformed XML has been validated. Can also be
    used as `eve_api.post_transform`.
- eve_api.pre_preserve - Occurs before any of the preservers are called. __NOTE:
    Can contain either XML data or error message at this point as both are
    considered valid results. Only occurs if XML is valid.__ Can be used as
    `eve_api.post_validate`.
- eve_api.pre_xml_error - Event occurs when Yapeal determines XML contains error
    message but has NOT preformed any action yet.__ Good place to set
    `isActive = false` for a key or other wise manage keys etc depending on type
    of error.__
- eve_api.post_preserve - Preservers have finished and Yapeal is ready to move
    on. __NOTE: Like pre_retrieve this is per combo for account, char, corp
    sections. Only occurs if `oneShot()` returns true.__
- eve_api.done - Event occurs after all processing is done and per Eve API class
    is ready to return to the caller. Can be used for once per Eve API cleanup.

Just to be clear all the events from pre_retrieve to post_preserve can be issued
multiple times in the account, char, and corp sections and also in
eve/CharacterInfo if you have multiple keys, chars, or corps etc active for
those Eve APIs.
