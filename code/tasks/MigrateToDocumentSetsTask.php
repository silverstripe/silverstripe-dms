<?php
/**
 * This build task helps to migrate DMS data structures from DMS 1.x to 2.x which introduces document sets.
 *
 * See the "document-sets.md" migration guide for more information and use examples.
 */
class MigrateToDocumentSetsTask extends BuildTask
{
    protected $title = 'DMS 2.0 Migration Tool';

    protected $description = 'Migration tool for upgrading from DMS 1.x to 2.x. Add "action=create-default-document-set" to create a default set. "reassign-documents" to reassign legacy document relations. "dryrun=1" to show changes without writing.';

    /**
     * The valid actions that this task can perform (and the method that does them as the key)
     * @var array
     */
    protected $validActions = array(
        'createDefaultSet' => 'create-default-document-set',
        'reassignDocuments' => 'reassign-documents'
    );

    /**
     * @var SS_HTTPRequest
     */
    protected $request;

    /**
     * Holds number of pages/sets/documents processed for output at the end. Example:
     *
     * <code>
     * array(
     *     'total-pages' => 0,
     *     'pages-updated' => 0
     * )
     * </code>
     *
     * The individual action methods will update these metrics as required
     *
     * @var array
     */
    protected $results = array();

    public function run($request)
    {
        $this->request = $request;

        $action = $request->getVar('action');
        if (!in_array($action, $this->validActions)) {
            $this->output(
                'Error! Specified action is not valid. Valid actions are: ' . implode(', ', $this->validActions)
            );
            $this->output('You can add "dryrun=1" to enable dryrun mode where no changes will be written to the DB.');
            return;
        }

        $this->outputHeader();
        $action = array_search($action, $this->validActions);
        $this->$action();
        $this->outputResults();
    }

    /**
     * Returns whether dryrun mode is enabled ("dryrun=1")
     *
     * @return bool
     */
    public function isDryrun()
    {
        return (bool) $this->request->getVar('dryrun') == 1;
    }

    /**
     * Creates a default document set for any valid page that doesn't have one
     *
     * @return $this
     */
    protected function createDefaultSet()
    {
        $pages = SiteTree::get();
        foreach ($pages as $page) {
            // Only handle valid page types
            if (!$page->config()->get('documents_enabled')) {
                $this->addResult('Skipped: documents disabled');
                continue;
            }

            if ($page->DocumentSets()->count()) {
                // Don't add a set if it already has one
                $this->addResult('Skipped: already has a set');
                continue;
            }
            $this->addDefaultDocumentSet($page);
            $this->addResult('Default document set added');
        }
        return $this;
    }

    /**
     * Reassign documents to the default document set, where they'd previously have been assigned to pages
     *
     * @return $this
     */
    protected function reassignDocuments()
    {
        $countCheck = SQLSelect::create('*', 'DMSDocument_Pages');
        if (!$countCheck->count()) {
            $this->output('There was no data to migrate. Finishing.');
            return $this;
        }

        $query = SQLSelect::create(array('DMSDocumentID', 'SiteTreeID'), 'DMSDocument_Pages');
        $result = $query->execute();

        foreach ($result as $row) {
            $document = DMSDocument::get()->byId($row['DMSDocumentID']);
            if (!$document) {
                $this->addResult('Skipped: document does not exist');
                continue;
            }

            $page = SiteTree::get()->byId($row['SiteTreeID']);
            if (!$page) {
                $this->addResult('Skipped: page does not exist');
                continue;
            }

            // Don't try and process pages that don't have a document set. This should be created by the first
            // action step in this build task, so shouldn't occur if run in correct order.
            if (!$page->DocumentSets()->count()) {
                $this->addResult('Skipped: no default document set');
                continue;
            }
            $this->addDocumentToSet($document, $page->DocumentSets()->first());
            $this->addResult('Reassigned to document set');
        }

        return $this;
    }

    /**
     * Create a "default" document set and add it to the given Page via the ORM relationship added by
     * {@link DMSSiteTreeExtension}
     *
     * @param  SiteTree $page
     * @return $this
     */
    protected function addDefaultDocumentSet(SiteTree $page)
    {
        if ($this->isDryrun()) {
            return $this;
        }

        $set = DMSDocumentSet::create();
        $set->Title = 'Default';
        $set->write();

        $page->DocumentSets()->add($set);

        return $this;
    }

    /**
     * Add the given document to the given document set
     *
     * @param  DMSDocument $document
     * @param  DMSDocumentSet $set
     * @return $this
     */
    protected function addDocumentToSet(DMSDocument $document, DMSDocumentSet $set)
    {
        if ($this->isDryrun()) {
            return $this;
        }

        $set->Documents()->add($document);
        return $this;
    }

    /**
     * Output a header info line
     *
     * @return $this
     */
    protected function outputHeader()
    {
        $this->output('Migrating DMS data to 2.x for document sets');
        if ($this->isDryrun()) {
            $this->output('NOTE: Dryrun mode enabled. No changes will be written.');
        }
        return $this;
    }

    /**
     * Output a "finished" notice and the results of what was done
     *
     * @return $this
     */
    protected function outputResults()
    {
        $this->output();
        $this->output('Finished:');
        foreach ($this->results as $metric => $count) {
            $this->output('+ ' . $metric . ': ' . $count);
        }
        return $this;
    }

    /**
     * Add the $increment to the result key identified by $key
     *
     * @param  string $key
     * @param  int    $increment
     * @return $this
     */
    protected function addResult($key, $increment = 1)
    {
        if (!array_key_exists($key, $this->results)) {
            $this->results[$key] = 0;
        }
        $this->results[$key] += $increment;
        return $this;
    }

    /**
     * Outputs a message formatted either for CLI or browser output
     *
     * @param  string $message
     * @return $this
     */
    public function output($message = '')
    {
        if ($this->isCli()) {
            echo $message, PHP_EOL;
        } else {
            echo $message . '<br />';
        }
        return $this;
    }

    /**
     * Returns whether the task is called via CLI or not
     *
     * @return bool
     */
    protected function isCli()
    {
        return Director::is_cli();
    }
}
