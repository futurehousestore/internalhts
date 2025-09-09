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
 * \file        class/hts_mapping.class.php
 * \ingroup     internalhts
 * \brief       This file is a CRUD class file for HTSMapping (Create/Read/Update/Delete)
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';

/**
 * Class for HTSMapping
 */
class HTSMapping extends CommonObject
{
	/**
	 * @var string ID of module.
	 */
	public $module = 'internalhts';

	/**
	 * @var string ID to identify managed object.
	 */
	public $element = 'hts_mapping';

	/**
	 * @var string Name of table without prefix where object is stored. This is also the key used for extrafields management.
	 */
	public $table_element = 'internalhts_hts_mapping';

	/**
	 * @var int  Does this object support multicompany module ?
	 * 0=No test on entity, 1=Test with field entity, 'field@table'=Test with link by field@table
	 */
	public $ismultientitymanaged = 0;

	/**
	 * @var int  Does object support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 1;

	/**
	 * @var string String with name of icon for hts_mapping. Must be a 'fa-xxx' fontawesome code (or 'fa-xxx_fa_color_size') or 'hts_mapping@internalhts' if picto is file 'img/object_hts_mapping.png'.
	 */
	public $picto = 'fa-link';

	/**
	 *  'type' field format:
	 *  See parent class for detailed documentation of field properties.
	 */

	// BEGIN MODULEBUILDER PROPERTIES
	/**
	 * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
	 */
	public $fields=array(
		'rowid' => array('type'=>'integer', 'label'=>'TechnicalID', 'enabled'=>'1', 'position'=>1, 'notnull'=>1, 'visible'=>0, 'noteditable'=>'1', 'index'=>1, 'css'=>'left', 'comment'=>"Id"),
		'fk_product' => array('type'=>'integer:Product:product/class/product.class.php', 'label'=>'Product', 'enabled'=>'1', 'position'=>20, 'notnull'=>1, 'visible'=>1, 'index'=>1, 'picto'=>'product', 'css'=>'maxwidth500 widthcentpercentminusxx'),
		'fk_hts_code' => array('type'=>'integer', 'label'=>'HTSCode', 'enabled'=>'1', 'position'=>30, 'notnull'=>1, 'visible'=>1, 'index'=>1),
		'country_origin' => array('type'=>'varchar(2)', 'label'=>'CountryOrigin', 'enabled'=>'1', 'position'=>40, 'notnull'=>0, 'visible'=>1),
		'customs_value' => array('type'=>'price', 'label'=>'CustomsValue', 'enabled'=>'1', 'position'=>50, 'notnull'=>0, 'visible'=>1, 'default'=>0, 'isameasure'=>1),
		'weight_kg' => array('type'=>'double(10,4)', 'label'=>'WeightKg', 'enabled'=>'1', 'position'=>60, 'notnull'=>0, 'visible'=>1, 'default'=>0, 'isameasure'=>1),
		'active' => array('type'=>'integer', 'label'=>'Active', 'enabled'=>'1', 'position'=>70, 'notnull'=>0, 'visible'=>1, 'default'=>1, 'arrayofkeyval'=>array('0'=>'No', '1'=>'Yes')),
		'datec' => array('type'=>'datetime', 'label'=>'DateCreation', 'enabled'=>'1', 'position'=>500, 'notnull'=>1, 'visible'=>-2,),
		'tms' => array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>'1', 'position'=>501, 'notnull'=>0, 'visible'=>-2,),
		'fk_user_creat' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserCreation', 'enabled'=>'1', 'position'=>510, 'notnull'=>1, 'visible'=>-2, 'foreignkey'=>'user.rowid',),
		'fk_user_modif' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserModif', 'enabled'=>'1', 'position'=>511, 'notnull'=>-1, 'visible'=>-2,),
	);
	public $rowid;
	public $fk_product;
	public $fk_hts_code;
	public $country_origin;
	public $customs_value;
	public $weight_kg;
	public $active;
	public $datec;
	public $tms;
	public $fk_user_creat;
	public $fk_user_modif;
	// END MODULEBUILDER PROPERTIES

	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		global $conf, $langs;

		$this->db = $db;

		if (empty($conf->global->MAIN_SHOW_TECHNICAL_ID) && isset($this->fields['rowid'])) {
			$this->fields['rowid']['visible'] = 0;
		}
		if (!isModEnabled('multicompany') && isset($this->fields['entity'])) {
			$this->fields['entity']['enabled'] = 0;
		}

		// Unset fields that are disabled
		foreach ($this->fields as $key => $val) {
			if (isset($val['enabled']) && empty($val['enabled'])) {
				unset($this->fields[$key]);
			}
		}

		// Translate some data of arrayofkeyval
		if (is_object($langs)) {
			foreach ($this->fields as $key => $val) {
				if (!empty($val['arrayofkeyval']) && is_array($val['arrayofkeyval'])) {
					foreach ($val['arrayofkeyval'] as $key2 => $val2) {
						$this->fields[$key]['arrayofkeyval'][$key2] = $langs->trans($val2);
					}
				}
			}
		}
	}

	/**
	 * Create object into database
	 *
	 * @param  User $user      User that creates
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, Id of created object if OK
	 */
	public function create(User $user, $notrigger = false)
	{
		return $this->createCommon($user, $notrigger);
	}

	/**
	 * Load object in memory from the database
	 *
	 * @param int    $id   Id object
	 * @param string $ref  Ref
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetch($id, $ref = null)
	{
		$result = $this->fetchCommon($id, $ref);
		return $result;
	}

	/**
	 * Load list of objects in memory from the database.
	 *
	 * @param  string      $sortorder    Sort Order
	 * @param  string      $sortfield    Sort field
	 * @param  int         $limit        limit
	 * @param  int         $offset       Offset
	 * @param  array       $filter       Filter array. Example array('field'=>'valueforlike', 'customurl'=>...)
	 * @param  string      $filtermode   Filter mode (AND or OR)
	 * @return array|int                 int <0 if KO, array of pages if OK
	 */
	public function fetchAll($sortorder = '', $sortfield = '', $limit = 0, $offset = 0, array $filter = array(), $filtermode = 'AND')
	{
		dol_syslog(__METHOD__, LOG_DEBUG);

		$records = array();

		$sql = "SELECT ";
		$sql .= $this->getFieldList('t');
		$sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element." as t";
		$sql .= " WHERE 1 = 1";
		// Manage filter
		$sqlwhere = array();
		if (count($filter) > 0) {
			foreach ($filter as $key => $value) {
				if ($key == 't.rowid') {
					$sqlwhere[] = $key." = ".((int) $value);
				} elseif (in_array($this->fields[$key]['type'], array('date', 'datetime', 'timestamp'))) {
					$sqlwhere[] = $key." = '".$this->db->escape($value)."'";
				} elseif ($key == 'customsql') {
					$sqlwhere[] = $value;
				} elseif (strpos($value, '%') === false) {
					$sqlwhere[] = $key." IN (".$this->db->sanitize($this->db->escape($value)).")";
				} else {
					$sqlwhere[] = $key." LIKE '%".$this->db->escape($value)."%'";
				}
			}
		}
		if (count($sqlwhere) > 0) {
			$sql .= " AND (".implode(" ".$filtermode." ", $sqlwhere).")";
		}

		if (!empty($sortfield)) {
			$sql .= $this->db->order($sortfield, $sortorder);
		}
		if (!empty($limit)) {
			$sql .= $this->db->plimit($limit, $offset);
		}

		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < ($limit ? min($limit, $num) : $num)) {
				$obj = $this->db->fetch_object($resql);

				$record = new self($this->db);
				$record->setVarsFromFetchObj($obj);

				$records[$record->id] = $record;

				$i++;
			}
			$this->db->free($resql);

			return $records;
		} else {
			$this->errors[] = 'Error '.$this->db->lasterror();
			dol_syslog(__METHOD__.' '.join(',', $this->errors), LOG_ERR);

			return -1;
		}
	}

	/**
	 * Update object into database
	 *
	 * @param  User $user      User that modifies
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function update(User $user, $notrigger = false)
	{
		return $this->updateCommon($user, $notrigger);
	}

	/**
	 * Delete object in database
	 *
	 * @param User $user       User that deletes
	 * @param bool $notrigger  false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function delete(User $user, $notrigger = false)
	{
		return $this->deleteCommon($user, $notrigger);
	}

	/**
	 *	Load the info information in the object
	 *
	 *	@param  int		$id       Id of object
	 *	@return	void
	 */
	public function info($id)
	{
		$sql = "SELECT rowid, datec, tms, fk_user_creat, fk_user_modif";
		$sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element." as t";
		$sql .= " WHERE t.rowid = ".((int) $id);

		$result = $this->db->query($sql);
		if ($result) {
			if ($this->db->num_rows($result)) {
				$obj = $this->db->fetch_object($result);
				$this->id = $obj->rowid;
				$this->user_creation_id = $obj->fk_user_creat;
				$this->user_modification_id = $obj->fk_user_modif;
				$this->date_creation     = $this->db->jdate($obj->datec);
				$this->date_modification = empty($obj->tms) ? '' : $this->db->jdate($obj->tms);
			}

			$this->db->free($result);
		} else {
			dol_print_error($this->db);
		}
	}

	/**
	 * Initialise object with example values
	 * Id must be 0 if object instance is a specimen
	 *
	 * @return void
	 */
	public function initAsSpecimen()
	{
		$this->initAsSpecimenCommon();
	}

	/**
	 *  Return a link to the object card (with optionaly the picto)
	 *
	 *  @param  int     $withpicto                  Include picto in link (0=No picto, 1=Include picto into link, 2=Only picto)
	 *  @param  string  $option                     On what the link point to ('nolink', ...)
	 *  @param  int     $notooltip                  1=Disable tooltip
	 *  @param  string  $morecss                    Add more css on link
	 *  @param  int     $save_lastsearch_value      -1=Auto, 0=No save of lastsearch_values, 1=Save lastsearch_values
	 *  @return	string                              String with URL
	 */
	public function getNomUrl($withpicto = 0, $option = '', $notooltip = 0, $morecss = '', $save_lastsearch_value = -1)
	{
		global $conf, $langs, $hookmanager;

		if (!empty($conf->dol_no_mouse_hover)) {
			$notooltip = 1; // Force disable tooltips
		}

		$result = '';

		$label = img_picto('', $this->picto).' <u>'.$langs->trans("HTSMapping").'</u>';
		$label .= '<br>';
		$label .= '<b>'.$langs->trans('Product').':</b> '.$this->fk_product;

		$url = dol_buildpath('/internalhts/hts_mapping_card.php', 1).'?id='.$this->id;

		if ($option != 'nolink') {
			// Add param to save lastsearch_values or not
			$add_save_lastsearch_values = ($save_lastsearch_value == 1 ? 1 : 0);
			if ($save_lastsearch_value == -1 && preg_match('/list\.php/', $_SERVER["PHP_SELF"])) {
				$add_save_lastsearch_values = 1;
			}
			if ($add_save_lastsearch_values) {
				$url .= '&save_lastsearch_values=1';
			}
		}

		$linkclose = '';
		if (empty($notooltip)) {
			if (!empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)) {
				$label = $langs->trans("ShowHTSMapping");
				$linkclose .= ' alt="'.$label.'"';
			}
			$linkclose .= ' title="'.dol_escape_htmltag($label, 1).'"';
			$linkclose .= ' class="classfortooltip'.($morecss ? ' '.$morecss : '').'"';
		} else {
			$linkclose = ($morecss ? ' class="'.$morecss.'"' : '');
		}

		if ($option == 'nolink') {
			$linkstart = '<span';
		} else {
			$linkstart = '<a href="'.$url.'"';
		}
		$linkstart .= $linkclose.'>';
		if ($option == 'nolink') {
			$linkend = '</span>';
		} else {
			$linkend = '</a>';
		}

		$result .= $linkstart;

		if ($withpicto) {
			$result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
		}

		if ($withpicto != 2) {
			$result .= $this->fk_product; // Show product ID as reference
		}

		$result .= $linkend;

		return $result;
	}

	/**
	 * Get HTS code information
	 *
	 * @return array|int HTS code data if OK, <0 if KO
	 */
	public function getHTSCodeInfo()
	{
		if (empty($this->fk_hts_code)) {
			return array();
		}

		$sql = "SELECT code, label, description, duty_rate";
		$sql .= " FROM ".MAIN_DB_PREFIX."c_hts_codes";
		$sql .= " WHERE rowid = ".((int) $this->fk_hts_code);
		$sql .= " AND active = 1";

		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				return array(
					'code' => $obj->code,
					'label' => $obj->label,
					'description' => $obj->description,
					'duty_rate' => $obj->duty_rate
				);
			}
			$this->db->free($resql);
		} else {
			$this->errors[] = 'Error '.$this->db->lasterror();
			return -1;
		}

		return array();
	}

	/**
	 * Get product information
	 *
	 * @return array|int Product data if OK, <0 if KO
	 */
	public function getProductInfo()
	{
		if (empty($this->fk_product)) {
			return array();
		}

		require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
		$product = new Product($this->db);
		$result = $product->fetch($this->fk_product);
		
		if ($result > 0) {
			return array(
				'ref' => $product->ref,
				'label' => $product->label,
				'description' => $product->description,
				'price' => $product->price,
				'weight' => $product->weight,
				'weight_units' => $product->weight_units
			);
		}

		return array();
	}

	/**
	 * Get mapping for a specific product
	 *
	 * @param int $product_id Product ID
	 * @return HTSMapping|int HTSMapping object if found, 0 if not found, <0 if KO
	 */
	public static function getByProduct($db, $product_id)
	{
		$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."internalhts_hts_mapping";
		$sql .= " WHERE fk_product = ".((int) $product_id);
		$sql .= " AND active = 1";

		$resql = $db->query($sql);
		if ($resql) {
			if ($db->num_rows($resql)) {
				$obj = $db->fetch_object($resql);
				$mapping = new HTSMapping($db);
				$result = $mapping->fetch($obj->rowid);
				if ($result > 0) {
					return $mapping;
				}
			}
			$db->free($resql);
		}

		return 0;
	}

	/**
	 * Import HTS codes from CSV file
	 *
	 * @param string $filepath Path to CSV file
	 * @param User $user User importing
	 * @return int Number of imported records if OK, <0 if KO
	 */
	public static function importHTSCodes($db, $filepath, $user)
	{
		if (!file_exists($filepath)) {
			return -1;
		}

		$imported = 0;
		$handle = fopen($filepath, 'r');
		
		if ($handle === false) {
			return -2;
		}

		// Skip header line
		fgetcsv($handle);

		while (($data = fgetcsv($handle)) !== false) {
			if (count($data) >= 3) {
				$code = trim($data[0]);
				$label = trim($data[1]);
				$description = isset($data[2]) ? trim($data[2]) : '';
				$duty_rate = isset($data[3]) ? (float)$data[3] : 0;

				// Check if code already exists
				$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."c_hts_codes WHERE code = '".$db->escape($code)."'";
				$resql = $db->query($sql);
				
				if ($resql && $db->num_rows($resql) == 0) {
					// Insert new HTS code
					$sql = "INSERT INTO ".MAIN_DB_PREFIX."c_hts_codes (code, label, description, duty_rate, active, datec)";
					$sql .= " VALUES ('".$db->escape($code)."', '".$db->escape($label)."', '".$db->escape($description)."', ".$duty_rate.", 1, NOW())";
					
					if ($db->query($sql)) {
						$imported++;
					}
				}
				
				if ($resql) {
					$db->free($resql);
				}
			}
		}

		fclose($handle);
		return $imported;
	}
}