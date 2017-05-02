# Documents on the Filesystem

While the DMS architecture allows for remote storage of files, the default implementation (the `DMS` class)
stores them locally. Relations to pages and tags are persisted as many-many relationships through the SilverStripe ORM.

File locations in this implementation are structured into subfolders, in order to avoid exceeding filesystem limits.
The file name is a composite based on its database ID and the original file name. The exact location shouldn't be
relied on by custom logic, but rather retrieved through the API method `DMSDocument::getLink`.

Example:

```
dms-assets/
    0/
        1234~myfile.pdf
    1/
        2345~myotherfile.pdf
```
