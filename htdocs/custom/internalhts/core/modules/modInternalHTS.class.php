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
 * \defgroup internalhts Module InternalHTS
 * \brief InternalHTS module descriptor
 * 
 * Put detailed description here
 */

/**
 * \file htdocs/custom/internalhts/core/modules/modInternalHTS.class.php
 * \ingroup internalhts
 * \brief Description and activation file for module InternalHTS
 */
include_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';

/**
 * Description and activation class for module InternalHTS
 */
class modInternalHTS extends DolibarrModules
{
    /**
     * Constructor. Define names, constants, directories, boxes, permissions
     *
     * @param DoliDB $db Database handler
     */
    public function __construct($db)
    {
        global $langs, $conf;
        $this->db = $db;

        // Module unique id. Must be here and different from any other module
        $this->numero = 500000; // Go on page https://wiki.dolibarr.org/index.php/List_of_modules_id to reserve an id number for your module

        // Key text used to identify module (for permissions, menus, etc...)
        $this->rights_class = 'internalhts';

        // Family can be 'base' (core modules) or 'external' (external modules)
        $this->family = "external";

        // Module position in the menu on left side (0=first level, 1=under first level, ...)
        $this->module_position = '1000';

        // Give a name to your module
        $this->name = preg_replace('/^mod/i', '', get_class($this));

        // Module description, used if translation string 'ModuleInternalHTSDesc' not found
        $this->description = "InternalHTS module for managing internal HTS codes and commercial invoices";

        // Used only if file README.md and README-LL.md not found.
        $this->descriptionlong = "Module to manage internal HTS (Harmonized Tariff Schedule) codes and generate commercial invoices for international shipping";

        $this->editor_name = 'InternalHTS';
        $this->editor_url = '';

        // Possible values for version are: 'development', 'experimental', 'dolibarr', 'dolibarr_deprecated' or a version string like 'x.y.z'
        $this->version = '0.1.0';

        // URL to the file with your last numberversion of this module
        //$this->url_last_version = '';

        // Key used in llx_const table to save module status enabled/disabled
        $this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);

        // Name of image file used for this module.
        $this->picto = 'internalhts@internalhts';

        // Define some features supported by module (triggers, login, substitutions, menus, css, etc...)
        $this->module_parts = array(
            'triggers' => 0,
            'login' => 0,
            'substitutions' => 0,
            'menus' => 1,
            'theme' => 0,
            'tpl' => 0,
            'barcode' => 0,
            'models' => 1,
            'css' => array('/internalhts/css/internalhts.css.php'),
            'js' => array(),
            'hooks' => array(),
            'moduleforexternal' => 0,
        );

        // Data directories to create when module is enabled.
        $this->dirs = array("/internalhts/temp");

        // Config pages. Put here list of php page, stored into internalhts/admin directory, to use to setup module.
        $this->config_page_url = array("setup.php@internalhts");

        // Dependencies
        $this->hidden = false; // A condition to hide module
        $this->depends = array(); // List of module class names as string that must be enabled if this module is enabled. Example: array('always1'=>'modModuleToEnable1','always2'=>'modModuleToEnable2', 'FR1'=>'modModuleToEnableFR'...)
        $this->requiredby = array(); // List of module class names as string to disable if this one is disabled. Example: array('modModuleToDisable1', ...)
        $this->conflictwith = array(); // List of module class names as string this module is in conflict with. Example: array('modModuleToDisable1', ...)

        // The language file dedicated to your module
        $this->langfiles = array("internalhts@internalhts");

        // Prerequisites
        $this->phpmin = array(7, 0); // Minimum version of PHP required by module
        $this->need_dolibarr_version = array(11, 0); // Minimum version of Dolibarr required by module

        // Messages at activation
        $this->warnings_activation = array(); // Warning to show when we activate module. array('always'='text') or array('FR'='textfr','MX'='textmx'...)
        $this->warnings_activation_ext = array(); // Warning to show when we activate an external module. array('always'='text') or array('FR'='textfr','MX'='textmx'...)
        //$this->automatic_activation = array('FR'=>'InternalHTSWasAutomaticallyActivatedBecauseOfYourCountryChoice');
        //$this->always_enabled = true;								// If true, can't be disabled

        // Constants
        $this->const = array(
            1 => array('INTERNALHTS_MYNEWCONST1', 'chaine', 'myvalue', 'This is a constant to add', 1),
            2 => array('INTERNALHTS_MYNEWCONST2', 'chaine', 'myvalue', 'This is another constant to add', 0, 'current', 1)
        );

        // Some keys to add into the overwriting translation tables
        /*$this->overwrite_translation = array(
            'en_US:ParentCompany'=>'Parent company or reseller',
            'fr_FR:ParentCompany'=>'Maison mère ou revendeur'
        )*/

        if (!isModEnabled("internalhts")) {
            $conf->internalhts = new stdClass();
            $conf->internalhts->enabled = 0;
        }

        // Array to add new pages in new tabs
        $this->tabs = array();

        // Dictionaries
        $this->dictionaries = array();

        // Boxes/Widgets
        $this->boxes = array();

        // Cronjobs (Linux cron jobs)
        $this->cronjobs = array();

        // Permissions provided by this module
        $this->rights = array();
        $r = 0;
        // Add here entries to declare new permissions
        // Example:
        $this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1); // Permission id (must not be already used)
        $this->rights[$r][1] = 'Read InternalHTS documents'; // Permission label
        $this->rights[$r][4] = 'internalhts';
        $this->rights[$r][5] = 'read';
        $r++;
        $this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1);
        $this->rights[$r][1] = 'Create/Update InternalHTS documents';
        $this->rights[$r][4] = 'internalhts';
        $this->rights[$r][5] = 'write';
        $r++;
        $this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1);
        $this->rights[$r][1] = 'Delete InternalHTS documents';
        $this->rights[$r][4] = 'internalhts';
        $this->rights[$r][5] = 'delete';
        $r++;

        // Main menu entries to add
        $this->menu = array();
        $r = 0;

        // Add here entries to declare new menus
        $this->menu[$r++] = array(
            'fk_menu' => '', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'type' => 'top', // This is a Top menu entry
            'titre' => 'ModuleInternalHTSName',
            'mainmenu' => 'internalhts',
            'leftmenu' => '',
            'url' => '/internalhts/internalhtsindex.php',
            'langs' => 'internalhts@internalhts', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'position' => 1000 + $r,
            'enabled' => 'isModEnabled("internalhts")', // Define condition to show or hide menu entry. Use 'isModEnabled("internalhts")' if entry must be visible if module is enabled.
            'perms' => '$user->hasRight("internalhts", "read")', // Use 'perms'=>'$user->hasRight("internalhts","read")' if you want your menu with a permission rules
            'target' => '',
            'user' => 2, // 0=Menu for internal users, 1=external users, 2=both
        );

        $this->menu[$r++] = array(
            'fk_menu' => 'fk_mainmenu=internalhts',
            'type' => 'left',
            'titre' => 'Internal Invoices',
            'mainmenu' => 'internalhts',
            'leftmenu' => 'internalhts_docs',
            'url' => '/internalhts/list.php',
            'langs' => 'internalhts@internalhts',
            'position' => 1000 + $r,
            'enabled' => 'isModEnabled("internalhts")',
            'perms' => '$user->hasRight("internalhts", "read")',
            'target' => '',
            'user' => 2,
        );

        $this->menu[$r++] = array(
            'fk_menu' => 'fk_mainmenu=internalhts,fk_leftmenu=internalhts_docs',
            'type' => 'left',
            'titre' => 'New Internal Invoice',
            'mainmenu' => 'internalhts',
            'leftmenu' => 'internalhts_docs_new',
            'url' => '/internalhts/card.php?action=create',
            'langs' => 'internalhts@internalhts',
            'position' => 1000 + $r,
            'enabled' => 'isModEnabled("internalhts")',
            'perms' => '$user->hasRight("internalhts", "write")',
            'target' => '',
            'user' => 2,
        );

        $this->menu[$r++] = array(
            'fk_menu' => 'fk_mainmenu=internalhts',
            'type' => 'left',
            'titre' => 'HTS Mapping',
            'mainmenu' => 'internalhts',
            'leftmenu' => 'internalhts_hts',
            'url' => '/internalhts/admin/hts_import.php',
            'langs' => 'internalhts@internalhts',
            'position' => 1000 + $r,
            'enabled' => 'isModEnabled("internalhts")',
            'perms' => '$user->hasRight("internalhts", "write")',
            'target' => '',
            'user' => 2,
        );
    }

    /**
     * Function called when module is enabled.
     * The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
     * It also creates data directories
     *
     * @param string $options Options when enabling module ('', 'noboxes')
     * @return int 1 if OK, 0 if KO
     */
    public function init($options = '')
    {
        global $conf, $langs;

        $result = $this->_load_tables('/internalhts/sql/');
        if ($result < 0) {
            return -1; // Do not activate module if error 'not allowed' returned when loading module SQL queries (the _load_table run sql with run_sql with the error allowed parameter set to 'default')
        }

        // Create extrafields during init
        //include_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
        //$extrafields = new ExtraFields($this->db);
        //$result1=$extrafields->addExtraField('internalhts_myattr1', "New Attr 1 label", 'boolean', 1,  3, 'thirdparty',   0, 0, '', '', 1, '', 0, 0, '', '', 'internalhts@internalhts', 'isModEnabled("internalhts")');
        //$result2=$extrafields->addExtraField('internalhts_myattr2', "New Attr 2 label", 'varchar', 1, 10, 'project',      0, 0, '', '', 1, '', 0, 0, '', '', 'internalhts@internalhts', 'isModEnabled("internalhts")');
        //$result3=$extrafields->addExtraField('internalhts_myattr3', "New Attr 3 label", 'varchar', 1, 10, 'bank_account', 0, 0, '', '', 1, '', 0, 0, '', '', 'internalhts@internalhts', 'isModEnabled("internalhts")');
        //$result4=$extrafields->addExtraField('internalhts_myattr4', "New Attr 4 label", 'select',  1,  3, 'thirdparty',   0, 1, '', array('options'=>array('code1'=>'Val1','code2'=>'Val2','code3'=>'Val3')), 1,'', 0, 0, '', '', 'internalhts@internalhts', 'isModEnabled("internalhts")');
        //$result5=$extrafields->addExtraField('internalhts_myattr5', "New Attr 5 label", 'text',    1, 10, 'user',         0, 0, '', '', 1, '', 0, 0, '', '', 'internalhts@internalhts', 'isModEnabled("internalhts")');

        // Permissions
        $this->remove($options);

        $sql = array();

        return $this->_init($sql, $options);
    }

    /**
     * Function called when module is disabled.
     * Remove from database constants, boxes and permissions from Dolibarr database.
     * Data directories are not deleted
     *
     * @param string $options Options when enabling module ('', 'noboxes')
     * @return int 1 if OK, 0 if KO
     */
    public function remove($options = '')
    {
        $sql = array();
        return $this->_remove($sql, $options);
    }
}