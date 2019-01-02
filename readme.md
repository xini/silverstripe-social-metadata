# SilverStripe Social Metadata

[![Version](http://img.shields.io/packagist/v/innoweb/silverstripe-social-metadata.svg?style=flat-square)](https://packagist.org/packages/innoweb/silverstripe-social-metadata)
[![License](http://img.shields.io/packagist/l/innoweb/silverstripe-social-metadata.svg?style=flat-square)](license.md)

## Overview

Adds social metadata like OpenGraph, Twitter and JSON-LD to all pages.

The module adds the following functionality to a site:

* adds meta title fields for all pages to override the default 'page title - site title' title structure
* adds canonical url to the meta data of each page which can be overridden for each page, for example for blog posts re-published from external sources
* adds type, locations, opening hours, address, location, logo and image for schema.org JSON-LD data of the site
* supports the [multisites module](https://github.com/silverstripe-australia/silverstripe-multisites) and adds the config options to the Sites instead of SiteConfig
* supports the [blog module](https://github.com/silverstripe/silverstripe-blog) and handles the canonical url as well as custom meta titles and descriptions for tags and categories. It also adds JSON-LD for blog posts
* pages can extend the functionality and add their own data to the JSON-LD etc

## Requirements

* SilverStripe CMS 4.x

Note: this version is compatible with SilverStripe 4. For SilverStripe 3, please see the [3.0 release line](https://github.com/xini/silverstripe-social-metadata/tree/3.0).

## Installation

Install the module using composer:
```
composer require innoweb/silverstripe-social-metadata dev-master
```

Then run dev/build.

## Configuration

The module adds a new tab to the SiteConfig in the CMS where all the metadata can be configured. 

## License

BSD 3-Clause License, see [License](license.md)
