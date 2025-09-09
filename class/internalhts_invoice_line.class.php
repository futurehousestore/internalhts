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
 * \file        class/internalhts_invoice_line.class.php
 * \ingroup     internalhts
 * \brief       This file is a CRUD class file for InternalHTSInvoiceLine (Create/Read/Update/Delete)
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';

/**
 * Class for InternalHTSInvoiceLine
 */
class InternalHTSInvoiceLine extends CommonObject
{
	/**
	 * @var string ID of module.
	 */
	public $module = 'internalhts';

	/**
	 * @var string ID to identify managed object.
	 */
	public $element = 'internalhts_invoice_line';

	/**
	 * @var string Name of table without prefix where object is stored. This is also the key used for extrafields management.
	 */
	public $table_element = 'internalhts_invoice_line';

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
	 * @var string String with name of icon for internalhts_invoice_line. Must be a 'fa-xxx' fontawesome code (or 'fa-xxx_fa_color_size') or 'internalhts_invoice_line@internalhts' if picto is file 'img/object_internalhts_invoice_line.png'.
	 */
	public $picto = 'fa-list';

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
		'fk_internalhts_invoice' => array('type'=>'integer:InternalHTSInvoice:internalhts/class/internalhts_invoice.class.php', 'label'=>'InternalHTSInvoice', 'enabled'=>'1', 'position'=>10, 'notnull'=>1, 'visible'=>-1, 'index'=>1),
		'fk_product' => array('type'=>'integer:Product:product/class/product.class.php', 'label'=>'Product', 'enabled'=>'1', 'position'=>20, 'notnull'=>0, 'visible'=>1, 'index'=>1, 'picto'=>'product'),
		'fk_hts_code' => array('type'=>'integer', 'label'=>'HTSCode', 'enabled'=>'1', 'position'=>30, 'notnull'=>0, 'visible'=>1, 'index'=>1),
		'product_type' => array('type'=>'smallint', 'label'=>'ProductType', 'enabled'=>'1', 'position'=>35, 'notnull'=>0, 'visible'=>1, 'arrayofkeyval'=>array('0'=>'Product', '1'=>'Service')),
		'description' => array('type'=>'text', 'label'=>'Description', 'enabled'=>'1', 'position'=>40, 'notnull'=>0, 'visible'=>1),
		'hts_code' => array('type'=>'varchar(20)', 'label'=>'HTSCodeValue', 'enabled'=>'1', 'position'=>50, 'notnull'=>0, 'visible'=>1),
		'country_origin' => array('type'=>'varchar(2)', 'label'=>'CountryOrigin', 'enabled'=>'1', 'position'=>60, 'notnull'=>0, 'visible'=>1),
		'qty' => array('type'=>'double(10,4)', 'label'=>'Qty', 'enabled'=>'1', 'position'=>70, 'notnull'=>0, 'visible'=>1, 'default'=>1, 'isameasure'=>1),
		'unit_price' => array('type'=>'price', 'label'=>'UnitPrice', 'enabled'=>'1', 'position'=>80, 'notnull'=>0, 'visible'=>1, 'isameasure'=>1),
		'customs_value' => array('type'=>'price', 'label'=>'CustomsValue', 'enabled'=>'1', 'position'=>90, 'notnull'=>0, 'visible'=>1, 'isameasure'=>1),
		'weight_kg' => array('type'=>'double(10,4)', 'label'=>'WeightKg', 'enabled'=>'1', 'position'=>100, 'notnull'=>0, 'visible'=>1, 'isameasure'=>1),
		'packages' => array('type'=>'integer', 'label'=>'Packages', 'enabled'=>'1', 'position'=>110, 'notnull'=>0, 'visible'=>1, 'default'=>0, 'isameasure'=>1),
		'total_ht' => array('type'=>'price', 'label'=>'TotalHT', 'enabled'=>'1', 'position'=>120, 'notnull'=>0, 'visible'=>1, 'isameasure'=>1),
		'rang' => array('type'=>'integer', 'label'=>'Rank', 'enabled'=>'1', 'position'=>130, 'notnull'=>0, 'visible'=>0, 'default'=>0),
		'special_code' => array('type'=>'integer', 'label'=>'SpecialCode', 'enabled'=>'1', 'position'=>140, 'notnull'=>0, 'visible'=>0, 'default'=>0),
		'fk_unit' => array('type'=>'integer', 'label'=>'Unit', 'enabled'=>'1', 'position'=>150, 'notnull'=>0, 'visible'=>0),
		'datec' => array('type'=>'datetime', 'label'=>'DateCreation', 'enabled'=>'1', 'position'=>500, 'notnull'=>1, 'visible'=>-2,),
		'tms' => array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>'1', 'position'=>501, 'notnull'=>0, 'visible'=>-2,),
		'import_key' => array('type'=>'varchar(14)', 'label'=>'ImportId', 'enabled'=>'1', 'position'=>1000, 'notnull'=>-1, 'visible'=>-2,),
	);
	public $rowid;
	public $fk_internalhts_invoice;
	public $fk_product;
	public $fk_hts_code;
	public $product_type;
	public $description;
	public $hts_code;
	public $country_origin;
	public $qty;
	public $unit_price;
	public $customs_value;
	public $weight_kg;
	public $packages;
	public $total_ht;
	public $rang;
	public $special_code;
	public $fk_unit;
	public $datec;
	public $tms;
	public $import_key;
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
		// Calculate totals before creation
		$this->total_ht = $this->qty * $this->unit_price;

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
		// Calculate totals before update
		$this->total_ht = $this->qty * $this->unit_price;

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
		$sql = "SELECT rowid, datec, tms";
		$sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element." as t";
		$sql .= " WHERE t.rowid = ".((int) $id);

		$result = $this->db->query($sql);
		if ($result) {
			if ($this->db->num_rows($result)) {
				$obj = $this->db->fetch_object($result);
				$this->id = $obj->rowid;
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
	 * Get product information if linked
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
}