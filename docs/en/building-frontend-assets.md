# Building frontend assets

This guide is intended for instructions on dealing with frontend asset files while contributing to this module. You
could also extend the Javascript and/or SCSS files using a combination of your own Webpack configurations and
`Requirements::block` calls (to block the default DMS assets), but this is not the primary intent of this document.

## Javascript

Javascript files use jQuery entwine, and live in the `javascript` folder. You can edit these files directly.

## SASS/CSS

CSS is build using Webpack and the sass-loader plugin. To install the required dependencies, you will need NodeJS and
npm installed on your local machine. You can then install by running `npm install` from the `dms` module folder.

To make changes to CSS you need to first make the change in the relevant SCSS file in the `scss` folder.

You can then compile the SCSS into CSS files:

```
npm run build
# or, to watch:
npm run watch
```

This will compile the SCSS files and produce a single compiled file under `dist/css/cmsbundle.css`. This file is named
this way to distinguish the fact that its contents are all related to the CMS rather than the frontend of a SilverStripe
website.
