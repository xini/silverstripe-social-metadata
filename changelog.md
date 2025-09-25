# Changelog

All notable changes to this project will be documented in this file.

This project adheres to [Semantic Versioning](http://semver.org/).

## [8.5.5]

* fix conditions for @graph notation for multiple ld+json objects

## [8.5.4]

* use @graph notation for multiple ld+json objects to fix Safari's array bug (https://bugs.webkit.org/show_bug.cgi?id=255764) 

## [8.5.3]

* fix retrieving of Goole Maps API key when set via GoogleMapsField's env var

## [8.5.2]

* skip adding site schema data if controller is not a ContentController
* update list of sameAs links derived from social media profiles module 

## [8.5.1]

* only retrieve blog metadata for blog controller 

## [8.5.0]

* switch to https for schema.org

## [8.4.1]

* fix social profiles config for configured-multisites

## [8.4.0]

* add support for [configured-multisites](https://github.com/fromholdio/silverstripe-configured-multisites)

## [8.3.0]

* replace Twitter icon and labels with X

## [8.2.2]

* fix config retrieval for GoogleMapsField

## [8.2.1]

* Fix order of arguments in insertBefore call

## [8.2.0]

* Clean up metadata tab on pages
* Add jonom/silverstripe-text-target-length for page title and description tags

## [8.1.0]

* Change how config default values are set
* Fix canonical URL
* Fix metadata tab

## [8.0.1]

* Change removed method allVersions() to replacement Versions()
* Fix reference to HTMLValue class

## [8.0.0]

* Silverstripe 5 support

## [7.2.0]

* update translations

## [7.1.3]

* Fix MetaImage is secondary to FeaturedImage on BlogPost

## [7.1.2]

* fix issue where URL ends with 'home' breaking canonical links

## [7.1.1]

* fix incorrectly formatted ld+json for sameAs links if social profile merge removes duplicates

## [7.1.0]

* use InternationPhoneNumberField for phone and fax

## [7.0.1]

* wrap object property test condition with empty() to avoid throwing warning in PHP8

## [7.0.0]

* refactor to use SiteTree::MetaComponents() to add tags, removing updateSocialMetaTags hook
* include SameAsLinks in og:same_as
* add mainEntityOfPage to site ld+json data using SiteURL

## [6.1.0]

* add @id to site schema data
* add config and override to include site schema data on a page
* add logic to merge/combine site and page schema data
* add additional SameAs links to site

## [6.0.2]

* fix UTF-8 encoding of schema data

## [6.0.1]

* fix retrieval of social meta value on page object

## [6.0.0]

* fix updateSchemaData hook for site config

## [5.3.0]

* minify json+ld output by default (configurable)

## [5.2.0]

* fix retrieval of MetaTitle in DataObjects using the MetaFields extension
* fix retrieval of fallback values in MetaFieldsDataObjectExtension
* add config option on pages and data objects for using custom meta description fallback fields
* add config option on pages and data objects for falling back to site description

## [5.1.3]

* remove articleBody from blog post jsnon+ld to fix [edge case](https://github.com/silverstripe/silverstripe-framework/issues/9662) and remove duplication of content. Not required according to [Google guidelines](https://developers.google.com/search/docs/data-types/article).

## [5.1.2]

* fix cms fields for multisites site

## [5.1.1]

* fix meta description replacement if description is at the end of the metadata string

## [5.1.0]

* add og:see_also tags for social profiles
* add og:image:[xyz] tags for more image details, fixes #4
* make opening hours more flexible using time fields instead of fixed dropdown
* fix json+ld for openingHours
* fix json+ld for paymentAccepted

## [5.0.6]

* add Name hooks to config.yml blocks allowing other modules to prioritise before/after them

## [5.0.5]

* switch to fork fromholdio/silverstripe-externalurlfield
* fix social profile integration to list all profiles
* fix twitter:site meta tag

## [5.0.4]

* fix social profile integration to list all profiles

## [5.0.3]

* switch to fork fromholdio/silverstripe-externalurlfield

## [5.0.2]

* add configuration for twitter and opengraph image sizes
* fix URLs for twitter and opengraph images
* fix blog integration for categories and tags
* add ExtraMeta support
* fix checking for return values in fallback logic
* fix opengraph URL to use current URL, not custom canonical

## [5.0.1]

* add dependency burnbright/silverstripe-externalurlfield
* change ExternalURL db field references

## [5.0.0]

Complete rewrite of the module. 

The following SiteConfig field names have changed, please make sure you have a copy of all values before upgrading:

* MetaFacebookPage
* MetaFacebookAppId
* MetaFacebookAdmins
* MetaTwitterHandle
* MicroDataEnableCoordinates
* MicroDataAdditionalLocations flag
* MicroDataAdditionalLocationsSeparateEntities
* MicroDataLogo
* OpeningHours (please make a backup of all opening hours)
* AdditionalLocations (please make a backup of all additional locations)

## [4.0.2]

* add owns API for linked image
* fix sub organisation dropdowns
* fix visibility of location fields
* remove obsolete code

## [4.0.1]

* fix DBHTMLText::NoHTML() call, replace with DBHTMLText::Plain()

## [4.0.0]

* upgraded for SilverStripe 4 compatibility
* replaced extraattributesfield module with dependentdropdownfield
* replaced local cms javascript with displaylogic functionality

## [3.0.2]

* fix schema data for non-multisites sites

## [3.0.1]

* fix config to include cms js

## [3.0.0]

* refactor module
* use MetaTags() instead of template for rendering
* show global schema data only on home page

## [2.1.0]

* add SocialMetaImage to Schema data

## [2.0.1]

* fix js in CMS to hide locations if disabled

## [2.0.0]

* add possibility to add multiple location records for organisations and local businesses
* add hasMap property for entered addresses
* add possibility to add multiple opening hours records
* add pre-configured schema types for sub types of organisation, local business and event
* clean up code and CMS

## [1.4.0]

* add 'getKeyvisualImage' method call to social media image collector

## [1.3.2]

* fix issue with meta titles and descriptions

## [1.3.1]

* fix json+ld if no logo added

## [1.3.0]

* add logo to site meta data, replace image with logo in json+ld data

## [1.2.1]

* fix author meta data for blog posts

## [1.2.0]

* return array of ld+json data in order to give sub pages the ability to add more ld+json blocks

## [1.1.1]

* fix absulte url for image in ld+json data

## [1.1.0]

* move ld+json data creation from template to function

## [1.0.3]

* update dependencies from SSAU to symbiote

## [1.0.2]

* fix to match changes in social-share module

## [1.0.1]

* remove obsolete cleanup task
* update translations 

## [1.0.0]

* initial release
