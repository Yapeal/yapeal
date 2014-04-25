<?php
/**
 * Created by PhpStorm.
 * User: Dragon
 * Date: 4/23/2014
 * Time: 7:56 AM
 */
namespace Yapeal\Network;

/**
 * Wrapper for API network connection.
 */
interface NetworkInterface
{
    /**
     * Will retrieve the XML from API server.
     *
     * @param string $api      API needed.
     * @param string $section  Section API belongs to.
     * @param array  $postList A list of data that will be passed to the API
     *                         server. Example:
     *                         array(UserID => '123', apiKey => 'abc123', ...)
     *
     * @return string|false Returns XML data from API or FALSE for any
     * connection error.
     */
    public function retrieveEveApiXml($api, $section, $postList);
}
