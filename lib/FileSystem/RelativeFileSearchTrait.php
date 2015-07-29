<?php
/**
 * Contains RelativeFileSearchTrait Trait.
 *
 * PHP version 5.4
 *
 * @copyright 2015 Michael Cummings
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\FileSystem;

use LogicException;
use Yapeal\Event\EventMediatorInterface;
use Yapeal\Log\Logger;

/**
 * Trait RelativeFileSearchTrait
 *
 * @method EventMediatorInterface getYem()
 */
trait RelativeFileSearchTrait
{
    /**
     * Getter for $relativeBaseDir.
     *
     * Note that if it is NOT set beforehand it will default to the directory of the using class.
     *
     * @return string
     * @throws LogicException
     */
    protected function getRelativeBaseDir()
    {
        if (null === $this->relativeBaseDir) {
            $mess = 'Tried to use relativeBaseDir before it was set';
            throw new LogicException($mess);
        }
        return $this->relativeBaseDir;
    }
    /**
     * Fluent interface setter for $relativeBaseDir.
     *
     * @param string $value
     *
     * @return self Fluent interface.
     */
    public function setRelativeBaseDir($value)
    {
        $this->relativeBaseDir = str_replace('\\', '/', (string)$value);
        return $this;
    }
    /**
     * Used to find a file relative to the base path using section and api names for path and/or file name.
     *
     * @param string $sectionName
     * @param string $apiName
     * @param string $suffix
     *
     * @return string
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    protected function findEveApiFile($sectionName, $apiName, $suffix)
    {
        $fileNames = sprintf(
            '%3$s/%1$s/%2$s.%4$s,%3$s/%2$s.%4$s,%3$s/%1$s/%1$s.%4$s,%3$s/common.%4$s',
            $sectionName,
            $apiName,
            $this->getRelativeBaseDir(),
            $suffix
        );
        foreach (explode(',', $fileNames) as $fileName) {
            if (is_readable($fileName) && is_file($fileName)) {
                $mess = sprintf(
                    'Using %4$s file %3$s for %1$s/%2$s',
                    ucfirst($sectionName),
                    $apiName,
                    $fileName,
                    strtoupper($suffix)
                );
                $this->getYem()
                     ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $mess);
                return $fileName;
            }
        }
        $mess = sprintf(
            'Failed to find accessible %3$s file for %1$s/%2$s, check file permissions',
            $sectionName,
            $apiName,
            strtoupper($suffix)
        );
        $this->getYem()
             ->triggerLogEvent('Yapeal.Log.log', Logger::WARNING, $mess);
        return '';
    }
    /**
     * Holds the path that is prepended for searches.
     *
     * @type string $relativeBaseDir
     */
    protected $relativeBaseDir;
}
