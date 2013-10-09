<?php
/**
 * Handles replacing `dms_document_link` shortcodes with links to the actual
 * document.
 */
class DMSShortcodeHandler {

	public static function handle($arguments, $content = null, $parser = null) {
		$linkText = null;

		if (!empty($arguments['id'])) {
			$document = DMSDocument::get()->filter(array('ID' => $arguments['id']))->First();
			if ($document && !$document->isHidden()) {
				if (!empty($content)) {
					$linkText = sprintf('<a href="%s">%s</a>', $document->Link(), $parser->parse($content));
				} else {
					$linkText = $document->Link();
				}
			}
		}

		if (empty($linkText)) {
			$errorPage = ErrorPage::get()->filter(array('ErrorCode' => '404'))->First();
			if ($errorPage) {
				$linkText = $errorPage->Link();
			}
		}

		return $linkText;
	}

}
