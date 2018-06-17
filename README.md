# Document Management Module (DMS)

[![Build status](https://travis-ci.org/silverstripe/silverstripe-dms.png?branch=master)](https://travis-ci.org/silverstripe/silverstripe-dms)
[![SilverStripe supported module](https://img.shields.io/badge/silverstripe-supported-0071C4.svg)](https://www.silverstripe.org/software/addons/silverstripe-commercially-supported-module-list/)
[![Code quality](https://scrutinizer-ci.com/g/silverstripe/silverstripe-dms/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/silverstripe/silverstripe-dms/?branch=master)
[![Code coverage](https://codecov.io/gh/silverstripe/silverstripe-dms/branch/master/graph/badge.svg)](https://codecov.io/gh/silverstripe/silverstripe-dms)
![Helpful Robot](https://img.shields.io/badge/helpfulrobot-52-yellow.svg?style=flat)

## Overview

The module adds a new `DMSDocument` model which allows management of large amounts of files, and their relations to
pages. In contrast to the `File` model built into SilverStripe core, it aims to wrap storage and access concerns in
a generic API. This allows more fine-grained control over how the documents are managed and exposed through the website.

Additionally, documents are stored and managed as part of a page instead of away in a separate assets store.

## Features

 * Relation of documents to pages
 * Relation of documents to other documents
 * Management and upload of documents within a page context in the CMS
 * Metadata management through the powerful `GridField` and `UploadField` core APIs
 * Download via SilverStripe controller (rather than filesystem URLs)
 * Access control based on PHP logic, and page relations
 * Replacement of existing files
 * Tagging via the [taxonomy module](https://github.com/silverstripe/silverstripe-taxonomy) if installed

## Documentation

For information on configuring and using this module, please see [the documentation section](docs/en/index.md).

## Requirements

 * PHP 5.3 with the "fileinfo" module (or alternatively the "whereis" and "file" Unix commands)
 * SilverStripe framework/CMS ^3.5
 * [Taxonomy](https://github.com/silverstripe/silverstripe-taxonomy) ^1.2 (for tagging)
 * (optional) [Pagination of Documents in the CMS](https://github.com/silverstripe-big-o/gridfieldpaginatorwithshowall)
 * (optional) [Sorting of Documents in the CMS](https://github.com/silverstripe-big-o/SortableGridField)
 * (optional) [Full text search of Documents](https://github.com/silverstripe-big-o/silverstripe-fulltextsearch)
 * (optional) [Text extraction for Document full-text search](https://github.com/silverstripe-big-o/silverstripe-textextraction)

## Contributing

### Translations

Translations of the natural language strings are managed through a
third party translation interface, transifex.com.
Newly added strings will be periodically uploaded there for translation,
and any new translations will be merged back to the project source code.

Please use [https://www.transifex.com/projects/p/silverstripe-dms/](https://www.transifex.com/projects/p/silverstripe-dms/) to contribute translations,
rather than sending pull requests with YAML files.

See the ["i18n" topic](http://doc.silverstripe.org/framework/en/trunk/topics/i18n) on doc.silverstripe.org for more details.
