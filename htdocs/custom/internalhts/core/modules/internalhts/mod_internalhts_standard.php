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
 * \file htdocs/custom/internalhts/core/modules/internalhts/mod_internalhts_standard.php
 * \ingroup internalhts
 * \brief File with class to manage the numbering module Standard for InternalHTS references
 */

require_once DOL_DOCUMENT_ROOT.'/core/modules/internalhts/modules_internalhts.php';

/**
 * Class to manage the numbering module Standard for InternalHTS references
 */
class mod_internalhts_standard extends ModeleNumRefInternalHTS
{
    /**
     * Dolibarr version of the loaded document
     * @var string
     */
    public $version = 'dolibarr'; // 'development', 'experimental', 'dolibarr'

    /**
     * @var string Error code (or message)
     */
    public $error = '';

    /**
     * @var string Nom du modele
     * @deprecated
     * @see $name
     */
    public $nom = 'Standard';

    /**
     * @var string model name
     */
    public $name = 'Standard';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->code_auto = 1;
    }

    /**
     * Return description of numbering module
     *
     * @param Translate $langs Lang object to use for output
     * @return string Descriptive text
     */
    public function info($langs)
    {
        global $langs;
        return $langs->trans("StandardModel").' IH-YYYY-NNNNN';
    }

    /**
     * Return an example of numbering
     *
     * @return string Example
     */
    public function getExample()
    {
        return 'IH-'.date('Y').'-00001';
    }

    /**
     * Checks if the numbers already in the database do not
     * cause conflicts that would prevent this numbering working.
     *
     * @param Object $object Object we need a new number for
     * @return boolean false if conflict, true if ok
     */
    public function canBeActivated($object)
    {
        global $conf, $langs, $db;

        $coyymm = '';
        $max = '';

        $posindice = strlen($this->prefix) + 6;
        $sql = "SELECT MAX(CAST(SUBSTRING(ref FROM ".$posindice.") AS SIGNED)) as max";
        $sql .= " FROM ".MAIN_DB_PREFIX."internalhts_doc";
        $sql .= " WHERE ref LIKE '".$this->db->escape($this->prefix)."____-%'";
        if ($object->ismultientitymanaged == 1) {
            $sql .= " AND entity = ".$conf->entity;
        }

        $resql = $db->query($sql);
        if ($resql) {
            $row = $db->fetch_row($resql);
            if ($row) {
                $coyymm = substr($row[0], 0, 6);
                $max = $row[0];
            }
        }
        if ($coyymm && !preg_match('/'.$this->prefix.'[0-9][0-9][0-9][0-9]/i', $coyymm)) {
            $langs->load("errors");
            $this->error = $langs->trans('ErrorNumRefModel', $max);
            return false;
        }

        return true;
    }

    /**
     * Return next free value
     *
     * @param Object $object Object we need a new number for
     * @return string Value if KO, <0 if KO
     */
    public function getNextValue($object)
    {
        global $db, $conf;

        // First we get the max value
        $posindice = strlen($this->prefix) + 6;
        $sql = "SELECT MAX(CAST(SUBSTRING(ref FROM ".$posindice.") AS SIGNED)) as max";
        $sql .= " FROM ".MAIN_DB_PREFIX."internalhts_doc";
        $sql .= " WHERE ref LIKE '".$this->db->escape($this->prefix).date('Y')."-%'";
        if ($object->ismultientitymanaged == 1) {
            $sql .= " AND entity = ".$conf->entity;
        }

        $resql = $db->query($sql);
        if ($resql) {
            $obj = $db->fetch_object($resql);
            if ($obj) {
                $max = intval($obj->max);
            } else {
                $max = 0;
            }
        } else {
            dol_syslog("mod_internalhts_standard::getNextValue", LOG_DEBUG);
            return -1;
        }

        $date = time();
        $yymm = strftime("%Y", $date);

        if ($max >= (pow(10, 5) - 1)) {
            $num = $max + 1; // If counter > 99999, we do not format on 5 chars, we take number as it is
        } else {
            $num = sprintf("%05s", $max + 1);
        }

        dol_syslog("mod_internalhts_standard::getNextValue return ".$this->prefix.$yymm."-".$num);
        return $this->prefix.$yymm."-".$num;
    }

    // phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
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