<?php
/**
 * Contains EveApiRetriever class.
 *
 * PHP version 5.3
 *
 * LICENSE:
 * This file is part of 1.1.x-WIP
 * Copyright (C) 2014 Michael Cummings
 *
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General
 * Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option)
 * any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Lesser General Public License along with this program. If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * You should be able to find a copy of this license in the LICENSE.md file. A copy of the GNU GPL should also be
 * available in the GNU-GPL.md file.
 *
 * @copyright 2014 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Console\Command;

use Guzzle\Http\Client;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Yapeal\Xml\EveApiPreserverInterface;
use Yapeal\Xml\EveApiRetrieverInterface;
use Yapeal\Xml\EveApiXmlData;
use Yapeal\Xml\EveApiXmlModifyInterface;
use Yapeal\Xml\FileCachePreserver;
use Yapeal\Xml\GuzzleNetworkRetriever;

/**
 * Class EveApiRetriever
 */
class EveApiRetriever extends Command implements LoggerAwareInterface
{
    /**
     * @param string|null $name
     * @param string      $cwd
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function __construct($name = null, $cwd)
    {
        $this->setDescription(
            'Retrieves Eve Api XML from servers and puts it in file'
        );
        $this->setName($name);
        $this->setCwd($cwd);
        parent::__construct($name);
    }
    /**
     * @param string $value
     *
     * @throws \InvalidArgumentException
     * @return self
     */
    public function setCwd($value)
    {
        if (!is_string($value)) {
            $mess = 'Cwd MUST be string but given ' . gettype($value);
            throw new \InvalidArgumentException($mess);
        }
        $this->cwd = $value;
        return $this;
    }
    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface $logger
     *
     * @return null
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    /**
     * @type string
     */
    protected $cwd;
    /**
     * @type LoggerInterface
     */
    protected $logger;
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->addArgument(
            'section_name',
            InputArgument::REQUIRED,
            'Name of Eve Api section to retrieve.'
        );
        $this->addArgument(
            'api_name',
            InputArgument::REQUIRED,
            'Name of Eve Api to retrieve.'
        );
        $this->addArgument(
            'post',
            InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
            'Optional list of additional POST parameter(s) to send to server.',
            array()
        );
//        $this->addOption(
//            'keyID',
//            null,
//            InputOption::VALUE_REQUIRED,
//            'The ID of the Customizable API Key for authentication.'
//        );
//        $this->addOption(
//            'ownerID',
//            null,
//            InputOption::VALUE_REQUIRED,
//            'The ID of character/corporation requesting the data for APIs that require it.'
//        );
//        $this->addOption(
//            'vCode',
//            null,
//            InputOption::VALUE_REQUIRED,
//            'The user defined or CCP generated Verification Code for the Customizable API Key.'
//        );
        $this->addOption(
            'directory',
            'd',
            InputOption::VALUE_REQUIRED,
            'Directory that XML will be sent to.'
        );
        $help = <<<EOF
The <info>%command.full_name%</info> command retrieves the XML data from the Eve Api
server and stores it in a file. By default it will put the file in the current
working directory.

    <info>php %command.full_name% section_name api_name</info>

EXAMPLES:
Save current server status in current directory.
    <info>%command.name% server ServerStatus</info>

EOF;
        $this->setHelp($help);
    }
    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return null|integer null or 0 if everything went fine, or an error code
     *
     * @throws \LogicException
     * @see    setCode()
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $posts = $input->getArgument('post');
        if (!empty($posts)) {
            $arguments = array();
            foreach ($posts as $post) {
                list($key, $value) = explode('=', $post);
                $arguments[$key] = $value;
            }
            $posts = $arguments;
        }
        $data = $this->getXmlData(
            $input->getArgument('api_name'),
            $input->getArgument('section_name'),
            $posts
        );
        $retriever = $this->getNetworkRetriever();
        $retriever->retrieveEveApi($data);
        $preserver = $this->getCachingPreserver();
        $preserver->preserveEveApi($data);
        $output->writeln('I ran!!!');
    }
    /**
     * @return EveApiPreserverInterface
     */
    protected function getCachingPreserver()
    {
        return new FileCachePreserver(
            $this->getLogger(),
            $this->getCwd() . DIRECTORY_SEPARATOR . 'cache'
        );
    }
    /**
     * @return Client
     */
    protected function getClient()
    {
        $headers = array(
            'Accept' => 'text/xml,application/xml,application/xhtml+xml;'
                . 'q=0.9,text/html;q=0.8,text/plain;q=0.7,image/png;q=0.6,*/*;'
                . 'q=0.5',
            'Accept-Charset' => 'utf-8;q=0.9,windows-1251;q=0.7,*;q=0.6',
            'Accept-Encoding' => 'gzip',
            'Accept-Language' => 'en-us;q=0.9,en;q=0.8,*;q=0.7',
            'Connection' => 'Keep-Alive',
            'Keep-Alive' => '300'
        );
        $defaults = array(
            'headers' => $headers,
            'timeout' => 10,
            'connect_timeout' => 30,
            'verify' =>
                dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'config'
                . DIRECTORY_SEPARATOR . 'eveonline.crt',
        );
        return new Client(
            'https://api.eveonline.com',
            array('defaults' => $defaults)
        );
    }
    /**
     * @return string
     */
    protected function getCwd()
    {
        return $this->cwd;
    }
    /**
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        if (empty($this->logger)) {
            $handlerStreamCli = new StreamHandler('php://stderr');
            $this->logger = new Logger('console', array($handlerStreamCli));
        }
        return $this->logger;
    }
    /**
     * @return EveApiRetrieverInterface
     */
    protected function getNetworkRetriever()
    {
        return new GuzzleNetworkRetriever(
            $this->getLogger(), $this->getClient()
        );
    }
    /**
     * @param string   $apiName
     * @param string   $sectionName
     * @param string[] $posts
     *
     * @return EveApiXmlModifyInterface
     */
    protected function getXmlData($apiName, $sectionName, $posts)
    {
        return new EveApiXmlData($apiName, $sectionName, $posts);
    }
}
