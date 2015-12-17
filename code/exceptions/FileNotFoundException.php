<?php
/**
 * Simple exception extension so that we can tell the difference between internally
 * raised exceptions and those thrown by DMS.
 */
class FileNotFoundException extends Exception
{
}
