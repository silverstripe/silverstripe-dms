# Configuration

The file location is set via the `DMS::$dmsFolder` static, and points to a location in the webroot.

## Enable/disable documents/sets for a specific page type

If you don't need documents/document sets for a specific page type you can disable this with YAML configuration:

```yaml
MyPageType:
  documents_enabled: false
```

Likewise, you could override a previously set configuration value by setting this back to `true` in a configuration
file with a higher precedence.

## Allowed extensions for DMS documents

By default the allowed extensions for DMS documents will come from the UploadField's allowed extesions list, and will
have a customised list of extensions for DMS merged in. The base `allowed_extensions` is a site-wide configuration
setting. [See here for information](https://docs.silverstripe.org/en/3/developer_guides/forms/field_types/uploadfield/#limit-the-allowed-filetypes) on changing this.

To add extra allowed file extensions purely for DMS documents, you can update the YAML configuration property:

```yaml
DMSDocumentAddController:
  allowed_extensions:
    - php
    - php5
```
