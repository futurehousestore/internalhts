<?php
/* Copyright (C) 2024 FutureHouse Store
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
 * 	\file       core/modules/internalhts/mod_internalhts_standard.php
 * 	\ingroup    internalhts
 * 	\brief      File containing class for numbering module of InternalHTS invoices
 */

require_once DOL_DOCUMENT_ROOT.'/core/modules/internalhts/modules_internalhts.php';

/**
 * 	Class to manage numbering of InternalHTS invoices
 */
class mod_internalhts_standard extends ModeleNumRefInternalHTS
{
	/**
	 * @var string model name
	 */
	public $name = 'standard';

	/**
	 * @var string model description (short text)
	 */
	public $description = "Standard numbering for InternalHTS invoices";

	/**
	 * @var string Numbering example
	 */
	public $example = "IHI2024-0001";

	/**
	 * @var int Automatic numbering
	 */
	public $code_auto = 1;

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
	 * @return string      Text with description
	 */
	public function info()
	{
		global $langs;
		return $langs->trans("StandardModel", $this->example);
	}

	/**
	 * Return an example of numbering
	 *
	 * @return string      Example
	 */
	public function getExample()
	{
		return $this->example;
	}

	/**
	 * Checks if the numbers returned by this numbering module are already used or not
	 *
	 * @return boolean     false if conflict, true if ok
	 */
	public function canBeActivated()
	{
		global $conf, $langs, $db;

		$coyymm = '';
		$max = '';

		$posindice = strlen($this->prefix) + 6;
		$sql = "SELECT MAX(CAST(SUBSTRING(ref FROM ".$posindice.") AS SIGNED)) as max";
		$sql .= " FROM ".MAIN_DB_PREFIX."internalhts_invoice";
		$sql .= " WHERE ref LIKE '".$db->escape($this->prefix)."____-%'";
		if ($this->verifyIsUsed && !empty($conf->global->INTERNALHTS_INVOICE_ADDON) && $conf->global->INTERNALHTS_INVOICE_ADDON != $this->name) {
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
		if (!$coyymm || preg_match('/'.$this->prefix.'[0-9][0-9][0-9][0-9]/i', $coyymm)) {
			return true;
		} else {
			$langs->load("errors");
			$this->error = $langs->trans('ErrorNumRefModel', $max);
			return false;
		}
	}

	/**
	 * 	Return next assigned value
	 *
	 * 	@param  Object		$object		Object we need next value for
	 * 	@return string      			Value if KO, <0 if KO
	 */
	public function getNextValue($object)
	{
		global $db, $conf;

		// first we get the max value
		$posindice = strlen($this->prefix) + 6;
		$sql = "SELECT MAX(CAST(SUBSTRING(ref FROM ".$posindice.") AS SIGNED)) as max";
		$sql .= " FROM ".MAIN_DB_PREFIX."internalhts_invoice";
		$sql .= " WHERE ref LIKE '".$db->escape($this->prefix).date('Y')."-%'";
		$sql .= " AND entity = ".$conf->entity;

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

		$date = empty($object->date_invoice) ? dol_now() : $object->date_invoice;
		$yymm = strftime("%Y", $date);
		$num = sprintf("%04s", $max + 1);

		dol_syslog("mod_internalhts_standard::getNextValue return ".$this->prefix.$yymm."-".$num);
		return $this->prefix.$yymm."-".$num;
	}

	/**
	 * Return next reference not yet used as a reference
	 *
	 * @param	Object	$object		Object we need next value for
	 * @return 	string				Next not used reference
	 */
	public function getNumRef($object)
	{
		return $this->getNextValue($object);
	}
}