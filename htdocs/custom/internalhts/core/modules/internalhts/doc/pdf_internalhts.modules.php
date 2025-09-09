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
 * \file       core/modules/internalhts/doc/pdf_internalhts.modules.php
 * \ingroup    internalhts
 * \brief      PDF template for InternalHTS commercial invoices
 */

require_once DOL_DOCUMENT_ROOT.'/core/modules/internalhts/modules_internalhts.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';

/**
 * Class to generate the commercial invoice PDF for InternalHTS
 */
class pdf_internalhts extends ModeleNumRefInternalHTS
{
    /**
     * @var DoliDB Database handler
     */
    public $db;

    /**
     * @var string model name
     */
    public $name;

    /**
     * @var string model description (short)
     */
    public $description;

    /**
     * @var string document type
     */
    public $type;

    /**
     * @var array Minimum version of PHP required by module.
     * e.g.: PHP ≥ 5.6 = array(5, 6)
     */
    public $phpmin = array(5, 6);

    /**
     * Dolibarr version of the loaded document
     * @var string
     */
    public $version = 'dolibarr';

    /**
     * @var int page_largeur
     */
    public $page_largeur;

    /**
     * @var int page_hauteur
     */
    public $page_hauteur;

    /**
     * @var array format
     */
    public $format;

    /**
     * @var int marge_gauche
     */
    public $marge_gauche;

    /**
     * @var int marge_droite
     */
    public $marge_droite;

    /**
     * @var int marge_haute
     */
    public $marge_haute;

    /**
     * @var int marge_basse
     */
    public $marge_basse;

    /**
     * Issuer
     * @var Societe Object that emits
     */
    public $emetteur;

    /**
     * Constructor
     *
     * @param DoliDB $db Database handler
     */
    public function __construct($db)
    {
        global $conf, $langs, $mysoc;

        // Translations
        $langs->loadLangs(array("main", "bills", "internalhts@internalhts"));

        $this->db = $db;
        $this->name = "internalhts";
        $this->description = $langs->trans('InternalHTSCommercialInvoice');
        $this->update_main_doc_field = 1; // Save the name of generated file as the main doc when generating a doc with this template

        // Document type
        $this->type = 'pdf';
        $formatarray = pdf_getFormat();
        $this->page_largeur = $formatarray['width'];
        $this->page_hauteur = $formatarray['height'];
        $this->format = array($this->page_largeur, $this->page_hauteur);
        $this->marge_gauche = isset($conf->global->MAIN_PDF_MARGIN_LEFT) ? $conf->global->MAIN_PDF_MARGIN_LEFT : 10;
        $this->marge_droite = isset($conf->global->MAIN_PDF_MARGIN_RIGHT) ? $conf->global->MAIN_PDF_MARGIN_RIGHT : 10;
        $this->marge_haute = isset($conf->global->MAIN_PDF_MARGIN_TOP) ? $conf->global->MAIN_PDF_MARGIN_TOP : 10;
        $this->marge_basse = isset($conf->global->MAIN_PDF_MARGIN_BOTTOM) ? $conf->global->MAIN_PDF_MARGIN_BOTTOM : 10;

        $this->option_logo = 1; // Display logo
        $this->option_tva = 0; // Manage the vat option FACTURE_TVAOPTION
        $this->option_modereg = 0; // Display payment mode
        $this->option_condreg = 0; // Display payment terms
        $this->option_multilang = 1; // Available in several languages
        $this->option_escompte = 0; // Displays if there has been a discount
        $this->option_credit_note = 0; // Support credit notes
        $this->option_freetext = 1; // Support add of a personalised text
        $this->option_draft_watermark = 1; // Support add of a watermark on drafts

        // Get source company
        $this->emetteur = $mysoc;
        if (empty($this->emetteur->country_code)) {
            $this->emetteur->country_code = substr($langs->defaultlang, -2); // By default, if not defined
        }
    }

    // phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
    /**
     * Function to build pdf onto disk
     *
     * @param   InternalHTS  $object         Object to generate
     * @param   Translate    $outputlangs    Lang output object
     * @param   string       $srctemplatepath Full path of source filename for generator using a template file
     * @param   int          $hidedetails    Do not show line details
     * @param   int          $hidedesc       Do not show desc
     * @param   int          $hideref        Do not show ref
     * @return  int                          1=OK, 0=KO
     */
    public function write_file($object, $outputlangs, $srctemplatepath = '', $hidedetails = 0, $hidedesc = 0, $hideref = 0)
    {
        global $user, $langs, $conf, $hookmanager, $mysoc;

        dol_syslog("write_file outputlangs->defaultlang=".(is_object($outputlangs) ? $outputlangs->defaultlang : 'null'));

        if (!is_object($outputlangs)) {
            $outputlangs = $langs;
        }
        // For backward compatibility with FPDF, force output charset to ISO, because FPDF expect text to be encoded in ISO
        if (!empty($conf->global->MAIN_USE_FPDF)) {
            $outputlangs->charset_output = 'ISO-8859-1';
        }

        // Load translation files required by the page
        $outputlangs->loadLangs(array("main", "dict", "companies", "bills", "products", "internalhts@internalhts"));

        $nblines = count($object->lines);

        // Loop on each lines to detect if there is at least one image to show
        $realpatharray = array();
        $this->atleastonephoto = false;

        if ($conf->internalhts->multidir_output[$object->entity]) {
            $object->fetch_thirdparty();

            $deja_regle = "";

            // Definition of $dir and $file
            if ($object->specimen) {
                $dir = $conf->internalhts->multidir_output[$object->entity];
                $file = $dir."/SPECIMEN.pdf";
            } else {
                $objectref = dol_sanitizeFileName($object->ref);
                $dir = $conf->internalhts->multidir_output[$object->entity]."/".$objectref;
                $file = $dir."/".$objectref.".pdf";
            }

            if (!file_exists($dir)) {
                if (dol_mkdir($dir) < 0) {
                    $this->error = $langs->transnoentities("ErrorCanNotCreateDir", $dir);
                    return 0;
                }
            }

            if (file_exists($dir)) {
                // Add pdfgeneration hook
                if (!is_object($hookmanager)) {
                    include_once DOL_DOCUMENT_ROOT.'/core/class/hookmanager.class.php';
                    $hookmanager = new HookManager($this->db);
                }
                $hookmanager->initHooks(array('pdfgeneration'));
                $parameters = array('file'=>$file, 'object'=>$object, 'outputlangs'=>$outputlangs);
                global $action;
                $reshook = $hookmanager->executeHooks('beforePDFCreation', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks

                // Set nblines with the new facture lines content after hook
                $nblines = count($object->lines);
                $nbpayments = 0;

                // Create pdf instance
                $pdf = pdf_getInstance($this->format);
                $default_font_size = pdf_getPDFFontSize($outputlangs); // Must be after pdf_getInstance
                $pdf->SetAutoPageBreak(1, 0);

                $heightforinfotot = 40; // Height reserved to output the info and total part
                $heightforfreetext = (isset($conf->global->MAIN_PDF_FREETEXT_HEIGHT) ? $conf->global->MAIN_PDF_FREETEXT_HEIGHT : 5); // Height reserved to output the free text on last page
                $heightforfooter = $this->marge_basse + 8; // Height reserved to output the footer (value include bottom margin)

                if (class_exists('TCPDF')) {
                    $pdf->setPrintHeader(false);
                    $pdf->setPrintFooter(false);
                }
                $pdf->SetFont(pdf_getPDFFont($outputlangs));
                // Set path to the background PDF File
                if (!empty($conf->global->MAIN_ADD_PDF_BACKGROUND)) {
                    $pagecount = $pdf->setSourceFile($conf->mycompany->dir_output.'/'.$conf->global->MAIN_ADD_PDF_BACKGROUND);
                    $tplidx = $pdf->importPage(1);
                }

                $pdf->Open();
                $pagenb = 0;
                $pdf->SetDrawColor(128, 128, 128);

                $pdf->SetTitle($outputlangs->convToOutputCharset($object->ref));
                $pdf->SetSubject($outputlangs->transnoentities("CommercialInvoice"));
                $pdf->SetCreator("Dolibarr ".DOL_VERSION);
                $pdf->SetAuthor($outputlangs->convToOutputCharset($user->getFullName($outputlangs)));
                $pdf->SetKeyWords($outputlangs->convToOutputCharset($object->ref)." ".$outputlangs->transnoentities("CommercialInvoice")." ".$outputlangs->convToOutputCharset($object->thirdparty->name));
                if (!empty($conf->global->MAIN_DISABLE_PDF_COMPRESSION)) {
                    $pdf->SetCompression(false);
                }

                $pdf->SetMargins($this->marge_gauche, $this->marge_haute, $this->marge_droite); // Left, Top, Right

                // Add pages
                $this->_pagehead($pdf, $object, 1, $outputlangs);
                $pdf->SetFont('', '', $default_font_size - 1);
                $pdf->MultiCell(0, 3, ''); // Set interline to 3
                $pdf->SetTextColor(0, 0, 0);

                $tab_top = 90;
                $tab_top_newpage = (!empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD) ? 10 : 80);
                $tab_height = $this->page_hauteur - $tab_top - $heightforinfotot - $heightforfooter - $heightforfreetext;

                // Display notes
                $notetoshow = empty($object->note_public) ? '' : $object->note_public;
                if ($notetoshow) {
                    $substitutionarray = pdf_getSubstitutionArray($outputlangs, null, $object);
                    complete_substitutions_array($substitutionarray, $outputlangs, $object);
                    $notetoshow = make_substitutions($notetoshow, $substitutionarray, $outputlangs);
                    $notetoshow = convertBackOfficeMediasLinksToPublicLinks($notetoshow);

                    $tab_top = 88;

                    $pdf->SetFont('', '', $default_font_size - 1);
                    $pdf->writeHTMLCell(190, 3, $this->posxdesc - 1, $tab_top, dol_htmlentitiesbr($notetoshow), 0, 1);
                    $nexY = $pdf->GetY();
                    $height_note = $nexY - $tab_top;

                    // Rect takes a length in 3rd parameter
                    $pdf->SetDrawColor(192, 192, 192);
                    $pdf->Rect($this->marge_gauche, $tab_top - 1, $this->page_largeur - $this->marge_gauche - $this->marge_droite, $height_note + 1);

                    $tab_height = $tab_height - $height_note;
                    $tab_top = $nexY + 6;
                } else {
                    $height_note = 0;
                }

                // Display lines
                $iniY = $tab_top + 7;
                $curY = $tab_top + 7;
                $nexY = $tab_top + 7;

                // Loop on each lines
                for ($i = 0; $i < $nblines; $i++) {
                    $curY = $nexY;
                    $pdf->SetFont('', '', $default_font_size - 1); // Into loop to work with multipage
                    $pdf->SetTextColor(0, 0, 0);

                    // Define size of image if we need it
                    $imglinesize = array();
                    if (!empty($realpatharray[$i])) {
                        $imglinesize = pdf_getSizeForImage($realpatharray[$i]);
                    }

                    $pdf->setTopMargin($this->marge_haute);
                    $pdf->setPageOrientation('', 1, $heightforfooter + $heightforfreetext + $heightforinfotot); // The only function to edit the bottom margin of current page to set it.

                    $pageposbefore = $pdf->getPage();
                    $showpricebeforepagebreak = 1;
                    $posYAfterImage = 0;
                    $posYAfterDescription = 0;

                    // We start with Photo of product line
                    if (isset($imglinesize['width']) && isset($imglinesize['height']) && ($curY + $imglinesize['height']) > ($this->page_hauteur - ($heightforfooter + $heightforfreetext + $heightforinfotot))) { // If photo too high, we moved completely on new page
                        $pdf->AddPage('', '', true);
                        if (!empty($tplidx)) {
                            $pdf->useTemplate($tplidx);
                        }
                        if (!empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) {
                            $this->_pagehead($pdf, $object, 0, $outputlangs);
                        }
                        $pdf->setPage($pageposbefore + 1);

                        $curY = $tab_top_newpage;

                        // Allows data in the first page if description is long enough to break in multiples pages
                        if (!empty($conf->global->MAIN_PDF_DATA_ON_FIRST_PAGE)) {
                            $showpricebeforepagebreak = 1;
                        } else {
                            $showpricebeforepagebreak = 0;
                        }
                    }

                    if (isset($imglinesize['width']) && isset($imglinesize['height'])) {
                        $curX = $this->posxpicture - 1;
                        $pdf->Image($realpatharray[$i], $curX + (($this->posxtva - $this->posxpicture - $imglinesize['width']) / 2), $curY, $imglinesize['width'], $imglinesize['height'], '', '', '', 2, 300); // Use 300 dpi
                        // $pdf->Image does not increase value return by getY, so we save it manually
                        $posYAfterImage = $curY + $imglinesize['height'];
                    }

                    // Description of product line
                    $curX = $this->posxdesc - 1;

                    $pdf->startTransaction();
                    pdf_writelinedesc($pdf, $object, $i, $outputlangs, $this->posxtva - $curX, 3, $curX, $curY, $hideref, $hidedesc);
                    $pageposafter = $pdf->getPage();
                    if ($pageposbefore < $pageposafter) {
                        $pdf->rollbackTransaction(true);
                        $pageposafter = $pageposbefore;
                        //print $pageposafter.'-'.$pageposbefore;exit;
                        $pdf->setPageOrientation('', 1, $heightforfooter); // The only function to edit the bottom margin of current page to set it.
                        pdf_writelinedesc($pdf, $object, $i, $outputlangs, $this->posxtva - $curX, 4, $curX, $curY, $hideref, $hidedesc);
                        $pageposafter = $pdf->getPage();
                        $posyafter = $pdf->GetY();
                        //var_dump($posyafter); var_dump(($this->page_hauteur - ($heightforfooter+$heightforfreetext+$heightforinfotot))); exit;
                        if ($posyafter > ($this->page_hauteur - ($heightforfooter + $heightforfreetext + $heightforinfotot))) {
                            if ($i == ($nblines - 1)) {
                                $pdf->AddPage('', '', true);
                                if (!empty($tplidx)) {
                                    $pdf->useTemplate($tplidx);
                                }
                                if (!empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) {
                                    $this->_pagehead($pdf, $object, 0, $outputlangs);
                                }
                                $pdf->setPage($pageposafter + 1);
                            }
                        } else {
                            // We found a page break
                            $showpricebeforepagebreak = 1;
                        }
                    } else {
                        $pdf->commitTransaction();
                    }
                    $posYAfterDescription = $pdf->GetY();

                    $nexY = $pdf->GetY();
                    $pageposafter = $pdf->getPage();
                    $pdf->setPage($pageposbefore);
                    $pdf->setTopMargin($this->marge_haute);
                    $pdf->setPageOrientation('', 1, 0); // The only function to edit the bottom margin of current page to set it.

                    // We suppose that a too long description is moved completely on next page
                    if ($pageposafter > $pageposbefore && empty($showpricebeforepagebreak)) {
                        $pdf->setPage($pageposafter); $curY = $tab_top_newpage;
                    }

                    $pdf->SetFont('', '', $default_font_size - 1); // On repositionne la police par défaut

                    // SKU
                    if ($this->getColumnStatus('sku')) {
                        $pdf->SetXY($this->posxsku, $curY);
                        $pdf->MultiCell($this->posxqty - $this->posxsku - 0.8, 3, $object->lines[$i]->sku, 0, 'L');
                    }

                    // HTS Code
                    if ($this->getColumnStatus('hts')) {
                        $pdf->SetXY($this->posxhts, $curY);
                        $hts_code = '';
                        if ($object->lines[$i]->fk_hts > 0) {
                            // Fetch HTS code
                            require_once DOL_DOCUMENT_ROOT.'/custom/internalhts/class/hts.class.php';
                            $hts = new HTS($this->db);
                            if ($hts->fetch($object->lines[$i]->fk_hts) > 0) {
                                $hts_code = $hts->code;
                            }
                        }
                        $pdf->MultiCell($this->posxcoo - $this->posxhts - 0.8, 3, $hts_code, 0, 'L');
                    }

                    // Country of Origin
                    if ($this->getColumnStatus('coo')) {
                        $pdf->SetXY($this->posxcoo, $curY);
                        $pdf->MultiCell($this->posxqty - $this->posxcoo - 0.8, 3, $object->lines[$i]->country_of_origin, 0, 'L');
                    }

                    // Qty
                    if ($this->getColumnStatus('qty')) {
                        $pdf->SetXY($this->posxqty, $curY);
                        $pdf->MultiCell($this->posxup - $this->posxqty - 0.8, 3, $object->lines[$i]->qty, 0, 'R');
                    }

                    // Unit price
                    if ($this->getColumnStatus('up')) {
                        $pdf->SetXY($this->posxup, $curY);
                        $pdf->MultiCell($this->posxcustoms - $this->posxup - 0.8, 3, price($object->lines[$i]->unit_price), 0, 'R');
                    }

                    // Customs unit value
                    if ($this->getColumnStatus('customs')) {
                        $pdf->SetXY($this->posxcustoms, $curY);
                        $pdf->MultiCell($this->posxtotalht - $this->posxcustoms - 0.8, 3, price($object->lines[$i]->customs_unit_value), 0, 'R');
                    }

                    // Total HT
                    if ($this->getColumnStatus('totalht')) {
                        $pdf->SetXY($this->posxtotalht, $curY);
                        $pdf->MultiCell($this->page_largeur - $this->marge_droite - $this->posxtotalht, 3, price($object->lines[$i]->total_ht), 0, 'R');
                    }

                    $nexY = max($pdf->GetY(), $posYAfterImage);

                    // Add line
                    if (!empty($conf->global->MAIN_PDF_DASH_BETWEEN_LINES) && $i < ($nblines - 1)) {
                        $pdf->setPage($pageposafter);
                        $pdf->SetLineWidth(0.1);
                        $pdf->SetDrawColor(80, 80, 80);
                        if ($i == 0) {
                            $newY = $nexY + 3;
                        } else {
                            $newY = $nexY + 1;
                        }
                        $pdf->line($this->marge_gauche, $newY, $this->page_largeur - $this->marge_droite, $newY);
                        $pdf->SetLineWidth(0.3);
                        $pdf->SetDrawColor(0, 0, 0);
                    }

                    $nexY += 2; // Add space between lines

                    // Detect if some page were added automatically and output _tableau for past pages
                    while ($pagenb < $pageposafter) {
                        $pdf->setPage($pagenb);
                        if ($pagenb == 1) {
                            $this->_tableau($pdf, $tab_top, $this->page_hauteur - $tab_top - $heightforfooter, 0, $outputlangs, 0, 1, $object->multicurrency_code);
                        } else {
                            $this->_tableau($pdf, $tab_top_newpage, $this->page_hauteur - $tab_top_newpage - $heightforfooter, 0, $outputlangs, 1, 1, $object->multicurrency_code);
                        }
                        $this->_pagefoot($pdf, $object, $outputlangs, 1);
                        $pagenb++;
                        $pdf->setPage($pagenb);
                        $pdf->setPageOrientation('', 1, 0); // The only function to edit the bottom margin of current page to set it.
                        if (!empty($tplidx)) {
                            $pdf->useTemplate($tplidx);
                        }
                        if (!empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) {
                            $this->_pagehead($pdf, $object, 0, $outputlangs);
                        }
                    }
                    if (isset($object->lines[$i + 1]->pagebreak) && $object->lines[$i + 1]->pagebreak) {
                        if ($pagenb == 1) {
                            $this->_tableau($pdf, $tab_top, $this->page_hauteur - $tab_top - $heightforfooter, 0, $outputlangs, 0, 1, $object->multicurrency_code);
                        } else {
                            $this->_tableau($pdf, $tab_top_newpage, $this->page_hauteur - $tab_top_newpage - $heightforfooter, 0, $outputlangs, 1, 1, $object->multicurrency_code);
                        }
                        $this->_pagefoot($pdf, $object, $outputlangs, 1);
                        // New page
                        $pdf->AddPage();
                        if (!empty($tplidx)) {
                            $pdf->useTemplate($tplidx);
                        }
                        $pagenb++;
                        if (!empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) {
                            $this->_pagehead($pdf, $object, 0, $outputlangs);
                        }
                    }
                }

                // Show square
                if ($pagenb == 1) {
                    $this->_tableau($pdf, $tab_top, $this->page_hauteur - $tab_top - $heightforinfotot - $heightforfooter - $heightforfreetext, 0, $outputlangs, 0, 0, $object->multicurrency_code);
                    $bottomlasttab = $this->page_hauteur - $heightforinfotot - $heightforfooter - $heightforfreetext + 1;
                } else {
                    $this->_tableau($pdf, $tab_top_newpage, $this->page_hauteur - $tab_top_newpage - $heightforinfotot - $heightforfooter - $heightforfreetext, 0, $outputlangs, 1, 0, $object->multicurrency_code);
                    $bottomlasttab = $this->page_hauteur - $heightforinfotot - $heightforfooter - $heightforfreetext + 1;
                }

                // Display infos area
                $posy = $this->_tableau_info($pdf, $object, $bottomlasttab, $outputlangs);

                // Display total area
                $posy = $this->_tableau_tot($pdf, $object, $deja_regle, $bottomlasttab, $outputlangs);

                // Display free text
                if (!empty($conf->global->MAIN_PDF_FREETEXT)) {
                    $pdf->SetFont('', '', $default_font_size - 2);
                    $pdf->SetTextColor(0, 0, 0);
                    $pdf->SetXY($this->marge_gauche, $this->page_hauteur - $heightforfooter - $heightforfreetext);
                    $pdf->MultiCell(0, 3, $conf->global->MAIN_PDF_FREETEXT, 0, 'L', 0);
                }

                // Page footer
                $this->_pagefoot($pdf, $object, $outputlangs);
                if (method_exists($pdf, 'AliasNbPages')) {
                    $pdf->AliasNbPages();
                }

                $pdf->Close();

                $pdf->Output($file, 'F');

                // Add pdfgeneration hook
                $hookmanager->initHooks(array('pdfgeneration'));
                $parameters = array('file'=>$file, 'object'=>$object, 'outputlangs'=>$outputlangs);
                global $action;
                $reshook = $hookmanager->executeHooks('afterPDFCreation', $parameters, $this, $action); // Note that $action and $object may have been modified by some hooks

                dolChmod($file);

                $this->result = array('fullpath'=>$file);

                return 1; // No error
            } else {
                $this->error = $langs->transnoentities("ErrorCanNotCreateDir", $dir);
                return 0;
            }
        } else {
            $this->error = $langs->transnoentities("ErrorConstantNotDefined", "INTERNALHTS_OUTPUTDIR");
            return 0;
        }
    }

    // phpcs:disable PEAR.NamingConventions.ValidFunctionName.PublicUnderscore
    /**
     * Show table for lines
     *
     * @param   TCPDF       $pdf            Object PDF
     * @param   string      $tab_top        Top position of table
     * @param   string      $tab_height     Height of table (rectangle)
     * @param   int         $nexY           Y (not used)
     * @param   Translate   $outputlangs    Langs object
     * @param   int         $hidetop        1=Hide top bar of array and title, 0=Hide nothing, -1=Hide only title
     * @param   int         $hidebottom     Hide bottom bar of array
     * @param   string      $currency       Currency code
     * @return  void
     */
    protected function _tableau(&$pdf, $tab_top, $tab_height, $nexY, $outputlangs, $hidetop = 0, $hidebottom = 0, $currency = '')
    {
        global $conf;

        // Force to disable hidetop and hidebottom
        $hidebottom = 0;
        if ($hidetop) {
            $hidetop = -1;
        }

        $currency = !empty($currency) ? $currency : $conf->currency;
        $default_font_size = pdf_getPDFFontSize($outputlangs);

        // Amount in (at tab_top - 1)
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('', '', $default_font_size - 2);

        if (empty($hidetop)) {
            $titre = $outputlangs->transnoentities("CommercialInvoiceLines");
            $pdf->SetXY($this->marge_gauche, $tab_top - 4);
            $pdf->MultiCell(0, 2, $titre, 0, 'L');
        }

        $pdf->SetDrawColor(128, 128, 128);
        $pdf->SetFont('', '', $default_font_size - 1);

        // Output Rect
        $this->printRect($pdf, $this->marge_gauche, $tab_top, $this->page_largeur - $this->marge_gauche - $this->marge_droite, $tab_height, $hidetop, $hidebottom); // Rect takes a length in 3rd parameter and 4th parameter

        if (empty($hidetop)) {
            $pdf->line($this->marge_gauche, $tab_top + 5, $this->page_largeur - $this->marge_droite, $tab_top + 5); // line takes a position y in 2nd parameter and 4th parameter

            $pdf->SetXY($this->posxdesc - 1, $tab_top + 1);
            $pdf->MultiCell($this->posxsku - $this->posxdesc, 2, $outputlangs->transnoentities("Description"), '', 'L');
        }

        if (!empty($this->getColumnStatus('sku'))) {
            $pdf->line($this->posxsku - 1, $tab_top, $this->posxsku - 1, $tab_top + $tab_height);
            if (empty($hidetop)) {
                $pdf->SetXY($this->posxsku - 1, $tab_top + 1);
                $pdf->MultiCell($this->posxhts - $this->posxsku - 1, 2, $outputlangs->transnoentities("SKU"), '', 'L');
            }
        }

        if (!empty($this->getColumnStatus('hts'))) {
            $pdf->line($this->posxhts - 1, $tab_top, $this->posxhts - 1, $tab_top + $tab_height);
            if (empty($hidetop)) {
                $pdf->SetXY($this->posxhts - 1, $tab_top + 1);
                $pdf->MultiCell($this->posxcoo - $this->posxhts - 1, 2, $outputlangs->transnoentities("HTSCode"), '', 'L');
            }
        }

        if (!empty($this->getColumnStatus('coo'))) {
            $pdf->line($this->posxcoo - 1, $tab_top, $this->posxcoo - 1, $tab_top + $tab_height);
            if (empty($hidetop)) {
                $pdf->SetXY($this->posxcoo - 1, $tab_top + 1);
                $pdf->MultiCell($this->posxqty - $this->posxcoo - 1, 2, $outputlangs->transnoentities("CountryOfOrigin"), '', 'L');
            }
        }

        if (!empty($this->getColumnStatus('qty'))) {
            $pdf->line($this->posxqty - 1, $tab_top, $this->posxqty - 1, $tab_top + $tab_height);
            if (empty($hidetop)) {
                $pdf->SetXY($this->posxqty - 1, $tab_top + 1);
                $pdf->MultiCell($this->posxup - $this->posxqty - 1, 2, $outputlangs->transnoentities("Qty"), '', 'C');
            }
        }

        if (!empty($this->getColumnStatus('up'))) {
            $pdf->line($this->posxup - 1, $tab_top, $this->posxup - 1, $tab_top + $tab_height);
            if (empty($hidetop)) {
                $pdf->SetXY($this->posxup - 1, $tab_top + 1);
                $pdf->MultiCell($this->posxcustoms - $this->posxup - 1, 2, $outputlangs->transnoentities("UnitPrice").' ('.$outputlangs->transnoentities("HT").')', '', 'C');
            }
        }

        if (!empty($this->getColumnStatus('customs'))) {
            $pdf->line($this->posxcustoms - 1, $tab_top, $this->posxcustoms - 1, $tab_top + $tab_height);
            if (empty($hidetop)) {
                $pdf->SetXY($this->posxcustoms - 1, $tab_top + 1);
                $pdf->MultiCell($this->posxtotalht - $this->posxcustoms - 1, 2, $outputlangs->transnoentities("CustomsUnitValue"), '', 'C');
            }
        }

        if (!empty($this->getColumnStatus('totalht'))) {
            if (empty($hidetop)) {
                $pdf->SetXY($this->posxtotalht - 1, $tab_top + 1);
                $pdf->MultiCell($this->page_largeur - $this->marge_droite - $this->posxtotalht, 2, $outputlangs->transnoentities("TotalHT"), '', 'C');
            }
        }
    }

    // phpcs:disable PEAR.NamingConventions.ValidFunctionName.PublicUnderscore
    /**
     * Show top header of page.
     *
     * @param   TCPDF       $pdf            Object PDF
     * @param   InternalHTS $object         Object to show
     * @param   int         $showaddress    0=hide address, 1=show address
     * @param   Translate   $outputlangs    Object lang for output
     * @param   string      $titlekey       Translation key to show as title of document
     * @return  int                         Return topshift value
     */
    protected function _pagehead(&$pdf, $object, $showaddress, $outputlangs, $titlekey = "CommercialInvoice")
    {
        global $conf, $langs, $hookmanager;

        // Load translation files required by the page
        $outputlangs->loadLangs(array("main", "bills", "propal", "companies", "internalhts@internalhts"));

        $default_font_size = pdf_getPDFFontSize($outputlangs);

        pdf_pagehead($pdf, $outputlangs, $this->page_hauteur);

        // Show Draft Watermark
        if ($object->status == $object::STATUS_DRAFT && (!empty($conf->global->INTERNALHTS_DRAFT_WATERMARK))) {
            pdf_watermark($pdf, $outputlangs, $this->page_hauteur, $this->page_largeur, 'mm', $conf->global->INTERNALHTS_DRAFT_WATERMARK);
        }

        $pdf->SetTextColor(0, 0, 60);
        $pdf->SetFont('', 'B', $default_font_size + 3);

        $w = 110;

        $posy = $this->marge_haute;
        $posx = $this->page_largeur - $this->marge_droite - $w;

        $pdf->SetXY($this->marge_gauche, $posy);

        // Logo
        if (!empty($this->emetteur->logo)) {
            $logodir = $conf->mycompany->dir_output;
            if (!empty($conf->mycompany->multidir_output[$object->entity])) {
                $logodir = $conf->mycompany->multidir_output[$object->entity];
            }
            if (empty($conf->global->MAIN_PDF_USE_LARGE_LOGO)) {
                $logo = $logodir.'/logos/thumbs/'.$this->emetteur->logo_small;
            } else {
                $logo = $logodir.'/logos/'.$this->emetteur->logo;
            }
            if (is_readable($logo)) {
                $height = pdf_getHeightForLogo($logo);
                $pdf->Image($logo, $this->marge_gauche, $posy, 0, $height); // width=0 (auto)
            } else {
                $pdf->SetTextColor(200, 0, 0);
                $pdf->SetFont('', 'B', $default_font_size - 2);
                $pdf->MultiCell($w, 3, $outputlangs->transnoentities("ErrorLogoFileNotFound", $logo), 0, 'L');
                $pdf->MultiCell($w, 3, $outputlangs->transnoentities("ErrorGoToGlobalSetup"), 0, 'L');
            }
        } else {
            $text = $this->emetteur->name;
            $pdf->MultiCell($w, 4, $outputlangs->convToOutputCharset($text), 0, 'L');
        }

        $pdf->SetFont('', 'B', $default_font_size + 3);
        $pdf->SetXY($posx, $posy);
        $pdf->SetTextColor(0, 0, 60);
        $title = $outputlangs->transnoentities($titlekey);
        $pdf->MultiCell($w, 3, $title, '', 'R');

        $pdf->SetFont('', 'B', $default_font_size);

        $posy += 5;
        $pdf->SetXY($posx, $posy);
        $pdf->SetTextColor(0, 0, 60);
        $pdf->MultiCell($w, 4, $outputlangs->transnoentities("Ref")." : ".$outputlangs->convToOutputCharset($object->ref), '', 'R');

        $posy += 1;
        $pdf->SetFont('', '', $default_font_size - 2);

        if ($object->ref_customer) {
            $posy += 4;
            $pdf->SetXY($posx, $posy);
            $pdf->SetTextColor(0, 0, 60);
            $pdf->MultiCell($w, 3, $outputlangs->transnoentities("RefCustomer")." : ".$outputlangs->convToOutputCharset($object->ref_customer), '', 'R');
        }

        if ($object->thirdparty->code_client) {
            $posy += 4;
            $pdf->SetXY($posx, $posy);
            $pdf->SetTextColor(0, 0, 60);
            $pdf->MultiCell($w, 3, $outputlangs->transnoentities("CustomerCode")." : ".$outputlangs->transnoentities($object->thirdparty->code_client), '', 'R');
        }

        // Get contact
        if (!empty($conf->global->DOC_SHOW_FIRST_SALES_REP)) {
            $arrayidcontact = $object->getIdContact('internal', 'SALESREPFOLL');
            if (count($arrayidcontact) > 0) {
                $usertmp = new User($this->db);
                $usertmp->fetch($arrayidcontact[0]);
                $posy += 4;
                $pdf->SetXY($posx, $posy);
                $pdf->SetTextColor(0, 0, 60);
                $pdf->MultiCell($w, 3, $langs->transnoentities("SalesRepresentative")." : ".$usertmp->getFullName($langs), '', 'R');
            }
        }

        $posy += 2;

        $top_shift = 0;
        // Show list of linked objects
        $current_y = $pdf->getY();
        $posy = pdf_writeLinkedObjects($pdf, $object, $outputlangs, $posx, $posy, $w, 3, 'R', $default_font_size);
        if ($current_y < $pdf->getY()) {
            $top_shift = $pdf->getY() - $current_y;
        }

        if ($showaddress) {
            // Sender properties
            $carac_emetteur = '';
            // Add internal contact of proposal if defined
            $arrayidcontact = $object->getIdContact('internal', 'SALESREPFOLL');
            if (count($arrayidcontact) > 0) {
                $object->fetch_user($arrayidcontact[0]);
                $labelbeforecontactname = ($outputlangs->transnoentities("FromContactName") != 'FromContactName' ? $outputlangs->transnoentities("FromContactName") : $outputlangs->transnoentities("Name"));
                $carac_emetteur .= ($carac_emetteur ? "\n" : '').$labelbeforecontactname." ".$outputlangs->convToOutputCharset($object->user->getFullName($outputlangs));
                $carac_emetteur .= (getDolGlobalInt('PDF_SHOW_PHONE_AFTER_USER_CONTACT') || getDolGlobalInt('PDF_SHOW_EMAIL_AFTER_USER_CONTACT')) ? ' (' : '';
                $carac_emetteur .= (getDolGlobalInt('PDF_SHOW_PHONE_AFTER_USER_CONTACT') && !empty($object->user->office_phone)) ? $object->user->office_phone : '';
                $carac_emetteur .= (getDolGlobalInt('PDF_SHOW_PHONE_AFTER_USER_CONTACT') && getDolGlobalInt('PDF_SHOW_EMAIL_AFTER_USER_CONTACT')) ? ', ' : '';
                $carac_emetteur .= (getDolGlobalInt('PDF_SHOW_EMAIL_AFTER_USER_CONTACT') && !empty($object->user->email)) ? $object->user->email : '';
                $carac_emetteur .= (getDolGlobalInt('PDF_SHOW_PHONE_AFTER_USER_CONTACT') || getDolGlobalInt('PDF_SHOW_EMAIL_AFTER_USER_CONTACT')) ? ')' : '';
                $carac_emetteur .= "\n";
            }

            $carac_emetteur .= pdf_build_address($outputlangs, $this->emetteur, $object->thirdparty ?? null, '', 0, 'source', $object);

            // Show sender
            $posy = !empty($conf->global->MAIN_PDF_USE_ISO_LOCATION) ? 40 : 42;
            $posy += $top_shift;
            $posx = $this->marge_gauche;
            if (!empty($conf->global->MAIN_INVERT_SENDER_RECIPIENT)) {
                $posx = $this->page_largeur - $this->marge_droite - 80;
            }

            $hautcadre = !empty($conf->global->MAIN_PDF_USE_ISO_LOCATION) ? 38 : 40;
            $widthrecbox = !empty($conf->global->MAIN_PDF_USE_ISO_LOCATION) ? 92 : 82;

            // Show sender frame
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('', '', $default_font_size - 2);
            $pdf->SetXY($posx, $posy - 5);
            $pdf->MultiCell($widthrecbox, 5, $outputlangs->transnoentities("BillFrom"), 0, $ltrdirection);
            $pdf->SetXY($posx, $posy);
            $pdf->SetFillColor(230, 230, 230);
            $pdf->MultiCell($widthrecbox, $hautcadre, "", 0, 'R', 1);
            $pdf->SetTextColor(0, 0, 60);

            // Show sender name
            $pdf->SetXY($posx + 2, $posy + 3);
            $pdf->SetFont('', 'B', $default_font_size);
            $pdf->MultiCell($widthrecbox - 2, 4, $outputlangs->convToOutputCharset($this->emetteur->name), 0, $ltrdirection);
            $posy = $pdf->getY();

            // Show sender information
            $pdf->SetXY($posx + 2, $posy);
            $pdf->SetFont('', '', $default_font_size - 1);
            $pdf->MultiCell($widthrecbox - 2, 4, $carac_emetteur, 0, $ltrdirection);

            // If CUSTOMER contact defined, we use it
            $usecontact = false;
            $arrayidcontact = $object->getIdContact('external', 'CUSTOMER');
            if (count($arrayidcontact) > 0) {
                $usecontact = true;
                $result = $object->fetch_contact($arrayidcontact[0]);
            }

            // Recipient name
            if ($usecontact && $object->contact->socid != $object->thirdparty->id && (!isset($conf->global->MAIN_USE_COMPANY_NAME_OF_CONTACT) || !empty($conf->global->MAIN_USE_COMPANY_NAME_OF_CONTACT))) {
                $thirdparty = $object->contact;
            } else {
                $thirdparty = $object->thirdparty;
            }

            $carac_client_name = pdfBuildThirdpartyName($thirdparty, $outputlangs);

            $mode = 'target';
            $carac_client = pdf_build_address($outputlangs, $this->emetteur, $object->thirdparty, ($usecontact ? $object->contact : ''), $usecontact, $mode, $object);

            // Show recipient
            $widthrecbox = !empty($conf->global->MAIN_PDF_USE_ISO_LOCATION) ? 92 : 100;
            if ($this->page_largeur < 210) {
                $widthrecbox = 84; // To work with US executive format
            }
            $posy = !empty($conf->global->MAIN_PDF_USE_ISO_LOCATION) ? 40 : 42;
            $posy += $top_shift;
            $posx = $this->page_largeur - $this->marge_droite - $widthrecbox;
            if (!empty($conf->global->MAIN_INVERT_SENDER_RECIPIENT)) {
                $posx = $this->marge_gauche;
            }

            // Show recipient frame
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('', '', $default_font_size - 2);
            $pdf->SetXY($posx + 2, $posy - 5);
            $pdf->MultiCell($widthrecbox, 5, $outputlangs->transnoentities("BillTo"), 0, $ltrdirection);
            $pdf->Rect($posx, $posy, $widthrecbox, $hautcadre);

            // Show recipient name
            $pdf->SetXY($posx + 2, $posy + 3);
            $pdf->SetFont('', 'B', $default_font_size);
            $pdf->MultiCell($widthrecbox, 2, $carac_client_name, 0, $ltrdirection);

            $posy = $pdf->getY();

            // Show recipient information
            $pdf->SetFont('', '', $default_font_size - 1);
            $pdf->SetXY($posx + 2, $posy);
            $pdf->MultiCell($widthrecbox, 4, $carac_client, 0, $ltrdirection);
        }

        $pdf->SetTextColor(0, 0, 0);

        return $top_shift;
    }

    // phpcs:disable PEAR.NamingConventions.ValidFunctionName.PublicUnderscore
    /**
     * Show footer of page. Need this->emetteur object
     *
     * @param   TCPDF       $pdf            PDF
     * @param   InternalHTS $object         Object to show
     * @param   Translate   $outputlangs    Object lang for output
     * @param   int         $hidefreetext   1=Hide free text
     * @return  int                         Return height of bottom margin including footer text
     */
    protected function _pagefoot(&$pdf, $object, $outputlangs, $hidefreetext = 0)
    {
        $showdetails = getDolGlobalInt('MAIN_GENERATE_DOCUMENTS_SHOW_FOOT_DETAILS', 0);
        return pdf_pagefoot($pdf, $outputlangs, 'INTERNALHTS_FREE_TEXT', $this->emetteur, $this->marge_basse, $this->marge_gauche, $this->page_hauteur, $object, $showdetails, $hidefreetext);
    }

    /**
     * Define Array Column Field
     *
     * @param   InternalHTS $object         common object
     * @param   Translate   $outputlangs    langs
     * @param   int         $hidedetails    Do not show line details
     * @param   int         $hidedesc       Do not show desc
     * @param   int         $hideref        Do not show ref
     * @return  null
     */
    public function defineColumnField($object, $outputlangs, $hidedetails = 0, $hidedesc = 0, $hideref = 0)
    {
        global $conf, $hookmanager;

        // Default field style for content
        $this->defaultContentsFieldsStyle = array(
            'align' => 'R', // R,C,L
            'padding' => array(1, 0.5, 1, 0.5), // Like css 0 => top , 1 => right, 2 => bottom, 3 => left
        );

        // Default field style for content
        $this->defaultTitlesFieldsStyle = array(
            'align' => 'C', // R,C,L
            'padding' => array(0.5, 0, 0.5, 0), // Like css 0 => top , 1 => right, 2 => bottom, 3 => left
        );

        /*
         * For example
         * $this->cols['theColKey'] = array(
         *     'rank' => $rank, // int : use for ordering columns
         *     'width' => 20, // the column width in mm
         *     'title' => array(
         *         'textkey' => 'yourLangKey', // if there is no label, yourLangKey will be translated to replace label
         *         'label' => ' ', // the final label : used fore final generated text
         *         'align' => 'L', // text alignment :  R,C,L
         *         'padding' => array(0.5,0.5,0.5,0.5), // Like css 0 => top , 1 => right, 2 => bottom, 3 => left
         *     ),
         *     'content' => array(
         *         'align' => 'L', // text alignment :  R,C,L
         *         'padding' => array(0.5,0.5,0.5,0.5), // Like css 0 => top , 1 => right, 2 => bottom, 3 => left
         *     ),
         * );
         */

        $rank = 0; // do not use negative rank
        $this->cols['desc'] = array(
            'rank' => $rank,
            'width' => false, // only for desc
            'status' => true,
            'title' => array(
                'textkey' => 'Designation', // use lang key is usefull in somme case with module
                'align' => 'L',
                // 'textkey' => 'yourLangKey', // if there is no label, yourLangKey will be translated to replace label
                // 'label' => ' ', // the final label
                'padding' => array(0.5, 0.5, 0.5, 0.5), // Like css 0 => top , 1 => right, 2 => bottom, 3 => left
            ),
            'content' => array(
                'align' => 'L',
                'padding' => array(1, 0.5, 1, 1.5), // Like css 0 => top , 1 => right, 2 => bottom, 3 => left
            ),
        );

        $rank = $rank + 10;
        $this->cols['sku'] = array(
            'rank' => $rank,
            'width' => 20, // in mm
            'status' => false,
            'title' => array(
                'textkey' => 'SKU'
            ),
            'content' => array(
                'align' => 'L',
            ),
        );

        $rank = $rank + 10;
        $this->cols['hts'] = array(
            'rank' => $rank,
            'width' => 20, // in mm
            'status' => false,
            'title' => array(
                'textkey' => 'HTSCode'
            ),
            'content' => array(
                'align' => 'L',
            ),
        );

        $rank = $rank + 10;
        $this->cols['coo'] = array(
            'rank' => $rank,
            'width' => 16, // in mm
            'status' => false,
            'title' => array(
                'textkey' => 'CountryOfOrigin'
            ),
            'content' => array(
                'align' => 'C',
            ),
        );

        $rank = $rank + 10;
        $this->cols['qty'] = array(
            'rank' => $rank,
            'width' => 16, // in mm
            'status' => true,
            'title' => array(
                'textkey' => 'Qty'
            ),
            'content' => array(
                'align' => 'R',
            ),
        );

        $rank = $rank + 10;
        $this->cols['up'] = array(
            'rank' => $rank,
            'width' => 19, // in mm
            'status' => true,
            'title' => array(
                'textkey' => 'UnitPrice'
            ),
            'content' => array(
                'align' => 'R',
            ),
        );

        $rank = $rank + 10;
        $this->cols['customs'] = array(
            'rank' => $rank,
            'width' => 19, // in mm
            'status' => true,
            'title' => array(
                'textkey' => 'CustomsUnitValue'
            ),
            'content' => array(
                'align' => 'R',
            ),
        );

        $rank = $rank + 10;
        $this->cols['totalht'] = array(
            'rank' => $rank,
            'width' => 26, // in mm
            'status' => true,
            'title' => array(
                'textkey' => 'TotalHT'
            ),
            'content' => array(
                'align' => 'R',
            ),
        );

        // Add extrafields cols
        if (!empty($object->lines)) {
            $line = reset($object->lines);
            $this->defineColumnExtrafield($line, $outputlangs, $hidedetails);
        }

        $parameters = array(
            'object' => $object,
            'outputlangs' => $outputlangs,
            'hidedetails' => $hidedetails,
            'hidedesc' => $hidedesc,
            'hideref' => $hideref
        );

        $reshook = $hookmanager->executeHooks('defineColumnField', $parameters, $this); // Note that $object and $outputlangs may have been modified by hook
        if ($reshook < 0) {
            setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
        } elseif (empty($reshook)) {
            $this->insertNewColumnDef('totalht', $this->cols['totalht']);
        } else {
            $this->cols = $hookmanager->resArray;
        }
    }
}