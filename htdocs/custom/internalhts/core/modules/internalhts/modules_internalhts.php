<?php
/* Copyright (C) 2024
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file htdocs/custom/internalhts/core/modules/internalhts/modules_internalhts.php
 * \ingroup internalhts
 * \brief File that contains parent class for InternalHTS numbering modules
 */

/**
 * Parent class for InternalHTS numbering modules
 */
abstract class ModeleNumRefInternalHTS
{
    /**
     * @var string Error code (or message)
     */
    public $error = '';

    /**
     * @var string Nom du modele
     * @deprecated
     * @see $name
     */
    public $nom = '';

    /**
     * @var string model name
     */
    public $name = '';

    /**
     * @var string Prefix
     */
    public $prefix = 'IH-';

    /**
     * @var DoliDB Database handler
     */
    public $db;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $db;
        $this->db = $db;
    }

    /**
     * Return if a model can be used or not
     *
     * @return boolean true if model can be used
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     * Returns the default description of the numbering template
     *
     * @param Translate $langs Lang object to use for output
     * @return string Descriptive text
     */
    public function info($langs)
    {
        $langs->load("internalhts@internalhts");
        return $langs->trans("NoDescription");
    }

    /**
     * Returns an example of numbering
     *
     * @return string Example
     */
    public function getExample()
    {
        return $langs->trans("NoExample");
    }

    /**
     * Checks if the numbers already in the database do not
     * cause conflicts that would prevent this numbering working.
     *
     * @param Object $object Object we need a new number for
     * @return boolean false if KO, true if OK
     */
    public function canBeActivated($object)
    {
        return true;
    }

    /**
     * Return next value
     *
     * @param Object $object Object we need a new number for
     * @return string Value if OK, 0 if KO
     */
    abstract public function getNextValue($object);

    /**
     * Return next reference not yet used as a reference
     *
     * @param Object $object Object we need a new number for
     * @return string Next not used reference
     */
    public function internalhts_get_num($object)
    {
        return $this->getNextValue($object);
    }
}