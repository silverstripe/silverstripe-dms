# Migrating to use Document Sets

> **Warning!** Please ensure you take a backup of your database before performing any of these migration task steps.

Version 2.0.0 of the DMS module introduces document sets as the containing relationship for pages and documents. In
previous versions of DMS the relationship was between pages and documents directly.

If you are migrating from an earlier version of DMS to 2.x, you will need to set up new document sets for each page
that contained documents and establish the links from the old document-page to the new document set-document, and
document set-page.

We have included a migration build task that you can use to automate this process. It can be access via
`/dev/tasks/MigrateToDocumentSetsTask`, and will prompt you for the following steps in the migration process:

* Create a default document set for all valid pages (see note)
* Re-assign documents to their original page's new document set

## Using the migration build task

### Enabling dry run mode

For either of the "actions" in this build task, you can enable dry run mode to see what the results will be without
it actually writing anything in the database. We advise you do this as a first step.

You can enable dryrun mode by adding `dryrun=1` as an argument.

Example output will contain the following when dryrun mode is enabled:

```plain
NOTE: Dryrun mode enabled. No changes will be written.
```

### 1. Create a default document set

The first step of the migration build task will find all pages that do not have documents disabled (see note) and will
create a document set called "Default" if one does not already exist. In the case where a document set already exists
for a page, it will be used as the default.

Run from command line:

```plain
sake dev/tasks/MigrateToDocumentSetsTask action=create-default-document-set
```

Run from a browser:

```plain
http://yoursite.dev/dev/tasks/MigrateToDocumentSetsTask?action=create-default-document-set
```

An example output from this task might look like this:

```plain
Running Task DMS 2.0 Migration Tool

Migrating DMS data to 2.x for document sets

Finished:
+ Default document set added: 6
+ Skipped: documents disabled: 1
```

This task will only write records for those that are needed. If you run it more than once it will simply not do
anything.

### 2. Re-assign documents

> **Note!** If you want to choose specific document sets for documents to be assigned to rather than just the first
belonging to a page, you will need to run these queries manually (see further in this document).

The second step in the migration task is to reassign the relationship from pages to documents to document set to
documents. This task assumes that the original relationship data is still present in the database, since SilverStripe
will not remove old columns from the database tables once they've been made obsolete.

Run from command line:

```plain
sake dev/tasks/MigrateToDocumentSetsTask action=reassign-documents
```

Run from a browser:

```plain
http://yoursite.dev/dev/tasks/MigrateToDocumentSetsTask?action=reassign-documents
```

An example output from this task might look like this:

```plain
Running Task DMS 2.0 Migration Tool

Migrating DMS data to 2.x for document sets

Finished:
+ Reassigned to document set: 4
```

This task will show the same output on the initial and subsequent runs. You can follow the instructions below to clean
up legacy data after you've validated that everything is working correctly if you'd like to.

## Cleanup

Since SilverStripe will not remove the old obselete relationship table from the database, you can remove it manually
if required. Only do this once you've validated that everything has been migrated correctly.

```sql
DROP TABLE `your_ss_database`.`DMSDocument_Pages`;
```

## Migrating data manually

As mentioned earlier, if you need to migrate data manually for one reason or another you can do so with a couple of
manual SQL queries to the database.

One example of why you may need to do this is if you don't want your documents to
be automatically assigned to the "default" document set on a page, but would prefer to choose a specific set to assign
to. The automated build task cannot make this decision for us, but you can run some queries yourself.

In DMS 1.x the relationship of documents to pages is stored in the `DMSDocument_Pages` table. If you run an explain
query you will see some obviously named foreign key columns for `DMSDocumentID` and `SiteTreeID`.

In DMS 2.x the relationship is of document _sets_ to documents, and is stored in `DMSDocumentSet_Documents`.

How you manipulate this data is up to you, but an example might be that you want to move a certain range of documents
by their IDs into a specific document set (by its ID), so you could run the following:

```sql
-- Insert the new records
INSERT INTO `your_ss_database`.`DMSDocumentSet_Documents`
    (`DMSDocumentSetID`, `DMSDocumentID`)
SELECT
    -- your document set ID
    123,
    `ID`
FROM `your_ss_database`.`DMSDocument` WHERE `ID` IN(1, 2, 3, 4); -- your document IDs
```

## Notes

> Create a default document set for all valid pages

"Valid pages" means that the page class does not have the `documents_enabled` configuration property set to `false`.
