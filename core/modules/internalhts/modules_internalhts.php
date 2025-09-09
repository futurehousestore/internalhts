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
 * 		\file       core/modules/internalhts/modules_internalhts.php
 * 		\ingroup    internalhts
 * 		\brief      File that contains parent class for numbering models of InternalHTS module
 */

/**
 * 	Parent class of numbering models for InternalHTS invoices
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
	 * @var string Model name
	 */
	public $name = '';

	/**
	 * @var string Version
	 */
	public $version = '';

	/**
	 * @var string Prefix
	 */
	public $prefix = 'IHI';

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->nom = $this->name;
	}

	/**
	 * Return if a module can be used or not
	 *
	 * @return boolean     true if module can be used
	 */
	public function isEnabled()
	{
		return true;
	}

	/**
	 * Returns the default description of the numbering template
	 *
	 * @return string      Descriptive text
	 */
	public function info()
	{
		global $langs;
		$langs->load("internalhts@internalhts");
		return $langs->trans("NoDescription");
	}

	/**
	 * Returns an example of the numbering
	 *
	 * @return string      Example
	 */
	public function getExample()
	{
		global $langs;
		$langs->load("internalhts@internalhts");
		return $langs->trans("NoExample");
	}

	/**
	 * Checks if the numbers returned by this numbering module are already used or not
	 *
	 * @return boolean     false if conflict, true if ok
	 */
	public function canBeActivated()
	{
		return true;
	}

	/**
	 * Returns next assigned value
	 *
	 * @param  Object		$object		Object we need next value for
	 * @return string      				Value if OK, 0 if KO
	 */
	public function getNextValue($object)
	{
		global $langs;
		return $langs->trans("NotAvailable");
	}

	/**
	 * Returns version of numbering module
	 *
	 * @return string      Version string
	 */
	public function getVersion()
	{
		global $langs;
		$langs->load("admin");

		if ($this->version == 'development') {
			return $langs->trans("VersionDevelopment");
		} elseif ($this->version == 'experimental') {
			return $langs->trans("VersionExperimental");
		} elseif ($this->version == 'dolibarr') {
			return DOL_VERSION;
		} elseif ($this->version) {
			return $this->version;
		} else {
			return $langs->trans("NotAvailable");
		}
	}
}