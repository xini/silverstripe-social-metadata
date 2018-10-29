# Changelog

All notable changes to this project will be documented in this file.

This project adheres to [Semantic Versioning](http://semver.org/).

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
