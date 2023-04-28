# 2.0.1

* Fix cache saving items indefinitely when the time was not explicitely set.
* Development: Introduced Matrix Testing to test against different PHP/Wordpress Versions

## Compability

WP-VERSION | PHP 7.2 | PHP 7.3 | PHP 7.4 | PHP 8.0 | PHP 8.1 | PHP 8.2
---------- | ------- | ------- | ------- | ------- | ------- | -------
5.6        | yes     | yes     | yes     | -       | -       | -
5.7        | yes     | yes     | yes     | -       | -       | -
5.8        | yes     | yes     | yes     | -       | -       | -
5.9        | yes     | yes     | yes     | yes     | yes     | -
6.0        | yes     | yes     | yes     | yes     | yes     | -
6.1        | yes     | yes     | yes     | yes     | yes     | yes
6.2        | yes     | yes     | yes     | yes     | yes     | yes

# 2.0.0

## Main Features

* Added caching functionality
* 'Key' and 'Organizer-ID' are now configured globally on the Settings Page
* Added translation support
* Added the displaying of events as cards (as known from the `iframe` embedding of YesTicket)
* Added the displaying of events as slideshow to use for marketing on events
* Updated Plugin Settings Page
    * Guides through the configuration
    * Shortcodes have previews
    * All options are explained
    * Configure your api access there.

## Compability

WP-VERSION | PHP VERSION | STATUS
---------- | ----------- | -------
5.6.0      | 7.2         | works.
5.9.2      | 7.3         | to check.
6.1.1      | 7.4         | to check.
6.1.1      | 8.0         | to check.
6.1.1      | 8.1         | works.

## Other Changes

* Code Refractorings
* Fixed some code warnings
