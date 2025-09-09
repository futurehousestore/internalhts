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
 * \file       admin/setup.php
 * \ingroup    internalhts
 * \brief      InternalHTS setup page.
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
    $res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
    $i--; $j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
    $res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
    $res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../../main.inc.php")) {
    $res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
    $res = @include "../../../main.inc.php";
}
if (!$res && file_exists("../../../../main.inc.php")) {
    $res = @include "../../../../main.inc.php";
}
if (!$res) {
    die("Include of main fails");
}

global $langs, $user;

dol_include_once('/internalhts/lib/internalhts.lib.php');

// Translations
$langs->loadLangs(array("admin", "internalhts@internalhts"));

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$hookmanager->initHooks(array('internalhtssetup', 'globalsetup'));

// Access control
if (!$user->admin) {
    accessforbidden();
}

// Parameters
$action = GETPOST('action', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');

$value = GETPOST('value', 'alpha');
$label = GETPOST('label', 'alpha');
$scandir = GETPOST('scan_dir', 'alpha');
$type = 'internalhts';

$error = 0;
$setupnotempty = 0;

// Set this to 1 to use the factory to manage constants. Warning, the generated module will be compatible with version v15+ only
$useFormSetup = 1;

if (!class_exists('FormSetup')) {
    require_once DOL_DOCUMENT_ROOT.'/core/class/html.formsetup.class.php';
}
$formSetup = new FormSetup($db);

// Setup conf INTERNALHTS_* (this part can be generated automatically)
$item = array(
    array(
        'var'=>'INTERNALHTS_ADDON',
        'label'=>'ModuleNumberingModule',
        'type'=>'yesno',
        'default'=>'mod_internalhts_standard',
        'help'=>'',
        'enabled'=>1
    ),
    array(
        'var'=>'INTERNALHTS_DEFAULT_INCOTERM',
        'label'=>'DefaultIncoterm',
        'type'=>'string',
        'default'=>'EXW',
        'help'=>'',
        'enabled'=>1
    ),
    array(
        'var'=>'INTERNALHTS_DEFAULT_VALUE_SOURCE',
        'label'=>'DefaultValueSource',
        'type'=>'select',
        'values'=>array('retail'=>'Retail Price', 'wholesale'=>'Wholesale Price', 'customs'=>'Customs Value'),
        'default'=>'retail',
        'help'=>'',
        'enabled'=>1
    ),
    array(
        'var'=>'INTERNALHTS_DEFAULT_APPORTIONMENT_MODE',
        'label'=>'DefaultApportionmentMode',
        'type'=>'select',
        'values'=>array('weight'=>'By Weight', 'value'=>'By Value', 'quantity'=>'By Quantity'),
        'default'=>'value',
        'help'=>'',
        'enabled'=>1
    ),
);

$setupnotempty += count($item);

$formSetup->items = $item;

/*
 * Actions
 */

// For retrocompatibility Dolibarr < 15.0
if (versioncompare(explode('.', DOL_VERSION), array(15)) < 0 && $action == 'update' && !empty($user->admin)) {
    $db->begin();

    $res = 0;

    if (!$res) {
        $error++;
        setEventMessages($db->lasterror(), null, 'errors');
    }

    if (!$error) {
        $db->commit();
        setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    } else {
        $db->rollback();
    }
}

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) {
    setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

if ($action == 'updateMask') {
    $maskconst = GETPOST('maskconst', 'aZ09');
    $maskorder = GETPOST('maskorder', 'int');

    if ($maskconst && preg_match('/_MASK$/', $maskconst)) {
        $res = dolibarr_set_const($db, $maskconst, $maskorder, 'chaine', 0, '', $conf->entity);
        if (!($res > 0)) {
            $error++;
        }

        if (!$error) {
            setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
        } else {
            setEventMessages($langs->trans("Error"), null, 'errors');
        }
    } else {
        setEventMessages($langs->trans("ErrorWrongValue"), null, 'errors');
    }
}

/*
 * View
 */

$form = new Form($db);

$help_url = '';
$page_name = "InternalHTSSetup";

llxHeader('', $langs->trans($page_name), $help_url);

// Subheader
$linkback = '<a href="'.($backtopage ? $backtopage : DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1').'">'.$langs->trans("BackToModuleList").'</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'title_setup');

// Configuration header
$head = internalhtsAdminPrepareHead();
print dol_get_fiche_head($head, 'settings', $langs->trans($page_name), -1, "internalhts@internalhts");

// Setup page goes here
print info_admin($langs->trans("InternalHTSSetupPage"));
print '<br>';

if ($action == 'edit') {
    print $formSetup->generateOutput(true);
    print '<br>';
} elseif (!empty($formSetup->items)) {
    print $formSetup->generateOutput();
    print '<div class="tabsAction">';
    print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?action=edit&token='.newToken().'">'.$langs->trans("Modify").'</a>';
    print '</div>';
} else {
    print '<br>'.$langs->trans("NothingToSetup");
}

$moduledir = 'internalhts';
$myTmpObjects = array();
$myTmpObjects['InternalHTS'] = array('includerefgeneration'=>1, 'includedocgeneration'=>0);

foreach ($myTmpObjects as $myTmpObjectKey => $myTmpObjectArray) {
    if ($myTmpObjectArray['includerefgeneration']) {
        /*
         * Orders Numbering model
         */
        $setupnotempty++;

        print load_fiche_titre($langs->trans("NumberingModules", $myTmpObjectKey), '', '');

        print '<table class="noborder centpercent">';
        print '<tr class="liste_titre">';
        print '<td>'.$langs->trans("Name").'</td>';
        print '<td>'.$langs->trans("Description").'</td>';
        print '<td class="nowrap">'.$langs->trans("Example").'</td>';
        print '<td class="center" width="60">'.$langs->trans("Status").'</td>';
        print '<td class="center" width="16">'.$langs->trans("ShortInfo").'</td>';
        print '</tr>'."\n";

        clearstatcache();

        foreach (array('/core/modules/'.$moduledir.'/') as $reldir) {
            $dir = dol_buildpath($reldir, 0);
            if (is_dir($dir)) {
                $handle = opendir($dir);
                if (is_resource($handle)) {
                    while (($file = readdir($handle)) !== false) {
                        if (preg_match('/^(mod_.*)\.php$/i', $file, $reg)) {
                            $file = $reg[1];
                            $classname = $file;

                            require_once $dir.'/'.$file.'.php';

                            $module = new $file($db);
                            '@phan-var-force ModeleNumRefInternalHTS $module';

                            // Show modules according to features level
                            if ($module->version == 'development' && getDolGlobalInt('MAIN_FEATURES_LEVEL') < 2) {
                                continue;
                            }
                            if ($module->version == 'experimental' && getDolGlobalInt('MAIN_FEATURES_LEVEL') < 1) {
                                continue;
                            }

                            if ($module->isEnabled()) {
                                dol_include_once('/'.$moduledir.'/class/'.strtolower($myTmpObjectKey).'.class.php');

                                print '<tr class="oddeven"><td width="100">';
                                print (empty($module->name) ? $classname : $module->name);
                                print "</td><td>\n";
                                print $module->info($langs);
                                print '</td>';

                                // Show example of numbering model
                                print '<td class="nowrap">';
                                $tmp = $module->getExample();
                                if (preg_match('/^Error/', $tmp)) {
                                    $langs->load("errors");
                                    print '<div class="error">'.$langs->trans($tmp).'</div>';
                                } elseif ($tmp == 'NotConfigured') {
                                    print $langs->trans($tmp);
                                } else {
                                    print $tmp;
                                }
                                print '</td>'."\n";

                                print '<td class="center">';
                                $constforvar = 'INTERNALHTS_'.strtoupper($myTmpObjectKey).'_ADDON';
                                if (getDolGlobalString($constforvar) == $file) {
                                    print img_picto($langs->trans("Activated"), 'switch_on');
                                } else {
                                    print '<a href="'.$_SERVER["PHP_SELF"].'?action=setmod&token='.newToken().'&object='.strtolower($myTmpObjectKey).'&value='.urlencode($file).'">';
                                    print img_picto($langs->trans("Disabled"), 'switch_off');
                                    print '</a>';
                                }
                                print '</td>';

                                $mytmpinstance = new $myTmpObjectKey($db);
                                $mytmpinstance->initAsSpecimen();

                                // Info
                                $htmltooltip = '';
                                $htmltooltip .= ''.$langs->trans("Version").': <b>'.$module->getVersion().'</b><br>';

                                $nextval = $module->getNextValue($mytmpinstance);
                                if ("$nextval" != $langs->trans("NotAvailable")) {  // Keep " on nextval
                                    $htmltooltip .= ''.$langs->trans("NextValue").': ';
                                    if ($nextval) {
                                        if (preg_match('/^Error/', $nextval) || $nextval == 'NotConfigured') {
                                            $nextval = $langs->trans($nextval);
                                        }
                                        $htmltooltip .= $nextval.'<br>';
                                    } else {
                                        $htmltooltip .= $langs->trans($module->error).'<br>';
                                    }
                                }

                                print '<td class="center">';
                                print $form->textwithpicto('', $htmltooltip, 1, 0);
                                print '</td>';

                                print "</tr>\n";
                            }
                        }
                    }
                    closedir($handle);
                }
            }
        }
        print "</table><br>\n";
    }

    if ($myTmpObjectArray['includedocgeneration']) {
        /*
         * Document templates generators
         */
        $setupnotempty++;
        $type = strtolower($myTmpObjectKey);

        print load_fiche_titre($langs->trans("DocumentModules", $myTmpObjectKey), '', '');

        // Load array def of templates
        $def = array();
        $sql = "SELECT nom";
        $sql .= " FROM ".MAIN_DB_PREFIX."document_model";
        $sql .= " WHERE type = '".$db->escape($type)."'";
        $sql .= " AND entity = ".$conf->entity;
        $resql = $db->query($sql);
        if ($resql) {
            $i = 0;
            $num_rows = $db->num_rows($resql);
            while ($i < $num_rows) {
                $array = $db->fetch_array($resql);
                array_push($def, $array[0]);
                $i++;
            }
        } else {
            dol_print_error($db);
        }

        print "<table class=\"noborder\" width=\"100%\">\n";
        print "<tr class=\"liste_titre\">\n";
        print '<td>'.$langs->trans("Name").'</td>';
        print '<td>'.$langs->trans("Description").'</td>';
        print '<td class="center" width="60">'.$langs->trans("Status")."</td>\n";
        print '<td class="center" width="60">'.$langs->trans("Default")."</td>\n";
        print '<td class="center" width="38">'.$langs->trans("ShortInfo").'</td>';
        print '<td class="center" width="38">'.$langs->trans("Preview").'</td>';
        print "</tr>\n";

        clearstatcache();

        foreach (array('/core/modules/'.$moduledir.'/doc/') as $reldir) {
            foreach (array('', '/doc/') as $valdir) {
                $realpath = $reldir."/".$valdir;
                $dir = dol_buildpath($realpath);

                if (is_dir($dir)) {
                    $handle = opendir($dir);
                    if (is_resource($handle)) {
                        while (($file = readdir($handle)) !== false) {
                            $filelist[] = $file;
                        }
                        closedir($handle);
                        arsort($filelist);

                        foreach ($filelist as $file) {
                            if (preg_match('/\.modules\.php$/i', $file) && preg_match('/^(pdf_|doc_)/', $file)) {
                                if (file_exists($dir.'/'.$file)) {
                                    $name = substr($file, 4, dol_strlen($file) - 16);
                                    $classname = substr($file, 0, dol_strlen($file) - 4);

                                    require_once $dir.'/'.$file;
                                    $module = new $classname($db);

                                    $modulequalified = 1;
                                    if ($module->version == 'development' && getDolGlobalInt('MAIN_FEATURES_LEVEL') < 2) {
                                        $modulequalified = 0;
                                    }
                                    if ($module->version == 'experimental' && getDolGlobalInt('MAIN_FEATURES_LEVEL') < 1) {
                                        $modulequalified = 0;
                                    }

                                    if ($modulequalified) {
                                        print '<tr class="oddeven"><td width="100">';
                                        print (empty($module->name) ? $name : $module->name);
                                        print "</td><td>\n";
                                        if (method_exists($module, 'info')) {
                                            print $module->info($langs);
                                        } else {
                                            print $module->description;
                                        }
                                        print '</td>';

                                        // Active
                                        if (in_array($name, $def)) {
                                            print '<td class="center">'."\n";
                                            print '<a href="'.$_SERVER["PHP_SELF"].'?action=del&token='.newToken().'&value='.urlencode($name).'">';
                                            print img_picto($langs->trans("Enabled"), 'switch_on');
                                            print '</a>';
                                            print '</td>';
                                        } else {
                                            print '<td class="center">'."\n";
                                            print '<a href="'.$_SERVER["PHP_SELF"].'?action=set&token='.newToken().'&value='.urlencode($name).'&scan_dir='.urlencode($module->scandir).'&label='.urlencode($module->name).'">';
                                            print img_picto($langs->trans("Disabled"), 'switch_off');
                                            print '</a>';
                                            print "</td>";
                                        }

                                        // Default
                                        print '<td class="center">';
                                        $constforvar = 'INTERNALHTS_'.strtoupper($myTmpObjectKey).'_ADDON_PDF';
                                        if (getDolGlobalString($constforvar) == $name) {
                                            //print img_picto($langs->trans("Default"), 'on');
                                            // Even if choice is the default value, we allow to edit it, to show its name
                                            print '<a href="'.$_SERVER["PHP_SELF"].'?action=unsetdoc&token='.newToken().'&object='.urlencode(strtolower($myTmpObjectKey)).'&value='.urlencode($name).'&scan_dir='.urlencode($module->scandir).'&label='.urlencode($module->name).'&type='.urlencode($type).'" alt="'.$langs->trans("Disable").'">';
                                            print img_picto($langs->trans("Enabled"), 'on');
                                            print '</a>';
                                        } else {
                                            print '<a href="'.$_SERVER["PHP_SELF"].'?action=setdoc&token='.newToken().'&object='.urlencode(strtolower($myTmpObjectKey)).'&value='.urlencode($name).'&scan_dir='.urlencode($module->scandir).'&label='.urlencode($module->name).'" alt="'.$langs->trans("Default").'">';
                                            print img_picto($langs->trans("Disabled"), 'off');
                                            print '</a>';
                                        }
                                        print '</td>';

                                        // Info
                                        $htmltooltip = ''.$langs->trans("Name").': '.$module->name;
                                        $htmltooltip .= '<br>'.$langs->trans("Type").': '.($module->type ? $module->type : $langs->trans("Unknown"));
                                        if ($module->type == 'pdf') {
                                            $htmltooltip .= '<br>'.$langs->trans("Width").'/'.$langs->trans("Height").': '.$module->page_largeur.'/'.$module->page_hauteur;
                                        }
                                        $htmltooltip .= '<br>'.$langs->trans("Path").': '.preg_replace('/^\//', '', $realpath).'/'.$file;

                                        $htmltooltip .= '<br><br><u>'.$langs->trans("FeaturesSupported").':</u>';
                                        $htmltooltip .= '<br>'.$langs->trans("Logo").': '.yn($module->option_logo, 1, 1);
                                        $htmltooltip .= '<br>'.$langs->trans("MultiLanguage").': '.yn($module->option_multilang, 1, 1);

                                        print '<td class="center">';
                                        print $form->textwithpicto('', $htmltooltip, 1, 0);
                                        print '</td>';

                                        // Preview
                                        print '<td class="center">';
                                        if ($module->type == 'pdf') {
                                            print '<a href="'.$_SERVER["PHP_SELF"].'?action=specimen&module='.$name.'&object='.$myTmpObjectKey.'">'.img_object($langs->trans("Preview"), 'pdf').'</a>';
                                        } else {
                                            print img_object($langs->trans("PreviewNotAvailable"), 'generic');
                                        }
                                        print '</td>';

                                        print "</tr>\n";
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        print '</table>';
    }
}

if (empty($setupnotempty)) {
    print '<br>'.$langs->trans("NothingToSetup");
}

// Page end
print dol_get_fiche_end();

llxFooter();