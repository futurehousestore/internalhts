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
 * \file class/internalhts.class.php
 * \ingroup internalhts
 * \brief This file is a CRUD class file for InternalHTS (Create/Read/Update/Delete)
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/internalhts/class/internalhtsline.class.php';

/**
 * Class for InternalHTS
 */
class InternalHTS extends CommonObject
{
    /**
     * @var string ID to identify managed object.
     */
    public $element = 'internalhts';

    /**
     * @var string Name of table without prefix where object is stored. This is also the key used for extrafields management.
     */
    public $table_element = 'internalhts_doc';

    /**
     * @var int  Does this object support multicompany module ?
     * 0=No test on entity, 1=Test with field entity, 'field@table'=Test with link by field@table
     */
    public $ismultientitymanaged = 1;

    /**
     * @var int  Does object support extrafields ? 0=No, 1=Yes
     */
    public $isextrafieldmanaged = 1;

    /**
     * @var string String with name of icon for internalhts. Must be the part after the 'object_' into object_internalhts.png
     */
    public $picto = 'internalhts@internalhts';

    const STATUS_DRAFT = 0;
    const STATUS_VALIDATED = 1;

    /**
     * 'type' field format ('integer', 'integer:ObjectClass:PathToClass[:AddCreateButtonOrNot[:Filter]]', 'sellist:TableName:LabelFieldName[:KeyFieldName[:KeyFieldParent[:Filter]]]', 'varchar(x)', 'double(24,8)', 'real', 'price', 'text', 'text:none', 'html', 'date', 'datetime', 'timestamp', 'duration', 'mail', 'phone', 'url', 'password')
     * 'label' the translation key.
     * 'picto' is code of a picto to show before value in forms
     * 'enabled' is a condition when the field must be managed (Example: 1 or 'isModEnabled("accounting")')
     * 'position' is the sort order of field.
     * 'notnull' is set to 1 if not null in database. Set to -1 if we must set data to null if empty ('' or 0).
     * 'visible' says if field is visible in list (Examples: 0=Not visible, 1=Visible on list and create/update/view forms, 2=Visible on list only, 3=Visible on create/update/view form only (not list), 4=Visible on list but not on forms, 5=Visible on list but not on create forms)
     * 'noteditable' says if field is not editable (1 or 0)
     * 'default' is a default value for creation (can still be overwrote by the Setup of Default Values if field is editable in creation form). Note: If default is set to '(PROV)' and field is 'ref', the default is replaced by the next nomenclature value.
     * 'index' if we want an index in database.
     * 'foreignkey'=>'tablename.field' if the field is a foreign key (it is recommanded to name the field fk_...).
     * 'searchall' is 1 if we want to search in this field when making a "search in all"
     * 'isameasure' must be set to 1 if you want to have a total on list for this field. Field type must be summable like integer or double(24,8).
     * 'css' and 'cssview' and 'csslist' is the CSS style to use on field. 'css' is used in creation and update. 'cssview' is used in view mode. 'csslist' is used in list mode.
     * 'help' is a 'TranslationString' to use to show a tooltip on field. You can also use 'TranslationString:keyfortoolip' for a tooltip with parameter.
     * 'showoncombobox' if value of the field must be visible into the label of the combobox that list record
     * 'disabled' is 1 if we want to have the field locked by a 'disabled' attribute. In most cases, this is never set into the definition of $fields into class, but is set dynamically by some part of code.
     * 'arrayofkeyval' to set list of value if type is a list of predefined values. For example: array("0"=>"Draft","1"=>"Active","-1"=>"Cancel")
     * 'autofocusoncreate' to have field having the focus on a create form. Only 1 field should have this property set to 1.
     * 'comment' is not used. You can store here any text of your choice. It is not used by application.
     *
     * To show field computed or used by API:
     * 'computed' is 1 if field is a computed field (stored value is result of expression)
     * 'api' is 1 if field can be used for API
     */

    /**
     * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
     */
    public $fields=array(
        'rowid' => array('type'=>'integer', 'label'=>'TechnicalID', 'enabled'=>'1', 'position'=>1, 'notnull'=>1, 'visible'=>0, 'noteditable'=>'1', 'index'=>1, 'css'=>'left', 'comment'=>"Id"),
        'ref' => array('type'=>'varchar(128)', 'label'=>'Ref', 'enabled'=>'1', 'position'=>20, 'notnull'=>1, 'visible'=>1, 'noteditable'=>'1', 'default'=>'(PROV)', 'index'=>1, 'searchall'=>1, 'showoncombobox'=>'1', 'comment'=>"Reference of object"),
        'label' => array('type'=>'varchar(255)', 'label'=>'Label', 'enabled'=>'1', 'position'=>30, 'notnull'=>0, 'visible'=>1, 'searchall'=>1, 'css'=>'minwidth300', 'help'=>"Help text", 'showoncombobox'=>'2'),
        'description' => array('type'=>'text', 'label'=>'Description', 'enabled'=>'1', 'position'=>60, 'notnull'=>0, 'visible'=>3),
        'note_public' => array('type'=>'html', 'label'=>'NotePublic', 'enabled'=>'1', 'position'=>61, 'notnull'=>0, 'visible'=>0),
        'note_private' => array('type'=>'html', 'label'=>'NotePrivate', 'enabled'=>'1', 'position'=>62, 'notnull'=>0, 'visible'=>0),
        'date_creation' => array('type'=>'datetime', 'label'=>'DateCreation', 'enabled'=>'1', 'position'=>500, 'notnull'=>1, 'visible'=>0),
        'tms' => array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>'1', 'position'=>501, 'notnull'=>0, 'visible'=>0),
        'fk_user_creat' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserAuthor', 'enabled'=>'1', 'position'=>510, 'notnull'=>1, 'visible'=>0, 'foreignkey'=>'user.rowid'),
        'fk_user_modif' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserModif', 'enabled'=>'1', 'position'=>511, 'notnull'=>-1, 'visible'=>0),
        'last_main_doc' => array('type'=>'varchar(255)', 'label'=>'LastMainDoc', 'enabled'=>'1', 'position'=>600, 'notnull'=>0, 'visible'=>0),
        'import_key' => array('type'=>'varchar(14)', 'label'=>'ImportId', 'enabled'=>'1', 'position'=>1000, 'notnull'=>-1, 'visible'=>0),
        'model_pdf' => array('type'=>'varchar(255)', 'label'=>'Model pdf', 'enabled'=>'1', 'position'=>1010, 'notnull'=>-1, 'visible'=>0),
        'status' => array('type'=>'smallint', 'label'=>'Status', 'enabled'=>'1', 'position'=>1000, 'notnull'=>1, 'visible'=>1, 'index'=>1, 'default'=>'0', 'arrayofkeyval'=>array('0'=>'Draft', '1'=>'Validated')),
        'incoterm' => array('type'=>'varchar(50)', 'label'=>'Incoterm', 'enabled'=>'1', 'position'=>40, 'notnull'=>0, 'visible'=>1),
        'value_source' => array('type'=>'varchar(50)', 'label'=>'ValueSource', 'enabled'=>'1', 'position'=>41, 'notnull'=>0, 'visible'=>1),
        'apportionment_mode' => array('type'=>'varchar(50)', 'label'=>'ApportionmentMode', 'enabled'=>'1', 'position'=>42, 'notnull'=>0, 'visible'=>1),
        'total_ht' => array('type'=>'double(24,8)', 'label'=>'TotalHT', 'enabled'=>'1', 'position'=>43, 'notnull'=>0, 'visible'=>1, 'default'=>'0'),
        'total_customs_value' => array('type'=>'double(24,8)', 'label'=>'TotalCustomsValue', 'enabled'=>'1', 'position'=>44, 'notnull'=>0, 'visible'=>1, 'default'=>'0'),
        'ship_from_country' => array('type'=>'varchar(3)', 'label'=>'ShipFromCountry', 'enabled'=>'1', 'position'=>45, 'notnull'=>0, 'visible'=>1),
        'ship_to_country' => array('type'=>'varchar(3)', 'label'=>'ShipToCountry', 'enabled'=>'1', 'position'=>46, 'notnull'=>0, 'visible'=>1),
        'currency' => array('type'=>'varchar(3)', 'label'=>'Currency', 'enabled'=>'1', 'position'=>47, 'notnull'=>0, 'visible'=>1, 'default'=>'USD'),
        'exchange_rate' => array('type'=>'double(24,8)', 'label'=>'ExchangeRate', 'enabled'=>'1', 'position'=>48, 'notnull'=>0, 'visible'=>1, 'default'=>'1'),
    );

    public $rowid;
    public $ref;
    public $label;
    public $description;
    public $note_public;
    public $note_private;
    public $date_creation;
    public $tms;
    public $fk_user_creat;
    public $fk_user_modif;
    public $fk_user_valid;
    public $last_main_doc;
    public $import_key;
    public $model_pdf;
    public $status;
    public $incoterm;
    public $value_source;
    public $apportionment_mode;
    public $total_ht;
    public $total_customs_value;
    public $date_valid;
    public $ship_from_country;
    public $ship_to_country;
    public $currency;
    public $exchange_rate;

    /**
     * @var InternalHTSLine[] Lines
     */
    public $lines = array();

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

        // Example to show how to set values of fields definition dynamically
        /*if ($user->hasRight('internalhts', 'read')) {
            $this->fields['myfield']['visible'] = 1;
            $this->fields['myfield']['noteditable'] = 0;
        }*/

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
        global $conf;

        $error = 0;

        // Clean parameters
        if (isset($this->ref)) {
            $this->ref = trim($this->ref);
        }
        if (isset($this->label)) {
            $this->label = trim($this->label);
        }
        if (isset($this->description)) {
            $this->description = trim($this->description);
        }
        if (isset($this->note_public)) {
            $this->note_public = trim($this->note_public);
        }
        if (isset($this->note_private)) {
            $this->note_private = trim($this->note_private);
        }
        if (isset($this->import_key)) {
            $this->import_key = trim($this->import_key);
        }

        // Check parameters
        // Put here code to add control on parameters values

        // Insert request
        $sql = 'INSERT INTO '.MAIN_DB_PREFIX.$this->table_element.'(';
        $sql .= 'ref,';
        $sql .= 'label,';
        $sql .= 'description,';
        $sql .= 'note_public,';
        $sql .= 'note_private,';
        $sql .= 'date_creation,';
        $sql .= 'fk_user_creat,';
        $sql .= 'status,';
        $sql .= 'incoterm,';
        $sql .= 'value_source,';
        $sql .= 'apportionment_mode,';
        $sql .= 'total_ht,';
        $sql .= 'total_customs_value,';
        $sql .= 'ship_from_country,';
        $sql .= 'ship_to_country,';
        $sql .= 'currency,';
        $sql .= 'exchange_rate';
        $sql .= ') VALUES (';
        $sql .= ' '.(!isset($this->ref) ? 'NULL' : "'".$this->db->escape($this->ref)."'").',';
        $sql .= ' '.(!isset($this->label) ? 'NULL' : "'".$this->db->escape($this->label)."'").',';
        $sql .= ' '.(!isset($this->description) ? 'NULL' : "'".$this->db->escape($this->description)."'").',';
        $sql .= ' '.(!isset($this->note_public) ? 'NULL' : "'".$this->db->escape($this->note_public)."'").',';
        $sql .= ' '.(!isset($this->note_private) ? 'NULL' : "'".$this->db->escape($this->note_private)."'").',';
        $sql .= ' '.(!isset($this->date_creation) || dol_strlen($this->date_creation) == 0 ? 'NULL' : "'".$this->db->idate($this->date_creation)."'").',';
        $sql .= ' '.((int) $user->id).',';
        $sql .= ' '.((int) $this->status).',';
        $sql .= ' '.(!isset($this->incoterm) ? 'NULL' : "'".$this->db->escape($this->incoterm)."'").',';
        $sql .= ' '.(!isset($this->value_source) ? 'NULL' : "'".$this->db->escape($this->value_source)."'").',';
        $sql .= ' '.(!isset($this->apportionment_mode) ? 'NULL' : "'".$this->db->escape($this->apportionment_mode)."'").',';
        $sql .= ' '.((double) $this->total_ht).',';
        $sql .= ' '.((double) $this->total_customs_value).',';
        $sql .= ' '.(!isset($this->ship_from_country) ? 'NULL' : "'".$this->db->escape($this->ship_from_country)."'").',';
        $sql .= ' '.(!isset($this->ship_to_country) ? 'NULL' : "'".$this->db->escape($this->ship_to_country)."'").',';
        $sql .= ' '.(!isset($this->currency) ? "'USD'" : "'".$this->db->escape($this->currency)."'").',';
        $sql .= ' '.((double) ($this->exchange_rate ?: 1));
        $sql .= ')';

        $this->db->begin();

        dol_syslog(get_class($this)."::create", LOG_DEBUG);
        $resql = $this->db->query($sql);
        if (!$resql) {
            $error++; $this->errors[] = "Error ".$this->db->lasterror();
        }

        if (!$error) {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX.$this->table_element);

            // Generate ref
            if ($this->ref == '(PROV)') {
                $this->ref = $this->getNextNumRef();
                
                $sql = 'UPDATE '.MAIN_DB_PREFIX.$this->table_element;
                $sql .= ' SET ref = "'.$this->db->escape($this->ref).'"';
                $sql .= ' WHERE rowid = '.((int) $this->id);
                
                $resql = $this->db->query($sql);
                if (!$resql) {
                    $error++;
                    $this->errors[] = "Error ".$this->db->lasterror();
                }
            }

            if (!$notrigger) {
                // Call triggers
                $result = $this->call_trigger('INTERNALHTS_CREATE', $user);
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
     * Clone an object into another one
     *
     * @param  	User 	$user      	User that creates
     * @param  	int 	$fromid     Id of object to clone
     * @return 	mixed 				New object created, <0 if KO
     */
    public function createFromClone(User $user, $fromid)
    {
        global $langs, $extrafields;
        $error = 0;

        dol_syslog(__METHOD__, LOG_DEBUG);

        $object = new self($this->db);

        $this->db->begin();

        // Load source object
        $result = $object->fetchCommon($fromid);
        if ($result > 0 && !empty($object->table_element_line)) {
            $object->fetchLines();
        }

        // Reset some properties
        unset($object->id);
        unset($object->fk_user_creat);
        unset($object->import_key);

        // Clear fields
        if (property_exists($object, 'ref')) {
            $object->ref = empty($this->fields['ref']['default']) ? "Copy_Of_".$object->ref : $this->fields['ref']['default'];
        }
        if (property_exists($object, 'label')) {
            $object->label = empty($this->fields['label']['default']) ? $langs->trans("CopyOf")." ".$object->label : $this->fields['label']['default'];
        }
        if (property_exists($object, 'status')) {
            $object->status = self::STATUS_DRAFT;
        }
        if (property_exists($object, 'date_creation')) {
            $object->date_creation = dol_now();
        }
        if (property_exists($object, 'date_modification')) {
            $object->date_modification = null;
        }
        // ...
        // Clear extrafields that are unique
        if (is_array($object->array_options) && count($object->array_options) > 0) {
            $extrafields->fetch_name_optionals_label($this->table_element);
            foreach ($object->array_options as $key => $option) {
                $shortkey = preg_replace('/options_/', '', $key);
                if (!empty($extrafields->attributes[$this->table_element]['unique'][$shortkey])) {
                    //var_dump($key); var_dump($clonedObj->array_options[$key]); exit;
                    unset($object->array_options[$key]);
                }
            }
        }

        // Create clone
        $object->context['createfromclone'] = 'createfromclone';
        $result = $object->createCommon($user);
        if ($result < 0) {
            $error++;
            $this->error = $object->error;
            $this->errors = $object->errors;
        }

        if (!$error) {
            // copy internal contacts
            if ($this->copy_linked_contact($object, $fromid) < 0) {
                $error++;
            }
        }

        if (!$error) {
            // copy external contacts if same company
            if (property_exists($this, 'socid') && $this->socid == $object->socid) {
                if ($this->copy_linked_contact($object, $fromid) < 0) {
                    $error++;
                }
            }
        }

        unset($object->context['createfromclone']);

        // End
        if (!$error) {
            $this->db->commit();
            return $object;
        } else {
            $this->db->rollback();
            return -1;
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
        if ($result > 0 && !empty($this->table_element_line)) {
            $this->fetchLines();
        }
        return $result;
    }

    /**
     * Load object lines in memory from the database
     *
     * @return int         <0 if KO, 0 if not found, >0 if OK
     */
    public function fetchLines()
    {
        $this->lines = array();

        $result = $this->fetchLinesCommon();
        return $result;
    }

    /**
     * Load lines from database
     *
     * @return int <0 if KO, 0 if not found, >0 if OK
     */
    public function fetchLinesCommon()
    {
        $this->lines = array();

        $sql = 'SELECT t.rowid, t.fk_internalhts_doc, t.fk_product, t.fk_hts, t.description, t.sku, t.country_of_origin';
        $sql .= ', t.qty, t.unit_price, t.customs_unit_value, t.total_ht, t.total_customs_value';
        $sql .= ', t.date_creation, t.tms, t.fk_user_creat, t.fk_user_modif, t.import_key, t.rang, t.status';
        $sql .= ' FROM '.MAIN_DB_PREFIX.'internalhts_docline as t';
        $sql .= ' WHERE t.fk_internalhts_doc = '.((int) $this->id);
        $sql .= ' ORDER BY t.rang';

        dol_syslog(get_class($this)."::fetchLines", LOG_DEBUG);
        $resql = $this->db->query($sql);
        if ($resql) {
            $num = $this->db->num_rows($resql);
            $i = 0;
            while ($i < $num) {
                $obj = $this->db->fetch_object($resql);

                $line = new InternalHTSLine($this->db);
                $line->id = $obj->rowid;
                $line->fk_internalhts_doc = $obj->fk_internalhts_doc;
                $line->fk_product = $obj->fk_product;
                $line->fk_hts = $obj->fk_hts;
                $line->description = $obj->description;
                $line->sku = $obj->sku;
                $line->country_of_origin = $obj->country_of_origin;
                $line->qty = $obj->qty;
                $line->unit_price = $obj->unit_price;
                $line->customs_unit_value = $obj->customs_unit_value;
                $line->total_ht = $obj->total_ht;
                $line->total_customs_value = $obj->total_customs_value;
                $line->date_creation = $this->db->jdate($obj->date_creation);
                $line->tms = $this->db->jdate($obj->tms);
                $line->fk_user_creat = $obj->fk_user_creat;
                $line->fk_user_modif = $obj->fk_user_modif;
                $line->import_key = $obj->import_key;
                $line->rang = $obj->rang;
                $line->status = $obj->status;

                $this->lines[$i] = $line;

                $i++;
            }
            $this->db->free($resql);

            return 1;
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
        //return $this->deleteCommon($user, $notrigger, 1);
    }

    /**
     * Validate document
     *
     * @param User $user     User validating
     * @param int $notrigger 1=Does not execute triggers, 0=Execute triggers
     * @return int           <0 if KO, >0 if OK
     */
    public function validate($user, $notrigger = 0)
    {
        global $conf, $langs;

        require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

        $error = 0;

        // Protection
        if ($this->status == self::STATUS_VALIDATED) {
            dol_syslog(get_class($this)."::validate action abandoned: already validated", LOG_WARNING);
            return 0;
        }

        /*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->internalhts->creer))
         || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->internalhts->internalhts_advance->validate))))
         {
         $this->error='NotEnoughPermissions';
         dol_syslog(get_class($this)."::valid ".$this->error, LOG_ERR);
         return -1;
         }*/

        $now = dol_now();

        $this->db->begin();

        // Define new ref
        if (!$error && (preg_match('/^[\(]?PROV/i', $this->ref) || empty($this->ref))) { // empty should not happened, but when it occurs, the test save life
            $num = $this->getNextNumRef();
        } else {
            $num = $this->ref;
        }
        $this->newref = $num;

        if (!empty($num)) {
            // Validate
            $sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element;
            $sql .= " SET ref = '".$this->db->escape($num)."',";
            $sql .= " status = ".self::STATUS_VALIDATED.",";
            $sql .= " date_valid = '".$this->db->idate($now)."',";
            $sql .= " fk_user_valid = ".((int) $user->id);
            $sql .= " WHERE rowid = ".((int) $this->id);

            dol_syslog(get_class($this)."::validate()", LOG_DEBUG);
            $resql = $this->db->query($sql);
            if (!$resql) {
                dol_print_error($this->db);
                $this->error = $this->db->lasterror();
                $error++;
            }

            if (!$error && !$notrigger) {
                // Call trigger
                $result = $this->call_trigger('INTERNALHTS_VALIDATE', $user);
                if ($result < 0) {
                    $error++;
                }
                // End call triggers
            }
        }

        if (!$error) {
            $this->oldref = $this->ref;

            // Rename directory if dir was a temporary ref
            if (preg_match('/^[\(]?PROV/i', $this->ref)) {
                // Now we rename also files into index
                $sql = 'UPDATE '.MAIN_DB_PREFIX."ecm_files set filename = CONCAT('".$this->db->escape($this->newref)."', SUBSTR(filename, ".(strlen($this->ref) + 1).")), filepath = 'internalhts/".$this->db->escape($this->newref)."'";
                $sql .= " WHERE filename LIKE '".$this->db->escape($this->ref)."%' AND filepath = 'internalhts/".$this->db->escape($this->ref)."' and entity = ".$conf->entity;
                $resql = $this->db->query($sql);
                if (!$resql) {
                    $error++; $this->error = $this->db->lasterror();
                }

                // We rename directory ($this->ref = old ref, $num = new ref) in order not to lose the attachments
                $oldref = dol_sanitizeFileName($this->ref);
                $newref = dol_sanitizeFileName($num);
                $dirsource = $conf->internalhts->dir_output.'/'.$oldref;
                $dirdest = $conf->internalhts->dir_output.'/'.$newref;
                if (!$error && file_exists($dirsource)) {
                    dol_syslog(get_class($this)."::validate() rename dir ".$dirsource." into ".$dirdest);

                    if (@rename($dirsource, $dirdest)) {
                        dol_syslog("Rename ok");
                        // Rename docs starting with $oldref with $newref
                        $listoffiles = dol_dir_list($conf->internalhts->dir_output.'/'.$newref, 'files', 1, '^'.preg_quote($oldref, '/'));
                        foreach ($listoffiles as $fileentry) {
                            $dirsource = $fileentry['name'];
                            $dirdest = preg_replace('/^'.preg_quote($oldref, '/').'/', $newref, $dirsource);
                            $dirsource = $fileentry['path'].'/'.$dirsource;
                            $dirdest = $fileentry['path'].'/'.$dirdest;
                            @rename($dirsource, $dirdest);
                        }
                    }
                }
            }
        }

        // Set new ref and current status
        if (!$error) {
            $this->ref = $num;
            $this->status = self::STATUS_VALIDATED;
        }

        if (!$error) {
            $this->db->commit();
            return 1;
        } else {
            $this->db->rollback();
            return -1;
        }
    }

    /**
     * Set draft status
     *
     * @param  User $user      Object user that modify
     * @param  int  $notrigger 1=Does not execute triggers, 0=Execute triggers
     * @return int             <0 if KO, >0 if OK
     */
    public function setDraft($user, $notrigger = 0)
    {
        // Protection
        if ($this->status <= self::STATUS_DRAFT) {
            return 0;
        }

        /*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->internalhts->write))
         || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->internalhts->internalhts_advance->validate))))
         {
         $this->error='Permission denied';
         return -1;
         }*/

        return $this->setStatusCommon($user, self::STATUS_DRAFT, $notrigger, 'INTERNALHTS_UNVALIDATE');
    }

    /**
     * Return the next reference of object
     *
     * @return string Next reference
     */
    public function getNextNumRef()
    {
        global $langs, $conf;
        $langs->load("internalhts@internalhts");

        if (empty($conf->global->INTERNALHTS_ADDON)) {
            $conf->global->INTERNALHTS_ADDON = 'mod_internalhts_standard';
        }

        if (!empty($conf->global->INTERNALHTS_ADDON)) {
            $mybool = false;

            $file = $conf->global->INTERNALHTS_ADDON.".php";
            $classname = $conf->global->INTERNALHTS_ADDON;

            // Include file with class
            $dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);
            foreach ($dirmodels as $reldir) {
                $dir = dol_buildpath($reldir."core/modules/internalhts/", 0);
                if (is_dir($dir)) {
                    $mybool |= @include_once $dir.$file;
                }
            }

            if ($mybool === false) {
                dol_print_error('', "Failed to include file ".$file);
                return '';
            }

            if (class_exists($classname)) {
                $obj = new $classname();
                $numref = $obj->getNextValue($this);

                if ($numref != '' && $numref != '-1') {
                    return $numref;
                } else {
                    $this->error = $obj->error;
                    //dol_print_error($this->db,get_class($this)."::getNextNumRef ".$obj->error);
                    return "";
                }
            } else {
                print $langs->trans("Error")." ".$langs->trans("ClassNotFound").' '.$classname;
                return "";
            }
        } else {
            print $langs->trans("ErrorNumberingModuleNotSetup", $this->element);
            return "";
        }
    }

    /**
     * Recalculate totals
     *
     * @return int <0 if KO, >0 if OK
     */
    public function update_totals()
    {
        $this->total_ht = 0;
        $this->total_customs_value = 0;

        if (!empty($this->lines) && is_array($this->lines)) {
            foreach ($this->lines as $line) {
                $this->total_ht += $line->total_ht;
                $this->total_customs_value += $line->total_customs_value;
            }
        }

        $sql = 'UPDATE '.MAIN_DB_PREFIX.$this->table_element.' SET';
        $sql .= ' total_ht = '.((double) $this->total_ht);
        $sql .= ', total_customs_value = '.((double) $this->total_customs_value);
        $sql .= ' WHERE rowid = '.((int) $this->id);

        dol_syslog(get_class($this)."::update_totals", LOG_DEBUG);
        $resql = $this->db->query($sql);
        if ($resql) {
            return 1;
        } else {
            $this->error = $this->db->lasterror();
            return -1;
        }
    }

    /**
     * Add a line to the document
     *
     * @param  string $description Description
     * @param  string $sku         SKU
     * @param  int    $fk_product  Product ID
     * @param  int    $fk_hts      HTS code ID
     * @param  string $coo         Country of origin
     * @param  double $qty         Quantity
     * @param  double $unit_price  Unit price
     * @param  double $customs_unit_value Customs unit value
     * @return int                 <0 if KO, >0 if OK
     */
    public function addLine($description, $sku, $fk_product = 0, $fk_hts = 0, $coo = '', $qty = 1, $unit_price = 0, $customs_unit_value = 0)
    {
        global $user;

        $line = new InternalHTSLine($this->db);
        $line->fk_internalhts_doc = $this->id;
        $line->description = $description;
        $line->sku = $sku;
        $line->fk_product = $fk_product;
        $line->fk_hts = $fk_hts;
        $line->country_of_origin = $coo;
        $line->qty = $qty;
        $line->unit_price = $unit_price;
        $line->customs_unit_value = $customs_unit_value;
        $line->total_ht = $qty * $unit_price;
        $line->total_customs_value = $qty * $customs_unit_value;
        $line->rang = count($this->lines) + 1;

        $result = $line->create($user);
        if ($result > 0) {
            $this->lines[] = $line;
            $this->update_totals();
            return $result;
        } else {
            $this->error = $line->error;
            $this->errors = $line->errors;
            return -1;
        }
    }

    /**
     * Update a line
     *
     * @param  int    $lineid      Line ID
     * @param  string $description Description
     * @param  string $sku         SKU
     * @param  int    $fk_product  Product ID
     * @param  int    $fk_hts      HTS code ID
     * @param  string $coo         Country of origin
     * @param  double $qty         Quantity
     * @param  double $unit_price  Unit price
     * @param  double $customs_unit_value Customs unit value
     * @return int                 <0 if KO, >0 if OK
     */
    public function updateLine($lineid, $description, $sku, $fk_product = 0, $fk_hts = 0, $coo = '', $qty = 1, $unit_price = 0, $customs_unit_value = 0)
    {
        global $user;

        $line = new InternalHTSLine($this->db);
        $result = $line->fetch($lineid);
        if ($result > 0) {
            $line->description = $description;
            $line->sku = $sku;
            $line->fk_product = $fk_product;
            $line->fk_hts = $fk_hts;
            $line->country_of_origin = $coo;
            $line->qty = $qty;
            $line->unit_price = $unit_price;
            $line->customs_unit_value = $customs_unit_value;
            $line->total_ht = $qty * $unit_price;
            $line->total_customs_value = $qty * $customs_unit_value;

            $result = $line->update($user);
            if ($result > 0) {
                $this->fetchLines();
                $this->update_totals();
                return $result;
            } else {
                $this->error = $line->error;
                $this->errors = $line->errors;
                return -1;
            }
        } else {
            $this->error = $line->error;
            $this->errors = $line->errors;
            return -1;
        }
    }

    /**
     * Delete a line
     *
     * @param  int $lineid Line ID
     * @return int         <0 if KO, >0 if OK
     */
    public function deleteLine($lineid)
    {
        global $user;

        $line = new InternalHTSLine($this->db);
        $result = $line->fetch($lineid);
        if ($result > 0) {
            $result = $line->delete($user);
            if ($result > 0) {
                $this->fetchLines();
                $this->update_totals();
                return $result;
            } else {
                $this->error = $line->error;
                $this->errors = $line->errors;
                return -1;
            }
        } else {
            $this->error = $line->error;
            $this->errors = $line->errors;
            return -1;
        }
    }

    /**
     * Get list of InternalHTS objects
     *
     * @param  int    $socid   Company ID filter
     * @param  string $sortfield Sort field
     * @param  string $sortorder Sort order
     * @param  int    $limit   Number of lines to return
     * @param  int    $offset  Offset for pagination
     * @param  array  $filter  Array of additional filters
     * @param  string $filtermode Filter mode
     * @return array|int       Array of objects or <0 if error
     */
    public function fetchAll($socid = 0, $sortfield = 'id', $sortorder = 'ASC', $limit = 0, $offset = 0, array $filter = array(), $filtermode = 'AND')
    {
        dol_syslog(__METHOD__, LOG_DEBUG);

        $objects = array();

        $sql = 'SELECT ';
        $sql .= $this->getFieldList('t');
        $sql .= ' FROM '.MAIN_DB_PREFIX.$this->table_element.' as t';
        if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 1) {
            $sql .= ' WHERE t.entity IN ('.getEntity($this->table_element).')';
        } else {
            $sql .= ' WHERE 1 = 1';
        }
        if ($socid) {
            $sql .= ' AND t.fk_soc = '.((int) $socid);
        }

        // Manage filter
        $sqlwhere = array();
        if (count($filter) > 0) {
            foreach ($filter as $key => $value) {
                if ($key == 't.rowid') {
                    $sqlwhere[] = $key." = ".((int) $value);
                } elseif (strpos($key, 'date') !== false) {
                    $sqlwhere[] = $key." = '".$this->db->idate($value)."'";
                } elseif ($key == 'customsql') {
                    $sqlwhere[] = $value;
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
            $sql .= " ".$this->db->plimit($limit, $offset);
        }

        $resql = $this->db->query($sql);
        if ($resql) {
            $num = $this->db->num_rows($resql);
            $i = 0;
            while ($i < $num) {
                $obj = $this->db->fetch_object($resql);

                $record = new self($this->db);
                $record->setVarsFromFetchObj($obj);

                $objects[$record->id] = $record;

                $i++;
            }
            $this->db->free($resql);

            return $objects;
        } else {
            $this->errors[] = 'Error '.$this->db->lasterror();
            dol_syslog(__METHOD__.' '.join(',', $this->errors), LOG_ERR);

            return -1;
        }
    }

    /**
     * Print object lines for PDF or other output
     *
     * @param   TCPDF      $pdf               Object PDF
     * @param   string     $action            Current action
     * @param   Societe    $mysoc             Current company
     * @param   Societe    $soc               Third party
     * @param   int        $lineid            Line ID to edit
     * @param   int        $outputlang        Output language
     * @return  void
     */
    public function printObjectLines($action, $mysoc, $soc, $lineid, $outputlang)
    {
        global $conf, $langs, $form;

        $num = count($this->lines);

        for ($i = 0; $i < $num; $i++) {
            // Show product/service info
            print '<tr class="oddeven">';
            
            // Description
            print '<td>';
            if (!empty($this->lines[$i]->fk_product) && $this->lines[$i]->fk_product > 0) {
                print '<a href="'.DOL_URL_ROOT.'/product/card.php?id='.$this->lines[$i]->fk_product.'">';
                print img_object($langs->trans('ShowProduct'), 'product').' ';
                print '</a>';
            }
            print nl2br($this->lines[$i]->description);
            print '</td>';
            
            // SKU
            print '<td>'.$this->lines[$i]->sku.'</td>';
            
            // HTS Code
            print '<td>';
            if ($this->lines[$i]->fk_hts > 0) {
                require_once DOL_DOCUMENT_ROOT.'/custom/internalhts/class/hts.class.php';
                $hts = new HTS($this->db);
                if ($hts->fetch($this->lines[$i]->fk_hts) > 0) {
                    print '<span class="internalhts-hts-code">'.$hts->code.'</span>';
                }
            }
            print '</td>';
            
            // Country of Origin
            print '<td>'.$this->lines[$i]->country_of_origin.'</td>';
            
            // Quantity
            print '<td class="right">'.$this->lines[$i]->qty.'</td>';
            
            // Unit Price
            print '<td class="right">'.price($this->lines[$i]->unit_price).'</td>';
            
            // Customs Unit Value
            print '<td class="right internalhts-customs-value">'.price($this->lines[$i]->customs_unit_value).'</td>';
            
            // Total HT
            print '<td class="right internalhts-line-total">'.price($this->lines[$i]->total_ht).'</td>';
            
            // Action buttons
            print '<td class="right">';
            if ($action != 'editline' && $this->status == self::STATUS_DRAFT) {
                print '<a href="'.$_SERVER["PHP_SELF"].'?id='.$this->id.'&action=editline&lineid='.$this->lines[$i]->id.'">';
                print img_edit();
                print '</a>';
                print ' ';
                print '<a href="'.$_SERVER["PHP_SELF"].'?id='.$this->id.'&action=deleteline&lineid='.$this->lines[$i]->id.'">';
                print img_delete();
                print '</a>';
            }
            print '</td>';
            
            print '</tr>';
        }
    }

    /**
     * Show add line form
     *
     * @param   int     $dateSelector       1=Show also date range selector
     * @param   Societe $seller             Selling company
     * @param   Societe $buyer              Buying company
     * @return  void
     */
    public function formAddObjectLine($dateSelector, $seller, $buyer)
    {
        global $conf, $user, $langs, $form;

        if ($this->status != self::STATUS_DRAFT) {
            return;
        }

        print '<tr class="liste_titre nodrag nodrop">';
        print '<td>'.$langs->trans('Description').'</td>';
        print '<td>'.$langs->trans('SKU').'</td>';
        print '<td>'.$langs->trans('HTSCode').'</td>';
        print '<td>'.$langs->trans('CountryOfOrigin').'</td>';
        print '<td class="right">'.$langs->trans('Qty').'</td>';
        print '<td class="right">'.$langs->trans('UnitPrice').'</td>';
        print '<td class="right">'.$langs->trans('CustomsUnitValue').'</td>';
        print '<td class="right">'.$langs->trans('TotalHT').'</td>';
        print '<td class="right">'.$langs->trans('Add').'</td>';
        print '</tr>';

        print '<tr class="pair nodrag nodrop">';
        
        // Description
        print '<td>';
        print '<textarea name="product_desc" class="flat" rows="2" cols="30"></textarea>';
        print '</td>';
        
        // SKU
        print '<td>';
        print '<input type="text" name="sku" class="flat" size="10">';
        print '</td>';
        
        // HTS Code
        print '<td>';
        print '<select name="fk_hts" class="flat">';
        print '<option value="0">'.$langs->trans('None').'</option>';
        require_once DOL_DOCUMENT_ROOT.'/custom/internalhts/class/hts.class.php';
        $hts = new HTS($this->db);
        $htsList = $hts->fetchAll('', 'country,code', 'ASC', 100);
        if (!empty($htsList)) {
            foreach ($htsList as $htsRecord) {
                print '<option value="'.$htsRecord->id.'">'.$htsRecord->country.' - '.$htsRecord->code.'</option>';
            }
        }
        print '</select>';
        print '</td>';
        
        // Country of Origin
        print '<td>';
        print '<input type="text" name="country_of_origin" class="flat" size="3" maxlength="3">';
        print '</td>';
        
        // Quantity
        print '<td class="right">';
        print '<input type="text" name="qty" class="flat" size="6" value="1">';
        print '</td>';
        
        // Unit Price
        print '<td class="right">';
        print '<input type="text" name="unit_price" class="flat" size="8" value="0">';
        print '</td>';
        
        // Customs Unit Value
        print '<td class="right">';
        print '<input type="text" name="customs_unit_value" class="flat" size="8" value="0">';
        print '</td>';
        
        // Total (calculated automatically)
        print '<td class="right">-</td>';
        
        // Add button
        print '<td class="right">';
        print '<input type="submit" class="button" name="addline" value="'.$langs->trans('Add').'">';
        print '</td>';
        
        print '</tr>';
    }

    /**
     * Action executed by scheduler
     * CAN BE A CRON TASK. In such a case, parameters come from the schedule job setup field 'Parameters'
     * Use this function to set up a cron job to execute this action.
     *
     * @return	int			0 if OK, <>0 if KO (this function is used also by cron so only 0 is OK)
     */
    public function doScheduledJob()
    {
        global $conf, $langs;

        //$conf->global->SYSLOG_FILE = 'DOL_DATA_ROOT/dolibarr_mydedicatedlofile.log';

        $error = 0;
        $this->output = '';
        $this->error='';

        dol_syslog(__METHOD__, LOG_DEBUG);

        $now = dol_now();

        $this->db->begin();

        // ...

        $this->db->commit();

        return $error;
    }
}