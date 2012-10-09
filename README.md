# Document Management Module (DMS)

## Overview

The module adds a new `DMSDocument` model which allows management
of large amounts of files, and their relations to pages.
In contrast to the `File` model built into SilverStripe core,
it aims to wrap storage and access concerns in a generic API,
which allows more fine-grained control over how the documents are
managed and exposed through the website.

Additionally, documents are stored and managed as part of a page instead of
away in a separate assets store.

Features:

 * Relation of documents to pages
 * Management and upload of documents within a page context in the CMS
 * Metadata management through the powerful `GridField` and `UploadField` core APIs
 * Configurable tags for documents
 * Download via SilverStripe controller (rather than filesystem URLs)
 * Access control based on PHP logic, and page relations
 * Replacement of existing files

## Documents on the Filesystem

While the DMS architecture allows for remote storage of files,
the default implementation (the `DMS` class) stores them locally.
Relations to pages and tags are persisted as many-many relationships
through the SilverStripe ORM.

File locations in this implementation are structured into 
subfolders, in order to avoid exceeding filesystem limits.
The file name is a composite based on its database ID
and the original file name. The exact location shouldn't
be relied on by custom logic, but rather retrieved through
the API (`DMSDocument->getLink()`).

Example:

	dms-assets/
		0/
			1234~myfile.pdf
		1/
			2345~myotherfile.pdf


### Requirements

 * PHP 5.3 with the "fileinfo" module (or alternatively the "whereis" and "file" Unix commands)
 * (optional) [https://github.com/silverstripe-big-o/gridfieldpaginatorwithshowall](Pagination of Documents in the CMS)
 * (optional) [https://github.com/silverstripe-big-o/SortableGridField](Sorting of Documents in the CMS)
 * (optional) [https://github.com/silverstripe-big-o/SortableGridField](Sorting of Documents in the CMS)
 * (optional) [https://github.com/silverstripe-big-o/silverstripe-fulltextsearch](Full text search of Documents)
 * (optional) [https://github.com/silverstripe-big-o/silverstripe-textextraction](Text extraction for Document full-text search)

### Configuration

The file location is set via the `DMS::$dmsFolder` static, and points to a location in the webroot.

### Usage

Add a simple include to any of your .ss templates to display the DMSDocuments associated with
the current page on the front-end.

	<% include Documents %>

#### Create Documents

Create by relative path:

	$dms = DMS::getDMSInstance();
	$doc = $dms->storeDocument('assets/myfile.pdf');

Create from an existing `File` record:

	$dms = DMS::getDMSInstance();
	$file = File::get()->byID(99);
	$doc = $dms->storeDocument($file);

Note: Both operations copy the existing file.

#### Download Documents

	$dms = DMS::getDMSInstance();
	$docs = $dms->getByTag('priority', 'important')->First();
	$link = $doc->getLink();

#### Manage Page Relations

	// Find documents by page
	$dms = DMS::getDMSInstance();
	$page = SiteTree::get()->filter('URLSegment', 'home')->First();
	$docs = $dms->getByPage($page);

	// Add documents to page

#### Manage Tags

	// Find documents by tag
	$dms = DMS::getDMSInstance();
	$docs = $dms->getByTag('priority', 'important');

	// Add tag to existing document
	$doc = Document::get()->byID(99);
	$doc->addTag('priority', 'low');

	// Supports multiple values for tags
	$doc->addTag('category', 'keyboard');
	$doc->addTag('category', 'input device');

	// Removing tags is abstracted as well
	$doc->removeTag('category', 'keyboard'); 
	$doc->removeTag('category', 'input device'); 
	$doc->removeAllTags();