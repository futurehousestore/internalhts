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
 * \file       lib/internalhts.lib.php
 * \ingroup    internalhts
 * \brief      Library files with common functions for InternalHTS
 */

/**
 * Prepare admin pages header
 *
 * @return array
 */
function internalhtsAdminPrepareHead()
{
	global $langs, $conf;

	$langs->load("internalhts@internalhts");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/internalhts/admin/setup.php", 1);
	$head[$h][1] = $langs->trans("Settings");
	$head[$h][2] = 'settings';
	$h++;

	$head[$h][0] = dol_buildpath("/internalhts/admin/about.php", 1);
	$head[$h][1] = $langs->trans("About");
	$head[$h][2] = 'about';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@internalhts:/internalhts/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@internalhts:/internalhts/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, null, $head, $h, 'internalhts');

	complete_head_from_modules($conf, $langs, null, $head, $h, 'internalhts', 'remove');

	return $head;
}

/**
 * Return array of tabs to used on pages for internal HTS invoices.
 *
 * @param 	InternalHTSInvoice	$object		Object invoice shown
 * @return 	array				Array of tabs
 */
function internalhts_invoice_prepare_head($object)
{
	global $db, $langs, $conf;

	$langs->load("internalhts@internalhts");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath('/internalhts/internalhts_invoice_card.php', 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Card");
	$head[$h][2] = 'card';
	$h++;

	if (isset($object->fields['note_public']) || isset($object->fields['note_private'])) {
		$nbNote = 0;
		if (!empty($object->note_private)) {
			$nbNote++;
		}
		if (!empty($object->note_public)) {
			$nbNote++;
		}
		$head[$h][0] = dol_buildpath('/internalhts/internalhts_invoice_note.php', 1).'?id='.$object->id;
		$head[$h][1] = $langs->trans('Notes');
		if ($nbNote > 0) {
			$head[$h][1] .= (!empty($nbNote) ? '<span class="badge marginleftonlyshort">'.$nbNote.'</span>' : '');
		}
		$head[$h][2] = 'note';
		$h++;
	}

	require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
	require_once DOL_DOCUMENT_ROOT.'/core/class/link.class.php';
	$upload_dir = $conf->internalhts->dir_output."/internalhts_invoice/".dol_sanitizeFileName($object->ref);
	$nbFiles = count(dol_dir_list($upload_dir, 'files', 0, '', '(\.meta|_preview.*\.png)$'));
	$nbLinks = Link::count($db, $object->element, $object->id);
	$head[$h][0] = dol_buildpath("/internalhts/internalhts_invoice_document.php", 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans('Documents');
	if (($nbFiles + $nbLinks) > 0) {
		$head[$h][1] .= '<span class="badge marginleftonlyshort">'.($nbFiles + $nbLinks).'</span>';
	}
	$head[$h][2] = 'document';
	$h++;

	$head[$h][0] = dol_buildpath("/internalhts/internalhts_invoice_agenda.php", 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Events");
	$head[$h][2] = 'agenda';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@internalhts:/internalhts/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@internalhts:/internalhts/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'internalhts_invoice');

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'internalhts_invoice', 'remove');

	return $head;
}

/**
 * Return array of tabs to used on pages for HTS mappings.
 *
 * @param 	HTSMapping	$object		Object mapping shown
 * @return 	array				Array of tabs
 */
function hts_mapping_prepare_head($object)
{
	global $db, $langs, $conf;

	$langs->load("internalhts@internalhts");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath('/internalhts/hts_mapping_card.php', 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Card");
	$head[$h][2] = 'card';
	$h++;

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'hts_mapping');

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'hts_mapping', 'remove');

	return $head;
}

/**
 * Get list of HTS codes for select
 *
 * @param DoliDB $db Database handler
 * @param int $selected Selected value
 * @param string $htmlname Name of select
 * @param int $show_empty Show empty option
 * @return string HTML select
 */
function selectHTSCodes($db, $selected = 0, $htmlname = 'hts_code', $show_empty = 1)
{
	global $langs;

	$langs->load("internalhts@internalhts");

	$out = '';
	$sql = "SELECT rowid, code, label FROM ".MAIN_DB_PREFIX."c_hts_codes";
	$sql .= " WHERE active = 1";
	$sql .= " ORDER BY code ASC";

	$resql = $db->query($sql);
	if ($resql) {
		$out .= '<select class="flat minwidth200" name="'.$htmlname.'" id="'.$htmlname.'">';
		if ($show_empty) {
			$out .= '<option value="0">&nbsp;</option>';
		}

		$num = $db->num_rows($resql);
		$i = 0;
		while ($i < $num) {
			$obj = $db->fetch_object($resql);
			$key = $obj->rowid;
			$value = $obj->code.' - '.$obj->label;
			
			if ($key == $selected) {
				$out .= '<option value="'.$key.'" selected>'.$value.'</option>';
			} else {
				$out .= '<option value="'.$key.'">'.$value.'</option>';
			}
			$i++;
		}
		$out .= '</select>';
		$db->free($resql);
	} else {
		dol_print_error($db);
	}

	return $out;
}

/**
 * Get list of countries for select
 *
 * @param DoliDB $db Database handler
 * @param int $selected Selected value
 * @param string $htmlname Name of select
 * @param int $show_empty Show empty option
 * @return string HTML select
 */
function selectCountriesOrigin($db, $selected = '', $htmlname = 'country_origin', $show_empty = 1)
{
	global $langs;

	$langs->load("internalhts@internalhts");

	$countries = array(
		'US' => 'United States',
		'CA' => 'Canada', 
		'MX' => 'Mexico',
		'CN' => 'China',
		'DE' => 'Germany',
		'FR' => 'France',
		'GB' => 'United Kingdom',
		'IT' => 'Italy',
		'JP' => 'Japan',
		'KR' => 'South Korea',
		'IN' => 'India'
	);

	$out = '';
	$out .= '<select class="flat minwidth200" name="'.$htmlname.'" id="'.$htmlname.'">';
	if ($show_empty) {
		$out .= '<option value="">&nbsp;</option>';
	}

	foreach ($countries as $code => $name) {
		if ($code == $selected) {
			$out .= '<option value="'.$code.'" selected>'.$code.' - '.$name.'</option>';
		} else {
			$out .= '<option value="'.$code.'">'.$code.' - '.$name.'</option>';
		}
	}
	$out .= '</select>';

	return $out;
}

/**
 * Generate invoice from shipment
 *
 * @param DoliDB $db Database handler
 * @param int $shipment_id Shipment ID
 * @param User $user User creating
 * @return int Invoice ID if OK, <0 if KO
 */
function generateInvoiceFromShipment($db, $shipment_id, $user)
{
	require_once DOL_DOCUMENT_ROOT.'/expedition/class/expedition.class.php';
	require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
	dol_include_once('/internalhts/class/internalhts_invoice.class.php');
	dol_include_once('/internalhts/class/internalhts_invoice_line.class.php');
	dol_include_once('/internalhts/class/hts_mapping.class.php');

	$shipment = new Expedition($db);
	$result = $shipment->fetch($shipment_id);
	
	if ($result <= 0) {
		return -1;
	}

	// Create invoice
	$invoice = new InternalHTSInvoice($db);
	$invoice->ref = '(PROV)';
	$invoice->fk_soc = $shipment->fk_soc;
	$invoice->fk_shipment = $shipment_id;
	$invoice->date_invoice = dol_now();
	$invoice->invoice_type = 'standard';
	$invoice->status = InternalHTSInvoice::STATUS_DRAFT;

	$result = $invoice->create($user);
	if ($result <= 0) {
		return -2;
	}

	// Add lines from shipment
	$shipment->fetch_lines();
	$total_weight = 0;
	$total_packages = 0;
	$total_ht = 0;

	foreach ($shipment->lines as $shipment_line) {
		if (empty($shipment_line->fk_product)) continue;

		// Get product info
		$product = new Product($db);
		$product->fetch($shipment_line->fk_product);

		// Get HTS mapping
		$mapping = HTSMapping::getByProduct($db, $shipment_line->fk_product);

		$line = new InternalHTSInvoiceLine($db);
		$line->fk_internalhts_invoice = $invoice->id;
		$line->fk_product = $shipment_line->fk_product;
		$line->description = $product->description;
		$line->qty = $shipment_line->qty_shipped;
		$line->unit_price = $product->price;

		if ($mapping && is_object($mapping)) {
			$line->fk_hts_code = $mapping->fk_hts_code;
			$hts_info = $mapping->getHTSCodeInfo();
			if (!empty($hts_info['code'])) {
				$line->hts_code = $hts_info['code'];
			}
			$line->country_origin = $mapping->country_origin;
			$line->customs_value = $mapping->customs_value > 0 ? $mapping->customs_value : $product->price;
			$line->weight_kg = $mapping->weight_kg > 0 ? $mapping->weight_kg : $product->weight;
		} else {
			$line->customs_value = $product->price;
			$line->weight_kg = $product->weight;
		}

		$line->packages = 1; // Default to 1 package per line
		$line->total_ht = $line->qty * $line->unit_price;

		$result = $line->create($user);
		if ($result > 0) {
			$total_weight += $line->weight_kg * $line->qty;
			$total_packages += $line->packages;
			$total_ht += $line->total_ht;
		}
	}

	// Update invoice totals
	$invoice->total_weight_kg = $total_weight;
	$invoice->total_packages = $total_packages;
	$invoice->total_ht = $total_ht;
	$invoice->total_ttc = $total_ht; // No tax for internal invoices
	$invoice->update($user);

	return $invoice->id;
}