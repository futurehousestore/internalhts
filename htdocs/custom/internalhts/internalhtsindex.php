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
 * \file       internalhtsindex.php
 * \ingroup    internalhts
 * \brief      Home page of internalhts top menu
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
if (!$res && file_exists("../main.inc.php")) {
    $res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
    $res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
    $res = @include "../../../main.inc.php";
}
if (!$res) {
    die("Include of main fails");
}

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';

// Load translation files required by the page
$langs->loadLangs(array("internalhts@internalhts"));

$action = GETPOST('action', 'aZ09');

// Security check
if (!$user->hasRight('internalhts', 'read')) {
    accessforbidden();
}

// Get parameters
$id = GETPOST('id', 'int');
$myparam = GETPOST('myparam', 'alpha');

$max = 5;
$now = dol_now();

/*
 * Actions
 */

// None

/*
 * View
 */

$form = new Form($db);
$formfile = new FormFile($db);

llxHeader("", $langs->trans("InternalHTSArea"));

print load_fiche_titre($langs->trans("InternalHTSArea"), '', 'internalhts.png@internalhts');

print '<div class="fichecenter"><div class="fichethirdleft">';

/*
 * Statistics
 */

print '<div class="div-table-responsive-no-min">'; // You can use div-table-responsive-no-min if you dont need reserved height for your table
print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<th colspan="2">'.$langs->trans("Statistics").'</th>';
print "</tr>\n";

// Get some statistics
$sql = "SELECT COUNT(*) as nb_total, SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as nb_draft, SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as nb_validated";
$sql .= " FROM ".MAIN_DB_PREFIX."internalhts_doc";
$sql .= " WHERE entity IN (".getEntity('internalhts').")";

$result = $db->query($sql);
if ($result) {
    $obj = $db->fetch_object($result);
    
    $nb_total = $obj->nb_total;
    $nb_draft = $obj->nb_draft;
    $nb_validated = $obj->nb_validated;

    print '<tr class="oddeven">';
    print '<td>'.$langs->trans("Total").'</td>';
    print '<td class="right">'.$nb_total.'</td>';
    print '</tr>';
    
    print '<tr class="oddeven">';
    print '<td>'.$langs->trans("Draft").'</td>';
    print '<td class="right">'.$nb_draft.'</td>';
    print '</tr>';
    
    print '<tr class="oddeven">';
    print '<td>'.$langs->trans("Validated").'</td>';
    print '<td class="right">'.$nb_validated.'</td>';
    print '</tr>';
}

print '</table>';
print '</div>';

print '</div><div class="fichetwothirdright">';

$NBMAX = $max;
$MAXLIST = 10;

/*
 * Last modified invoices
 */

print '<div class="div-table-responsive-no-min">'; // You can use div-table-responsive-no-min if you dont need reserved height for your table
print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<th colspan="2">'.$langs->trans("LastModifiedInternalInvoices", $max).'</th>';
print '<th class="right">'.$langs->trans("Status").'</th>';
print "</tr>\n";

$sql = "SELECT i.rowid, i.ref, i.label, i.status, i.tms";
$sql .= " FROM ".MAIN_DB_PREFIX."internalhts_doc as i";
$sql .= " WHERE i.entity IN (".getEntity('internalhts').")";
$sql .= " ORDER BY i.tms DESC";
$sql .= " LIMIT ".$max;

$result = $db->query($sql);
if ($result) {
    $num = $db->num_rows($result);
    $i = 0;

    if ($num) {
        while ($i < $num) {
            $obj = $db->fetch_object($result);

            print '<tr class="oddeven">';
            print '<td class="nowrap">';
            print '<a href="'.dol_buildpath('/internalhts/card.php', 1).'?id='.$obj->rowid.'">'.img_object($langs->trans("ShowInternalInvoice"), "internalhts@internalhts").' '.$obj->ref.'</a>';
            print '</td>';
            print '<td>'.dol_escape_htmltag($obj->label).'</td>';
            print '<td class="right">';
            if ($obj->status == 0) {
                print '<span class="badge badge-status4 badge-status">'.$langs->trans("Draft").'</span>';
            } else {
                print '<span class="badge badge-status6 badge-status">'.$langs->trans("Validated").'</span>';
            }
            print '</td>';
            print '</tr>';
            $i++;
        }

        $db->free($result);
    } else {
        print '<tr class="oddeven"><td colspan="3"><span class="opacitymedium">'.$langs->trans("None").'</span></td></tr>';
    }
} else {
    dol_print_error($db);
}

print "</table></div>";

print '</div></div>';

// End of page
llxFooter();