<?php

/**
 * Handles replacing `dms_document_link` shortcodes with links to the actual
 * document.
 *
 * @package dms
 */
class DMSShortcodeHandler
{
    public static function handle($arguments, $content, ShortcodeParser $parser, $tag, array $extra = array())
    {
        if (!empty($arguments['id'])) {
            $document = DMSDocument::get()->byID($arguments['id']);

            if ($document && !$document->isHidden()) {
                if ($content) {
                    return sprintf(
                        '<a href="%s">%s</a>',
                        $document->Link(),
                        $parser->parse($content)
                    );
                }

                if (isset($extra['element'])) {
                    $extra['element']->setAttribute('data-ext', $document->getExtension());
                    $extra['element']->setAttribute('data-size', $document->getFileSizeFormatted());
                }

                return $document->Link();
            }
        }

        $error = ErrorPage::get()->filter('ErrorCode', '404')->First();

        if ($error) {
            return $error->Link();
        }

        return '';
    }
}
