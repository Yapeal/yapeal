<?php
/**
 * Contains IndustryJobs class.
 *
 * PHP version 5.4
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal which can be used to access the Eve Online
 * API data and place it into a database.
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
namespace Yapeal\Database\Corp;

use Yapeal\Database\AttributesDatabasePreserverTrait;
use Yapeal\Database\EveApiNameTrait;

/**
 * Class IndustryJobs
 */
class IndustryJobsHistory extends AbstractCorpSection
{
    use EveApiNameTrait, AttributesDatabasePreserverTrait;
    /**
     * @param string $xml
     * @param string $ownerID
     *
     * @return self
     */
    protected function preserverToIndustryJobsHistory(
        $xml,
        $ownerID
    ) {
        $columnDefaults = array(
            'ownerID' => $ownerID,
            'activityID' => null,
            'blueprintID' => null,
            'blueprintLocationID' => null,
            'blueprintTypeID' => null,
            'blueprintTypeName' => null,
            'completedCharacterID' => 0,
            'completedDate' => '1970-01-01 00:00:01',
            'cost' => null,
            'endDate' => '1970-01-01 00:00:01',
            'facilityID' => null,
            'installerID' => null,
            'installerName' => null,
            'jobID' => null,
            'licensedRuns' => null,
            'outputLocationID' => null,
            'pauseDate' => '1970-01-01 00:00:01',
            'probability' => null,
            'productTypeID' => null,
            'productTypeName' => null,
            'runs' => 0,
            'solarSystemID' => null,
            'solarSystemName' => null,
            'startDate' => null,
            'stationID' => null,
            'status' => null,
            'teamID' => null,
            'timeInSeconds' => null
        );
        $this->attributePreserveData(
            $xml,
            $columnDefaults,
            'corpIndustryJobs'
        );
        return $this;
    }
    /**
     * @var int $mask
     */
    protected $mask = 128;
}
