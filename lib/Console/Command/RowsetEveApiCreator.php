<?php
/**
 * Contains RowsetEveApiCreator class.
 *
 * PHP version 5.4
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2015 Michael Cummings
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Lesser General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License
 * for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * You should be able to find a copy of this license in the LICENSE.md file. A
 * copy of the GNU GPL should also be available in the GNU-GPL.md file.
 *
 * @copyright 2015 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Console\Command;

use FilePathNormalizer\FilePathNormalizerTrait;
use SimpleXMLElement;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yapeal\Configuration\ConsoleWiring;
use Yapeal\Configuration\WiringInterface;
use Yapeal\Console\CommandToolsTrait;
use Yapeal\Container\ContainerInterface;
use Yapeal\Exception\YapealDatabaseException;
use Yapeal\Xml\EveApiReadWriteInterface;
use Yapeal\Xml\EveApiXmlData;

/**
 * Class RowsetEveApiCreator
 */
class RowsetEveApiCreator extends Command implements WiringInterface
{
    use CommandToolsTrait, FilePathNormalizerTrait;
    /**
     * @param string|null        $name
     * @param string             $cwd
     * @param ContainerInterface $dic
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function __construct($name, $cwd, ContainerInterface $dic)
    {
        $this->setDescription(
            'Retrieves Eve Api XML from servers and creates class, xsd, sql files for simple rowsets'
        );
        $this->setName($name);
        $this->setCwd($cwd);
        $this->setDic($dic);
        parent::__construct($name);
    }
    /**
     * @param ContainerInterface $dic
     *
     * @throws YapealDatabaseException
     */
    public function wire(ContainerInterface $dic)
    {
        if (empty($dic['Yapeal.cwd'])) {
            $dic['Yapeal.cwd'] = $this->getFpn()
                                      ->normalizePath($this->getCwd());
        }
        $path = $this->getFpn()
                     ->normalizePath(dirname(dirname(dirname(__DIR__))));
        if (empty($dic['Yapeal.baseDir'])) {
            $dic['Yapeal.baseDir'] = $path;
        }
        if (empty($dic['Yapeal.vendorParentDir'])) {
            $vendorPos = strpos($path, 'vendor/');
            if (false !== $vendorPos) {
                $dic['Yapeal.vendorParentDir'] = substr($path, 0, $vendorPos);
            }
        }
        $wiring = new ConsoleWiring($dic);
        $wiring->wireDefaults()
               ->wireConfiguration();
        $dic['Yapeal.Config.Parser'];
        $wiring->wireErrorLogger();
        $dic['Yapeal.Error.Logger'];
        $wiring->wireLogLogger()
               ->wirePreserver()
               ->wireRetriever();
    }
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
            'mask',
            InputArgument::REQUIRED,
            'Bit mask for Eve Api.'
        );
        $this->addArgument(
            'post',
            InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
            'Optional list of additional POST parameter(s) to send to server.',
            []
        );
        $help = <<<EOF
The <info>%command.full_name%</info> command retrieves the XML data from the Eve Api
server and creates Yapeal Eve API Database class for simple rowset type APIs.

    <info>php %command.full_name% section_name api_name mask</info>

EXAMPLES:
Create Char/AccountBalance class in lib/Database/.
    <info>%command.name% char AccountBalance 1</info>

EOF;
        $this->setHelp($help);
    }
    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return null|int null or 0 if everything went fine, or an error code
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Yapeal\Exception\YapealConsoleException
     * @throws \Yapeal\Exception\YapealDatabaseException
     * @see    setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $posts = $this->processPost($input);
        $dic = $this->getDic();
        $this->wire($dic);
        $apiName = $input->getArgument('api_name');
        $sectionName = $input->getArgument('section_name');
        $data = $this->getXmlData(
            $apiName,
            $sectionName,
            $posts
        );
        /**
         * @type \Yapeal\Xml\EveApiRetrieverInterface $retriever
         */
        $retriever = $dic['Yapeal.Xml.Retriever'];
        $retriever->retrieveEveApi($data);
        if (false === $data->getEveApiXml()) {
            $mess = sprintf(
                '<error>Could NOT retrieve Eve Api data for %1$s/%2$s</error>',
                strtolower($sectionName),
                $apiName
            );
            $output->writeln($mess);
            return 2;
        }
        /**
         * @type \Yapeal\Xml\EveApiPreserverInterface $preserver
         */
        $preserver = $dic['Yapeal.Xml.Preserver'];
        $preserver->preserveEveApi($data);
        $subs = $this->getSubs($data, $input);
        foreach (['php', 'sql', 'xsd'] as $for) {
            $template = $this->getTemplate($for, $output);
            $contents = $this->processTemplate($subs, $template);
            $fileName = sprintf(
                $this->getDic()['Yapeal.baseDir'] . '/lib/Database/%1$s/%2$s.%3$s',
                $sectionName,
                $apiName,
                $for
            );
            $this->saveToFile($fileName, $contents);
        }
        return 0;
    }
    /**
     * @param string[] $columnNames
     * @param string   $sectionName
     *
     * @return string
     */
    protected function getColumnDefaults($columnNames, $sectionName)
    {
        if (in_array(strtolower($sectionName), ['char', 'corp', 'account'], true)) {
            $columnNames[] = 'ownerID';
        }
        $columnNames = array_unique($columnNames);
        sort($columnNames);
        $columns = [];
        foreach ($columnNames as $name) {
            $column = '\'' . $name . '\' => null';
            if ('ownerID' === $name) {
                $column = '\'' . $name . '\' => $ownerID';
            }
            $columns[] = $column;
        }
        return implode(",\n", $columns);
    }
    /**
     * @param  array $columnNames
     * @param string $sectionName
     *
     * @return string
     */
    protected function getColumnList(array $columnNames, $sectionName)
    {
        if (in_array(strtolower($sectionName), ['char', 'corp', 'account'], true)) {
            $columnNames[] = 'ownerID';
        }
        $columnNames = array_unique($columnNames);
        sort($columnNames);
        $columns = [];
        foreach ($columnNames as $name) {
            $column = '"' . $name . '" VARCHAR(255) DEFAULT \'\'';
            if (false !== strpos(strtolower($name), 'name')) {
                $column = '"' . $name . '" CHAR(50) NOT NULL';
            }
            if ('ID' === substr($name, -2)) {
                $column = '"' . $name . '" BIGINT(20) UNSIGNED NOT NULL';
            }
            $columns[] = $column;
        }
        return implode(",\n", $columns);
    }
    /**
     * @param string $sectionName
     *
     * @return string
     */
    protected function getDeleteFromTable($sectionName)
    {
        if (in_array(strtolower($sectionName), ['char', 'corp', 'account'], true)) {
            return 'getDeleteFromTableWithOwnerID($tableName, $ownerID)';
        }
        return 'getDeleteFromTable($tableName)';
    }
    /**
     * @param string $sectionName
     *
     * @return string
     */
    protected function getNamespace($sectionName)
    {
        return 'Yapeal\Database\\' . ucfirst($sectionName);
    }
    /**
     * @param  string[] $columnNames
     *
     * @return string
     */
    protected function getRowAttributes(array $columnNames)
    {
        sort($columnNames);
        $columns = [];
        foreach ($columnNames as $name) {
            $column = '<xs:attribute type="xs:string" name="' . $name . '"/>';
            if (false !== strpos(strtolower($name), 'name')) {
                $column = '<xs:attribute type="eveNameType" name="' . $name . '"/>';
            }
            if ('ID' === substr($name, -2)) {
                $column = '<xs:attribute type="eveIDType" name="' . $name . '"/>';
            }
            $columns[] = $column;
        }
        return implode("\n", $columns);
    }
    /**
     * @param string[] $keyNames
     * @param string   $sectionName
     *
     * @return string
     */
    protected function getSqlKeys(array $keyNames, $sectionName)
    {
        if (in_array(strtolower($sectionName), ['char', 'corp', 'account'], true)) {
            array_unshift($keyNames, 'ownerID');
        }
        $keyNames = array_unique($keyNames);
        return '"' . implode('","', $keyNames) . '"';
    }
    /**
     * @param EveApiReadWriteInterface $data
     * @param InputInterface           $input
     *
     * @return array
     */
    protected function getSubs(EveApiReadWriteInterface $data, InputInterface $input)
    {
        list($columnNames, $keyNames, $rowsetName) = $this->processXml($data);
        $apiName = ucfirst($input->getArgument('api_name'));
        $sectionName = $input->getArgument('section_name');
        $subs = [
            'className'      => $apiName,
            'columnDefaults' => $this->getColumnDefaults($columnNames, $sectionName),
            'columnList'     => $this->getColumnList($columnNames, $sectionName),
            'copyright'      => gmdate('Y'),
            'getDelete'      => $this->getDeleteFromTable($sectionName),
            'keys'           => $this->getSqlKeys($keyNames, $sectionName),
            'mask'           => $input->getArgument('mask'),
            'namespace'      => $this->getNamespace($sectionName),
            'sectionName'    => ucfirst($sectionName),
            'tableName'      => lcfirst($sectionName) . $apiName,
            'rowAttributes'  => $this->getRowAttributes($columnNames),
            'rowsetName'     => $rowsetName,
            'updateName'     => gmdate('YmdHi')
        ];
        return $subs;
    }
    /**
     * @param string $apiName
     * @param string $sectionName
     *
     * @return string
     */
    protected function getTableName($apiName, $sectionName)
    {
        return lcfirst($sectionName) . $apiName;
    }
    /**
     * @param string          $for
     * @param OutputInterface $output
     *
     * @return false|string
     * @throws \InvalidArgumentException
     */
    protected function getTemplate($for, OutputInterface $output)
    {
        $templateName = sprintf('%1$s/rowset.%2$s.template', __DIR__, $for);
        $templateName = $this->fpn->normalizeFile($templateName);
        if (!is_file($templateName)) {
            $mess = '<error>Could NOT find template file ' . $templateName . '</error>';
            $output->writeln($mess);
            return false;
        }
        $template = file_get_contents($templateName);
        if (false === $template) {
            $mess = '<error>Could NOT open template file ' . $templateName . '</error>';
            $output->writeln($mess);
            return false;
        }
        return $template;
    }
    /**
     * @param string   $apiName
     * @param string   $sectionName
     * @param string[] $posts
     *
     * @return \Yapeal\Xml\EveApiReadWriteInterface
     */
    protected function getXmlData($apiName, $sectionName, $posts)
    {
        return new EveApiXmlData($apiName, $sectionName, $posts);
    }
    /**
     * @param InputInterface $input
     *
     * @return array
     */
    protected function processPost(InputInterface $input)
    {
        /**
         * @type array $posts
         */
        $posts = (array)$input->getArgument('post');
        if (0 !== count($posts)) {
            $arguments = [];
            foreach ($posts as $post) {
                list($key, $value) = explode('=', $post);
                $arguments[$key] = $value;
            }
            $posts = $arguments;
            return $posts;
        }
        return $posts;
    }
    /**
     * @param array  $subs
     * @param string $template
     *
     * @return string
     */
    protected function processTemplate(
        array $subs,
        $template
    ) {
        $keys = [];
        $replacements = [];
        foreach ($subs as $name => $value) {
            $keys[] = '{' . $name . '}';
            $replacements[] = $value;
        }
        return str_replace($keys, $replacements, $template);
    }
    /**
     * @param EveApiReadWriteInterface $data
     *
     * @return array
     */
    protected function processXml(EveApiReadWriteInterface $data)
    {
        $simple = new SimpleXMLElement($data->getEveApiXml());
        $columnNames = (string)$simple->result[0]->rowset[0]['columns'];
        $columnNames = explode(',', $columnNames);
        $keyNames = (string)$simple->result[0]->rowset[0]['key'];
        $keyNames = explode(',', str_replace(' ', '', $keyNames));
        $rowsetName = (string)$simple->result[0]->rowset[0]['name'];
        return [$columnNames, $keyNames, $rowsetName];
    }
    /**
     * @param string $fileName
     * @param string $contents
     *
     * @return int
     */
    protected function saveToFile($fileName, $contents)
    {
        $fileName = $this->fpn->normalizeFile($fileName);
        return file_put_contents($fileName, $contents);
    }
}
