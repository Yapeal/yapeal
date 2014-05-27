<?php
/**
 * Contains RowSetTransformer class.
 *
 * PHP version 5.3
 *
 * LICENSE:
 * This file is part of Yapeal
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
namespace Yapeal\Validation;

use Yapeal\Dependency\DependenceAwareInterface;
use Yapeal\Dependency\DependenceInterface;
use Yapeal\Xml\ProcessorInterface;
use Yapeal\Xml\ReaderInterface;

/**
 * Class RowSetTransformer
 */
class RowSetTransformer implements DependenceAwareInterface
{
    /**
     * @param DependenceInterface|null $depend
     *
     * @return self
     */
    public function setDependenceContainer(DependenceInterface $depend = null)
    {
        $this->dependenceContainer = $depend;
        return $this;
    }
    /**
     * @param null|string|ProcessorInterface $value
     *
     * @return self
     */
    public function setProcessor($value = null)
    {
        $this->processor = $value;
        return $this;
    }
    /**
     * @param null|string|ReaderInterface $value
     *
     * @return self
     */
    public function setReader($value = null)
    {
        $this->reader = $value;
        return $this;
    }
    /**
     * @var DependenceInterface|null
     */
    protected $dependenceContainer;
    /**
     * @var \XSLTProcessor|string|null
     */
    protected $processor;
    /**
     * @var ReaderInterface|string|null
     */
    protected $reader;
    /**
     * @throws \LogicException
     * @return null|DependenceInterface
     */
    protected function getDependenceContainer()
    {
        if (empty($this->dependenceContainer)) {
            $mess = 'Tried to use dependenceContainer when NOT set';
            throw new \LogicException($mess);
        }
        return $this->dependenceContainer;
    }
    /**
     * @throws \LogicException
     * @return null|string|ProcessorInterface
     */
    protected function getProcessor()
    {
        if (empty($this->processor)) {
            $mess = 'Tried to use processor when NOT set';
            throw new \LogicException($mess);
        }
        if (is_string($this->processor)) {
        }
        return $this->processor;
    }
    /**
     * @throws \LogicException
     * @return null|string|ReaderInterface
     */
    protected function getReader()
    {
        if (empty($this->reader)) {
            $mess = 'Tried to use $reader when it was NOT set';
            throw new \LogicException($mess);
        } elseif (is_string($this->reader)) {
            $dependence = $this->getDependenceContainer();
            if (empty($dependence[$this->reader])) {
                $mess =
                    'Dependence container does NOT contain ' . $this->reader;
                throw new \DomainException($mess);
            }
            $this->reader = $dependence[$this->reader];
        }
        if (!$this->reader instanceof ReaderInterface) {
            $mess = '$reader could NOT be resolved to instance of'
                . ' ReaderInterface is instead ' . gettype($this->reader);
            throw new \InvalidArgumentException($mess);
        }
        return $this->reader;
    }
}
