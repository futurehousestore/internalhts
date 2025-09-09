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
 * \file class/internalhtsline.class.php
 * \ingroup internalhts
 * \brief This file is a CRUD class file for InternalHTSLine (Create/Read/Update/Delete)
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobjectline.class.php';

/**
 * Class for InternalHTSLine
 */
class InternalHTSLine extends CommonObjectLine
{
    /**
     * @var string ID to identify managed object.
     */
    public $element = 'internalhtsline';

    /**
     * @var string Name of table without prefix where object is stored. This is also the key used for extrafields management.
     */
    public $table_element = 'internalhts_docline';

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
     * @var string String with name of icon for internalhtsline. Must be the part after the 'object_' into object_internalhtsline.png
     */
    public $picto = 'internalhts@internalhts';

    /**
     * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
     */
    public $fields=array(
        'rowid' => array('type'=>'integer', 'label'=>'TechnicalID', 'enabled'=>'1', 'position'=>1, 'notnull'=>1, 'visible'=>0, 'noteditable'=>'1', 'index'=>1, 'css'=>'left', 'comment'=>"Id"),
        'fk_internalhts_doc' => array('type'=>'integer', 'label'=>'InternalHTSDoc', 'enabled'=>'1', 'position'=>10, 'notnull'=>1, 'visible'=>0, 'index'=>1),
        'fk_product' => array('type'=>'integer:Product:product/class/product.class.php', 'label'=>'Product', 'enabled'=>'1', 'position'=>20, 'notnull'=>0, 'visible'=>1, 'foreignkey'=>'product.rowid'),
        'fk_hts' => array('type'=>'integer', 'label'=>'HTSCode', 'enabled'=>'1', 'position'=>21, 'notnull'=>0, 'visible'=>1, 'index'=>1),
        'description' => array('type'=>'text', 'label'=>'Description', 'enabled'=>'1', 'position'=>30, 'notnull'=>0, 'visible'=>1),
        'sku' => array('type'=>'varchar(128)', 'label'=>'SKU', 'enabled'=>'1', 'position'=>31, 'notnull'=>0, 'visible'=>1),
        'country_of_origin' => array('type'=>'varchar(3)', 'label'=>'CountryOfOrigin', 'enabled'=>'1', 'position'=>32, 'notnull'=>0, 'visible'=>1),
        'qty' => array('type'=>'double(24,8)', 'label'=>'Quantity', 'enabled'=>'1', 'position'=>40, 'notnull'=>1, 'visible'=>1, 'default'=>'1'),
        'unit_price' => array('type'=>'double(24,8)', 'label'=>'UnitPrice', 'enabled'=>'1', 'position'=>41, 'notnull'=>0, 'visible'=>1, 'default'=>'0'),
        'customs_unit_value' => array('type'=>'double(24,8)', 'label'=>'CustomsUnitValue', 'enabled'=>'1', 'position'=>42, 'notnull'=>0, 'visible'=>1, 'default'=>'0'),
        'total_ht' => array('type'=>'double(24,8)', 'label'=>'TotalHT', 'enabled'=>'1', 'position'=>43, 'notnull'=>0, 'visible'=>1, 'default'=>'0'),
        'total_customs_value' => array('type'=>'double(24,8)', 'label'=>'TotalCustomsValue', 'enabled'=>'1', 'position'=>44, 'notnull'=>0, 'visible'=>1, 'default'=>'0'),
        'date_creation' => array('type'=>'datetime', 'label'=>'DateCreation', 'enabled'=>'1', 'position'=>500, 'notnull'=>1, 'visible'=>0),
        'tms' => array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>'1', 'position'=>501, 'notnull'=>0, 'visible'=>0),
        'fk_user_creat' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserAuthor', 'enabled'=>'1', 'position'=>510, 'notnull'=>1, 'visible'=>0, 'foreignkey'=>'user.rowid'),
        'fk_user_modif' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserModif', 'enabled'=>'1', 'position'=>511, 'notnull'=>-1, 'visible'=>0),
        'import_key' => array('type'=>'varchar(14)', 'label'=>'ImportId', 'enabled'=>'1', 'position'=>1000, 'notnull'=>-1, 'visible'=>0),
        'rang' => array('type'=>'integer', 'label'=>'Position', 'enabled'=>'1', 'position'=>1001, 'notnull'=>0, 'visible'=>0, 'default'=>'0'),
        'status' => array('type'=>'smallint', 'label'=>'Status', 'enabled'=>'1', 'position'=>1000, 'notnull'=>1, 'visible'=>0, 'index'=>1, 'default'=>'1'),
    );

    public $rowid;
    public $fk_internalhts_doc;
    public $fk_product;
    public $fk_hts;
    public $description;
    public $sku;
    public $country_of_origin;
    public $qty;
    public $unit_price;
    public $customs_unit_value;
    public $total_ht;
    public $total_customs_value;
    public $date_creation;
    public $tms;
    public $fk_user_creat;
    public $fk_user_modif;
    public $import_key;
    public $rang;
    public $status;

    /**
     * Constructor
     *
     * @param DoliDB $db Database handler
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
        $error = 0;

        // Clean parameters
        if (isset($this->description)) {
            $this->description = trim($this->description);
        }
        if (isset($this->sku)) {
            $this->sku = trim($this->sku);
        }
        if (isset($this->country_of_origin)) {
            $this->country_of_origin = trim($this->country_of_origin);
        }

        // Check parameters
        // Put here code to add control on parameters values

        // Insert request
        $sql = 'INSERT INTO '.MAIN_DB_PREFIX.$this->table_element.'(';
        $sql .= 'fk_internalhts_doc,';
        $sql .= 'fk_product,';
        $sql .= 'fk_hts,';
        $sql .= 'description,';
        $sql .= 'sku,';
        $sql .= 'country_of_origin,';
        $sql .= 'qty,';
        $sql .= 'unit_price,';
        $sql .= 'customs_unit_value,';
        $sql .= 'total_ht,';
        $sql .= 'total_customs_value,';
        $sql .= 'date_creation,';
        $sql .= 'fk_user_creat,';
        $sql .= 'rang,';
        $sql .= 'status';
        $sql .= ') VALUES (';
        $sql .= ' '.((int) $this->fk_internalhts_doc).',';
        $sql .= ' '.($this->fk_product ? ((int) $this->fk_product) : 'NULL').',';
        $sql .= ' '.($this->fk_hts ? ((int) $this->fk_hts) : 'NULL').',';
        $sql .= ' '.(!isset($this->description) ? 'NULL' : "'".$this->db->escape($this->description)."'").',';
        $sql .= ' '.(!isset($this->sku) ? 'NULL' : "'".$this->db->escape($this->sku)."'").',';
        $sql .= ' '.(!isset($this->country_of_origin) ? 'NULL' : "'".$this->db->escape($this->country_of_origin)."'").',';
        $sql .= ' '.((double) $this->qty).',';
        $sql .= ' '.((double) $this->unit_price).',';
        $sql .= ' '.((double) $this->customs_unit_value).',';
        $sql .= ' '.((double) $this->total_ht).',';
        $sql .= ' '.((double) $this->total_customs_value).',';
        $sql .= ' '.(!isset($this->date_creation) || dol_strlen($this->date_creation) == 0 ? 'NULL' : "'".$this->db->idate($this->date_creation)."'").',';
        $sql .= ' '.((int) $user->id).',';
        $sql .= ' '.((int) $this->rang).',';
        $sql .= ' '.((int) $this->status);
        $sql .= ')';

        $this->db->begin();

        dol_syslog(get_class($this)."::create", LOG_DEBUG);
        $resql = $this->db->query($sql);
        if (!$resql) {
            $error++; $this->errors[] = "Error ".$this->db->lasterror();
        }

        if (!$error) {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX.$this->table_element);

            if (!$notrigger) {
                // Call triggers
                $result = $this->call_trigger('INTERNALHTSLINE_CREATE', $user);
                if ($result < 0) {
                    $error++;
                }
                // End call triggers
            }
        }

        // Commit or rollback
        if ($error) {
            foreach ($this->errors as $errmsg) {
                dol_syslog(get_class($this)."::create ".$errmsg, LOG_ERR);
                $this->error .= ($this->error ? ', '.$errmsg : $errmsg);
            }
            $this->db->rollback();
            return -1*$error;
        } else {
            $this->db->commit();
            return $this->id;
        }
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
}