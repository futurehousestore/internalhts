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
 * \file    internalhts/css/internalhts.css.php
 * \ingroup internalhts
 * \brief   CSS file for module InternalHTS.
 */

//if (! defined('NOREQUIREUSER')) define('NOREQUIREUSER','1');	// Not disabled because need to load personalized language
//if (! defined('NOREQUIREDB'))   define('NOREQUIREDB','1');	// Not disabled. Language code is found on url.
if (!defined('NOREQUIRESOC')) {
	define('NOREQUIRESOC', '1');
}
//if (! defined('NOREQUIRETRAN')) define('NOREQUIRETRAN','1');	// Not disabled because need to do translations
if (!defined('NOCSRFCHECK')) {
	define('NOCSRFCHECK', 1);
}
if (!defined('NOTOKENRENEWAL')) {
	define('NOTOKENRENEWAL', 1);
}
if (!defined('NOLOGIN')) {
	define('NOLOGIN', 1);
}
if (!defined('NOREQUIREMENU')) {
	define('NOREQUIREMENU', 1);
}
if (!defined('NOREQUIREHTML')) {
	define('NOREQUIREHTML', 1);
}
if (!defined('NOREQUIREAJAX')) {
	define('NOREQUIREAJAX', '1');
}

session_cache_limiter('public');

require_once '../../main.inc.php';

// Define css type
header('Content-type: text/css');
// Important: Following code is to cache this file to avoid page request by browser at each Dolibarr page access.
// You can use CTRL+F5 to refresh your browser cache.
if (empty($dolibarr_nocache)) {
	header('Cache-Control: max-age=10800, public, must-revalidate');
} else {
	header('Cache-Control: no-cache');
}

?>

/* CSS for module InternalHTS */

.internalhts-invoice-card {
	margin: 0;
	padding: 0;
}

.internalhts-invoice-header {
	background: <?php echo $conf->global->THEME_ELDY_BACKBODY ? $conf->global->THEME_ELDY_BACKBODY : '#f8f9fa'; ?>;
	border: 1px solid #ddd;
	border-radius: 4px;
	margin-bottom: 20px;
	padding: 15px;
}

.internalhts-invoice-line {
	border-bottom: 1px solid #eee;
	padding: 10px 0;
}

.internalhts-invoice-line:last-child {
	border-bottom: none;
}

.internalhts-hts-code {
	font-family: monospace;
	font-weight: bold;
	color: #0066cc;
}

.internalhts-country-flag {
	width: 20px;
	height: auto;
	margin-right: 5px;
	vertical-align: middle;
}

.internalhts-weight {
	color: #666;
	font-size: 0.9em;
}

.internalhts-customs-value {
	color: #008000;
	font-weight: bold;
}

.internalhts-total-section {
	background: #f9f9f9;
	border: 1px solid #ddd;
	border-radius: 4px;
	margin-top: 20px;
	padding: 15px;
}

.internalhts-status-draft {
	color: #666;
}

.internalhts-status-validated {
	color: #008000;
}

.internalhts-status-paid {
	color: #0066cc;
}

.internalhts-status-abandoned {
	color: #cc0000;
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
	.internalhts-invoice-header {
		background: #2d3748;
		border-color: #4a5568;
		color: #e2e8f0;
	}
	
	.internalhts-total-section {
		background: #2d3748;
		border-color: #4a5568;
		color: #e2e8f0;
	}
	
	.internalhts-invoice-line {
		border-bottom-color: #4a5568;
	}
}

/* Force dark mode when enabled */
<?php if (!empty($conf->global->THEME_DARK_MODE)) { ?>
.internalhts-invoice-header {
	background: #2d3748 !important;
	border-color: #4a5568 !important;
	color: #e2e8f0 !important;
}

.internalhts-total-section {
	background: #2d3748 !important;
	border-color: #4a5568 !important;
	color: #e2e8f0 !important;
}

.internalhts-invoice-line {
	border-bottom-color: #4a5568 !important;
}
<?php } ?>

/* Responsive design */
@media (max-width: 768px) {
	.internalhts-invoice-header,
	.internalhts-total-section {
		margin-left: -10px;
		margin-right: -10px;
		border-radius: 0;
	}
}

/* HTS mapping specific styles */
.hts-mapping-card {
	margin: 0;
	padding: 0;
}

.hts-mapping-product {
	font-weight: bold;
	color: #0066cc;
}

.hts-mapping-code {
	font-family: monospace;
	background: #f5f5f5;
	padding: 2px 4px;
	border-radius: 2px;
}

.hts-mapping-inactive {
	opacity: 0.6;
	color: #999;
}

/* Export buttons */
.internalhts-export-buttons {
	margin: 20px 0;
	text-align: center;
}

.internalhts-export-buttons .button {
	margin: 0 5px;
}

/* Form enhancements */
.internalhts-form-section {
	margin: 20px 0;
	padding: 15px;
	border: 1px solid #ddd;
	border-radius: 4px;
}

.internalhts-form-section h3 {
	margin-top: 0;
	margin-bottom: 15px;
	padding-bottom: 8px;
	border-bottom: 1px solid #eee;
}

.internalhts-field-help {
	font-size: 0.9em;
	color: #666;
	font-style: italic;
	margin-top: 2px;
}

/* Table enhancements */
.internalhts-table th {
	background: #f8f9fa;
	font-weight: bold;
}

.internalhts-table .center {
	text-align: center;
}

.internalhts-table .right {
	text-align: right;
}

/* Loading indicator */
.internalhts-loading {
	text-align: center;
	padding: 20px;
	color: #666;
}

.internalhts-loading:after {
	content: '';
	display: inline-block;
	width: 20px;
	height: 20px;
	border: 2px solid #f3f3f3;
	border-top: 2px solid #3498db;
	border-radius: 50%;
	animation: spin 1s linear infinite;
	margin-left: 10px;
}

@keyframes spin {
	0% { transform: rotate(0deg); }
	100% { transform: rotate(360deg); }
}