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
 * \file class/hts.class.php
 * \ingroup internalhts
 * \brief This file is a CRUD class file for HTS (Create/Read/Update/Delete)
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';

/**
 * Class for HTS
 */
class HTS extends CommonObject
{
    /**
     * @var string ID to identify managed object.
     */
    public $element = 'hts';

    /**
     * @var string Name of table without prefix where object is stored. This is also the key used for extrafields management.
     */
    public $table_element = 'internalhts_hts';

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
     * @var string String with name of icon for hts. Must be the part after the 'object_' into object_hts.png
     */
    public $picto = 'internalhts@internalhts';

    /**
     * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
     */
    public $fields=array(
        'rowid' => array('type'=>'integer', 'label'=>'TechnicalID', 'enabled'=>'1', 'position'=>1, 'notnull'=>1, 'visible'=>0, 'noteditable'=>'1', 'index'=>1, 'css'=>'left', 'comment'=>"Id"),
        'country' => array('type'=>'varchar(3)', 'label'=>'Country', 'enabled'=>'1', 'position'=>20, 'notnull'=>1, 'visible'=>1, 'searchall'=>1, 'css'=>'minwidth100', 'help'=>"3-letter country code"),
        'code' => array('type'=>'varchar(20)', 'label'=>'HTSCode', 'enabled'=>'1', 'position'=>30, 'notnull'=>1, 'visible'=>1, 'searchall'=>1, 'css'=>'minwidth200', 'help'=>"HTS tariff code"),
        'description' => array('type'=>'text', 'label'=>'Description', 'enabled'=>'1', 'position'=>40, 'notnull'=>0, 'visible'=>1, 'searchall'=>1),
        'date_creation' => array('type'=>'datetime', 'label'=>'DateCreation', 'enabled'=>'1', 'position'=>500, 'notnull'=>1, 'visible'=>0),
        'tms' => array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>'1', 'position'=>501, 'notnull'=>0, 'visible'=>0),
        'fk_user_creat' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserAuthor', 'enabled'=>'1', 'position'=>510, 'notnull'=>1, 'visible'=>0, 'foreignkey'=>'user.rowid'),
        'fk_user_modif' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserModif', 'enabled'=>'1', 'position'=>511, 'notnull'=>-1, 'visible'=>0),
        'last_main_doc' => array('type'=>'varchar(255)', 'label'=>'LastMainDoc', 'enabled'=>'1', 'position'=>600, 'notnull'=>0, 'visible'=>0),
        'import_key' => array('type'=>'varchar(14)', 'label'=>'ImportId', 'enabled'=>'1', 'position'=>1000, 'notnull'=>-1, 'visible'=>0),
        'model_pdf' => array('type'=>'varchar(255)', 'label'=>'Model pdf', 'enabled'=>'1', 'position'=>1010, 'notnull'=>-1, 'visible'=>0),
        'status' => array('type'=>'smallint', 'label'=>'Status', 'enabled'=>'1', 'position'=>1000, 'notnull'=>1, 'visible'=>1, 'index'=>1, 'default'=>'1', 'arrayofkeyval'=>array('0'=>'Disabled', '1'=>'Active')),
    );

    public $rowid;
    public $country;
    public $code;
    public $description;
    public $date_creation;
    public $tms;
    public $fk_user_creat;
    public $fk_user_modif;
    public $last_main_doc;
    public $import_key;
    public $model_pdf;
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
        if (isset($this->country)) {
            $this->country = strtoupper(trim($this->country));
        }
        if (isset($this->code)) {
            $this->code = trim($this->code);
        }
        if (isset($this->description)) {
            $this->description = trim($this->description);
        }

        // Check parameters
        if (empty($this->country) || strlen($this->country) != 3) {
            $this->errors[] = 'Country must be a 3-letter code';
            return -1;
        }
        if (empty($this->code)) {
            $this->errors[] = 'HTS code is required';
            return -1;
        }

        // Insert request
        $sql = 'INSERT INTO '.MAIN_DB_PREFIX.$this->table_element.'(';
        $sql .= 'country,';
        $sql .= 'code,';
        $sql .= 'description,';
        $sql .= 'date_creation,';
        $sql .= 'fk_user_creat,';
        $sql .= 'status';
        $sql .= ') VALUES (';
        $sql .= ' "'.$this->db->escape($this->country).'",';
        $sql .= ' "'.$this->db->escape($this->code).'",';
        $sql .= ' '.(!isset($this->description) ? 'NULL' : "'".$this->db->escape($this->description)."'").',';
        $sql .= ' '.(!isset($this->date_creation) || dol_strlen($this->date_creation) == 0 ? 'NULL' : "'".$this->db->idate($this->date_creation)."'").',';
        $sql .= ' '.((int) $user->id).',';
        $sql .= ' '.((int) ($this->status ?: 1));
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
                $result = $this->call_trigger('HTS_CREATE', $user);
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
     * Fetch HTS by country and code
     *
     * @param string $country Country code (3 letters)
     * @param string $code    HTS code
     * @return int            <0 if KO, 0 if not found, >0 if OK
     */
    public function fetchByCountryCode($country, $code)
    {
        $sql = 'SELECT rowid FROM '.MAIN_DB_PREFIX.$this->table_element;
        $sql .= ' WHERE country = "'.$this->db->escape(strtoupper($country)).'"';
        $sql .= ' AND code = "'.$this->db->escape($code).'"';
        $sql .= ' AND status = 1';

        dol_syslog(get_class($this)."::fetchByCountryCode", LOG_DEBUG);
        $resql = $this->db->query($sql);
        if ($resql) {
            if ($this->db->num_rows($resql)) {
                $obj = $this->db->fetch_object($resql);
                return $this->fetch($obj->rowid);
            } else {
                return 0; // Not found
            }
        } else {
            $this->error = $this->db->lasterror();
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
     * Upsert HTS record (insert or update)
     *
     * @param User   $user        User
     * @param string $country     Country code
     * @param string $code        HTS code
     * @param string $description Description
     * @return int                <0 if KO, ID if OK
     */
    public function upsert(User $user, $country, $code, $description = '')
    {
        $existing = $this->fetchByCountryCode($country, $code);
        
        if ($existing > 0) {
            // Update existing
            $this->description = $description;
            $result = $this->update($user);
            return ($result > 0) ? $this->id : $result;
        } elseif ($existing == 0) {
            // Create new
            $this->country = strtoupper($country);
            $this->code = $code;
            $this->description = $description;
            $this->date_creation = dol_now();
            $this->status = 1;
            return $this->create($user);
        } else {
            // Error
            return $existing;
        }
    }

    /**
     * Get list of HTS objects
     *
     * @param  string $country   Country filter
     * @param  string $sortfield Sort field
     * @param  string $sortorder Sort order
     * @param  int    $limit     Number of lines to return
     * @param  int    $offset    Offset for pagination
     * @param  array  $filter    Array of additional filters
     * @param  string $filtermode Filter mode
     * @return array|int         Array of objects or <0 if error
     */
    public function fetchAll($country = '', $sortfield = 'country,code', $sortorder = 'ASC', $limit = 0, $offset = 0, array $filter = array(), $filtermode = 'AND')
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
        
        if ($country) {
            $sql .= ' AND t.country = "'.$this->db->escape(strtoupper($country)).'"';
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
     * Import HTS codes from CSV data
     *
     * @param User   $user     User
     * @param array  $csvdata  Array of CSV rows (each row is array of columns)
     * @param bool   $dryrun   If true, don't actually save to database
     * @return array           Array with 'success'=>count, 'errors'=>array of errors, 'imported'=>array of imported records
     */
    public function importFromCSV(User $user, $csvdata, $dryrun = false)
    {
        $result = array(
            'success' => 0,
            'errors' => array(),
            'imported' => array()
        );

        if (!is_array($csvdata) || empty($csvdata)) {
            $result['errors'][] = 'No CSV data provided';
            return $result;
        }

        // Expected CSV format: country, code, description
        foreach ($csvdata as $rownum => $row) {
            if (!is_array($row) || count($row) < 2) {
                $result['errors'][] = "Row ".($rownum + 1).": Invalid format, need at least country and code";
                continue;
            }

            $country = strtoupper(trim($row[0]));
            $code = trim($row[1]);
            $description = isset($row[2]) ? trim($row[2]) : '';

            // Validate
            if (empty($country) || strlen($country) != 3) {
                $result['errors'][] = "Row ".($rownum + 1).": Invalid country code '$country'";
                continue;
            }
            if (empty($code)) {
                $result['errors'][] = "Row ".($rownum + 1).": Empty HTS code";
                continue;
            }

            if (!$dryrun) {
                $hts = new self($this->db);
                $insertResult = $hts->upsert($user, $country, $code, $description);
                if ($insertResult > 0) {
                    $result['success']++;
                    $result['imported'][] = array(
                        'id' => $insertResult,
                        'country' => $country,
                        'code' => $code,
                        'description' => $description
                    );
                } else {
                    $result['errors'][] = "Row ".($rownum + 1).": Failed to save - ".$hts->error;
                }
            } else {
                // Dry run - just validate and collect
                $result['imported'][] = array(
                    'country' => $country,
                    'code' => $code,
                    'description' => $description
                );
                $result['success']++;
            }
        }

        return $result;
    }
}