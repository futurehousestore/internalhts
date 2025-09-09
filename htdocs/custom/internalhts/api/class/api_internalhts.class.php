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

use Luracast\Restler\RestException;

require_once DOL_DOCUMENT_ROOT.'/api/class/api.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/internalhts/class/internalhts.class.php';

/**
 * API class for InternalHTS objects
 *
 * @access protected
 * @class  DolibarrApiAccess {@requires user,external}
 */
class InternalHTSApi extends DolibarrApi
{
    /**
     * @var array   $FIELDS     Mandatory fields, checked when create and update object
     */
    public static $FIELDS = array(
        'ref',
        'label'
    );

    /**
     * Constructor
     */
    public function __construct()
    {
        global $db, $conf;
        $this->db = $db;
    }

    /**
     * Get properties of a InternalHTS object
     *
     * Return an array with InternalHTS informations
     *
     * @param   int         $id         ID of InternalHTS
     * @return  array|mixed             Data without useless information
     *
     * @throws  RestException
     */
    public function get($id)
    {
        if (!DolibarrApiAccess::$user->hasRight('internalhts', 'read')) {
            throw new RestException(401);
        }

        $result = $this->_fetch($id);
        if (!$result) {
            throw new RestException(404, 'InternalHTS not found');
        }

        if (!DolibarrApi::_checkAccessToResource('internalhts', $result->id)) {
            throw new RestException(401, 'Access not allowed for login '.DolibarrApiAccess::$user->login);
        }

        return $this->_cleanObjectDatas($result);
    }

    /**
     * List InternalHTS documents
     *
     * Get a list of InternalHTS documents
     *
     * @param string    $sortfield  Sort field
     * @param string    $sortorder  Sort order
     * @param int       $limit      Limit for list
     * @param int       $page       Page number
     * @param string    $sqlfilters Other criteria to filter answers separated by a comma. Syntax example "(t.ref:like:'SO-%') and (t.date_creation:<:'20160101')"
     * @return  array               Array of InternalHTS objects
     *
     * @throws RestException
     */
    public function index($sortfield = "t.rowid", $sortorder = 'ASC', $limit = 100, $page = 0, $sqlfilters = '')
    {
        global $db, $conf;

        if (!DolibarrApiAccess::$user->hasRight('internalhts', 'read')) {
            throw new RestException(401);
        }

        $obj_ret = array();

        // case of external user, $societe param is ignored and replaced by user's socid
        $socid = DolibarrApiAccess::$user->socid ? DolibarrApiAccess::$user->socid : '';

        $sql = "SELECT t.rowid";
        if (isset($extrafields->attributes[$object->table_element]['label']) && is_array($extrafields->attributes[$object->table_element]['label']) && count($extrafields->attributes[$object->table_element]['label'])) {
            $sql .= ", ef.rowid as efrowid";
        }
        $sql .= " FROM ".MAIN_DB_PREFIX."internalhts_doc as t";
        if (isset($extrafields->attributes[$object->table_element]['label']) && is_array($extrafields->attributes[$object->table_element]['label']) && count($extrafields->attributes[$object->table_element]['label'])) {
            $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."internalhts_doc_extrafields as ef on (t.rowid = ef.fk_object)";
        }
        $sql .= " WHERE 1 = 1";
        if ($socid) {
            $sql .= " AND t.fk_soc = ".((int) $socid);
        }
        $sql .= " AND t.entity IN (".getEntity('internalhts').")";

        // Add sql filters
        if ($sqlfilters) {
            $errormessage = '';
            $sql .= forgeSQLFromUniversalSearchCriteria($sqlfilters, $errormessage);
            if ($errormessage) {
                throw new RestException(400, 'Error when validating parameter sqlfilters -> '.$errormessage);
            }
        }

        $sql .= $this->db->order($sortfield, $sortorder);
        if ($limit) {
            if ($page < 0) {
                $page = 0;
            }
            $offset = $limit * $page;

            $sql .= $this->db->plimit($limit + 1, $offset);
        }

        dol_syslog("API Rest request");
        $result = $this->db->query($sql);

        if ($result) {
            $num = $this->db->num_rows($result);
            $min = min($num, ($limit <= 0 ? $num : $limit));
            $i = 0;
            while ($i < $min) {
                $obj = $this->db->fetch_object($result);
                $internalhts_static = new InternalHTS($this->db);
                if ($internalhts_static->fetch($obj->rowid)) {
                    $obj_ret[] = $this->_cleanObjectDatas($internalhts_static);
                }
                $i++;
            }
        } else {
            throw new RestException(503, 'Error when retrieve InternalHTS list : '.$this->db->lasterror());
        }
        if (!count($obj_ret)) {
            throw new RestException(404, 'No InternalHTS found');
        }

        return $obj_ret;
    }

    /**
     * Create InternalHTS object
     *
     * @param   array   $request_data   Request data
     * @return  int                     ID of InternalHTS
     */
    public function post($request_data = null)
    {
        if (!DolibarrApiAccess::$user->hasRight('internalhts', 'write')) {
            throw new RestException(401);
        }

        // Check mandatory fields
        $result = $this->_validate($request_data);

        foreach ($request_data as $field => $value) {
            $this->internalhts->$field = $value;
        }
        $this->internalhts->date_creation = dol_now();

        if ($this->internalhts->create(DolibarrApiAccess::$user) < 0) {
            throw new RestException(500, "Error creating InternalHTS", array_merge(array($this->internalhts->error), $this->internalhts->errors));
        }

        return $this->internalhts->id;
    }

    /**
     * Update InternalHTS
     *
     * @param int   $id             Id of InternalHTS to update
     * @param array $request_data   Datas
     * @return int
     */
    public function put($id, $request_data = null)
    {
        if (!DolibarrApiAccess::$user->hasRight('internalhts', 'write')) {
            throw new RestException(401);
        }

        $result = $this->_fetch($id);
        if (!$result) {
            throw new RestException(404, 'InternalHTS not found');
        }

        if (!DolibarrApi::_checkAccessToResource('internalhts', $this->internalhts->id)) {
            throw new RestException(401, 'Access not allowed for login '.DolibarrApiAccess::$user->login);
        }

        foreach ($request_data as $field => $value) {
            if ($field == 'id') {
                continue;
            }
            $this->internalhts->$field = $value;
        }

        if ($this->internalhts->update(DolibarrApiAccess::$user) > 0) {
            return $this->get($id);
        } else {
            throw new RestException(500, $this->internalhts->error);
        }
    }

    /**
     * Delete InternalHTS
     *
     * @param   int     $id   InternalHTS ID
     * @return  array
     */
    public function delete($id)
    {
        if (!DolibarrApiAccess::$user->hasRight('internalhts', 'delete')) {
            throw new RestException(401);
        }
        $result = $this->_fetch($id);
        if (!$result) {
            throw new RestException(404, 'InternalHTS not found');
        }

        if (!DolibarrApi::_checkAccessToResource('internalhts', $this->internalhts->id)) {
            throw new RestException(401, 'Access not allowed for login '.DolibarrApiAccess::$user->login);
        }

        if (!$this->internalhts->delete(DolibarrApiAccess::$user)) {
            throw new RestException(500, 'Error when delete InternalHTS : '.$this->internalhts->error);
        }

        return array(
            'success' => array(
                'code' => 200,
                'message' => 'InternalHTS deleted'
            )
        );
    }

    /**
     * Validate InternalHTS
     *
     * @param   int $id             InternalHTS ID
     * @param   int $notrigger      1=Does not execute triggers, 0= execute triggers
     * @return  array
     * @throws RestException
     */
    public function validate($id, $notrigger = 0)
    {
        if (!DolibarrApiAccess::$user->hasRight('internalhts', 'write')) {
            throw new RestException(401);
        }
        $result = $this->_fetch($id);
        if (!$result) {
            throw new RestException(404, 'InternalHTS not found');
        }

        if (!DolibarrApi::_checkAccessToResource('internalhts', $this->internalhts->id)) {
            throw new RestException(401, 'Access not allowed for login '.DolibarrApiAccess::$user->login);
        }

        $result = $this->internalhts->validate(DolibarrApiAccess::$user, $notrigger);
        if ($result == 0) {
            throw new RestException(304, 'Error nothing done. May be object is already validated');
        }
        if ($result < 0) {
            throw new RestException(500, 'Error when validating InternalHTS: '.$this->internalhts->error);
        }

        return array(
            'success' => array(
                'code' => 200,
                'message' => 'InternalHTS validated'
            )
        );
    }

    /**
     * Get lines of a InternalHTS
     *
     * @param int   $id             Id of InternalHTS
     * @return int
     */
    public function getLines($id)
    {
        if (!DolibarrApiAccess::$user->hasRight('internalhts', 'read')) {
            throw new RestException(401);
        }

        $result = $this->_fetch($id);
        if (!$result) {
            throw new RestException(404, 'InternalHTS not found');
        }

        if (!DolibarrApi::_checkAccessToResource('internalhts', $this->internalhts->id)) {
            throw new RestException(401, 'Access not allowed for login '.DolibarrApiAccess::$user->login);
        }

        $this->internalhts->getLinesArray();
        $result = array();
        foreach ($this->internalhts->lines as $line) {
            array_push($result, $this->_cleanObjectDatas($line));
        }
        return $result;
    }

    /**
     * Fetch properties of a InternalHTS object
     *
     * @param int   $id     ID of InternalHTS
     * @return mixed
     */
    private function _fetch($id)
    {
        $this->internalhts = new InternalHTS($this->db);
        return $this->internalhts->fetch($id);
    }

    /**
     * Validate fields before create or update object
     *
     * @param   array           $data   Array with data to verify
     * @return  array
     * @throws  RestException
     */
    private function _validate($data)
    {
        $internalhts = array();
        foreach (InternalHTSApi::$FIELDS as $field) {
            if (!isset($data[$field])) {
                throw new RestException(400, "$field field missing");
            }
            $internalhts[$field] = $data[$field];
        }
        return $internalhts;
    }

    // phpcs:disable PEAR.NamingConventions.ValidFunctionName.PublicUnderscore
    /**
     * Clean sensible object datas
     *
     * @param   Object  $object     Object to clean
     * @return  Object              Object with cleaned properties
     */
    protected function _cleanObjectDatas($object)
    {
        $object = parent::_cleanObjectDatas($object);

        // Remove fields not needed for API
        unset($object->rowid);
        unset($object->canvas);

        /*unset($object->name);
        unset($object->lastname);
        unset($object->firstname);
        unset($object->civility_id);
        unset($object->statut);
        unset($object->state);
        unset($object->state_id);
        unset($object->state_code);
        unset($object->region);
        unset($object->region_code);
        unset($object->country);
        unset($object->country_id);
        unset($object->country_code);
        unset($object->barcode_type_code);
        unset($object->barcode_type_label);
        unset($object->barcode_type_coder);
        unset($object->total_ht);
        unset($object->total_tva);
        unset($object->total_localtax1);
        unset($object->total_localtax2);
        unset($object->total_ttc);
        unset($object->fk_account);
        unset($object->comments);
        unset($object->note);
        unset($object->enabled);
        unset($object->archived);
        unset($object->print);
        unset($object->entity);
        unset($object->import_key);
        unset($object->array_options);
        unset($object->array_languages);
        unset($object->contacts_ids);
        unset($object->linkedObjectsIds);
        unset($object->linkedObjects);
        unset($object->clicktodial_url);
        unset($object->clicktodial_loaded);
        unset($object->fk_multicurrency);
        unset($object->multicurrency_code);
        unset($object->multicurrency_tx);
        unset($object->multicurrency_total_ht);
        unset($object->multicurrency_total_tva);
        unset($object->multicurrency_total_ttc);
        */

        return $object;
    }
}