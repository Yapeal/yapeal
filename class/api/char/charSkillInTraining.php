<?php
/**
 * Contains SkillInTraining class.
 *
 * PHP version 5
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal which can be used to access the Eve Online
 * API data and place it into a database.
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public License
 *  along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author     Michael Cummings <mgcummings@yahoo.com>
 * @copyright  Copyright (c) 2008-2014, Michael Cummings
 * @license    http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @link       http://code.google.com/p/yapeal/
 * @link       http://www.eveonline.com/
 */
use Yapeal\Database\AbstractChar;
use Yapeal\Database\QueryBuilder;

/**
 * Class used to fetch and store CharacterSheet API.
 */
class charSkillInTraining extends AbstractChar
{
    /**
     * Constructor
     *
     * @param array $params Holds the required parameters like keyID, vCode, etc
     *                      used in HTML POST parameters to API servers which varies depending on API
     *                      'section' being requested.
     *
     * @throws LengthException for any missing required $params.
     */
    public function __construct(array $params)
    {
        // Cut off 'A' and lower case abstract class name to make section name.
        $this->section = strtolower(substr(get_parent_class($this), 1));
        $this->api = str_replace($this->section, '', __CLASS__);
        parent::__construct($params);
    }
    /**
     * Per API parser for XML.
     *
     * @return bool Returns TRUE if XML was parsed correctly, FALSE if not.
     */
    protected function parserAPI()
    {
        $tableName = YAPEAL_TABLE_PREFIX . $this->section . $this->api;
        // Get a new query instance.
        $qb = new QueryBuilder($tableName, YAPEAL_DSN);
        $row = array(
            'currentTQTime' => YAPEAL_START_TIME,
            'offset' => 0,
            'ownerID' => $this->params['characterID'],
            'skillInTraining' => 0,
            'trainingDestinationSP' => 0,
            'trainingEndTime' => YAPEAL_START_TIME,
            'trainingStartSP' => 0,
            'trainingStartTime' => YAPEAL_START_TIME,
            'trainingToLevel' => 0,
            'trainingTypeID' => 0
        );
        try {
            while ($this->xr->read()) {
                switch ($this->xr->nodeType) {
                    case XMLReader::ELEMENT:
                        switch ($this->xr->localName) {
                            case 'skillInTraining':
                            case 'trainingDestinationSP':
                            case 'trainingEndTime':
                            case 'trainingStartSP':
                            case 'trainingStartTime':
                            case 'trainingToLevel':
                            case 'trainingTypeID':
                                // Grab node name.
                                $name = $this->xr->localName;
                                // Move to text node.
                                $this->xr->read();
                                $row[$name] = $this->xr->value;
                                break;
                            case 'currentTQTime':
                                $row['offset'] =
                                    $this->xr->getAttribute('offset');
                                // Move to text node.
                                $this->xr->read();
                                $row['currentTQTime'] = $this->xr->value;
                                break;
                            default: // Nothing to do.
                        }
                        break;
                    case XMLReader::END_ELEMENT:
                        if ($this->xr->localName == 'result') {
                            $qb->addRow($row);
                            $qb->store();
                            $qb = null;
                            return true;
                        }
                        break;
                    default: // Nothing to do.
                }
            }
        } catch (ADODB_Exception $e) {
            Logger::getLogger('yapeal')
                  ->error($e);
            return false;
        }
        $mess =
            'Function ' . __FUNCTION__ . ' did not exit correctly' . PHP_EOL;
        Logger::getLogger('yapeal')
              ->warn($mess);
        return false;
    }
}

