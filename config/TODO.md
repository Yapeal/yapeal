# Config TODO List #

List of things needed to be done with config files.

## yapeal.yaml ##

To make things easier to understand and find structure should follow basic
namespace rules.

- Base element is: ```Yapeal``` and everything else is inside of it.
- ```Yapeal``` will have the following elements within it:
-- ```parameters``` - Holds settings that are needed to config the services.
        These will be added to the DIC as well.
-- ```services``` - Holds the information needed to create services in DIC.
