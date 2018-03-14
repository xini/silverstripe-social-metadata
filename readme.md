# SilverStripe Social Metadata

[![Version](http://img.shields.io/packagist/v/innoweb/silverstripe-social-metadata.svg?style=flat-square)](https://packagist.org/packages/innoweb/silverstripe-social-metadata)
[![License](http://img.shields.io/packagist/l/innoweb/silverstripe-social-metadata.svg?style=flat-square)](license.md)

## Overview

Adds social metadata like OpenGraph, Twitter and JSON-LD to all pages.

## Requirements

* SilverStripe CMS ~3.2

## Installation

Install the module using composer:
```
composer require innoweb/silverstripe-social-metadata dev-master
```
or download or git clone the module into a ‘social-metadata’ directory in your webroot.

Then run dev/build.

## Configuration

The module adds a new tab to the SiteConfig in the CMS where all the metadata can be configured. 

### MultiSites support

The module supports the [multisites module] (https://github.com/silverstripe-australia/silverstripe-multisites) and by default adds the config options to the Sites.

If you want to manage the metadata globally, please add the following settings in your `config.yml`:

```
SocialMetaConfigExtension:
  multisites_enable_global_settings: true
``` 

This will add the fields to your SiteConfig instead of Site. 

## License

BSD 3-Clause License, see [License](license.md)
