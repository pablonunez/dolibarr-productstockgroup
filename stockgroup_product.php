<?php
/* Copyright (C) 2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2023 SuperAdmin
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *   	\file       stockgroup_card.php
 *		\ingroup    productstockgroup
 *		\brief      Page to create/edit/view stockgroup
 */

//if (! defined('NOREQUIREDB'))              define('NOREQUIREDB', '1');				// Do not create database handler $db
//if (! defined('NOREQUIREUSER'))            define('NOREQUIREUSER', '1');				// Do not load object $user
//if (! defined('NOREQUIRESOC'))             define('NOREQUIRESOC', '1');				// Do not load object $mysoc
//if (! defined('NOREQUIRETRAN'))            define('NOREQUIRETRAN', '1');				// Do not load object $langs
//if (! defined('NOSCANGETFORINJECTION'))    define('NOSCANGETFORINJECTION', '1');		// Do not check injection attack on GET parameters
//if (! defined('NOSCANPOSTFORINJECTION'))   define('NOSCANPOSTFORINJECTION', '1');		// Do not check injection attack on POST parameters
//if (! defined('NOCSRFCHECK'))              define('NOCSRFCHECK', '1');				// Do not check CSRF attack (test on referer + on token).
//if (! defined('NOTOKENRENEWAL'))           define('NOTOKENRENEWAL', '1');				// Do not roll the Anti CSRF token (used if MAIN_SECURITY_CSRF_WITH_TOKEN is on)
//if (! defined('NOSTYLECHECK'))             define('NOSTYLECHECK', '1');				// Do not check style html tag into posted data
//if (! defined('NOREQUIREMENU'))            define('NOREQUIREMENU', '1');				// If there is no need to load and show top and left menu
//if (! defined('NOREQUIREHTML'))            define('NOREQUIREHTML', '1');				// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))            define('NOREQUIREAJAX', '1');       	  	// Do not load ajax.lib.php library
//if (! defined("NOLOGIN"))                  define("NOLOGIN", '1');					// If this page is public (can be called outside logged session). This include the NOIPCHECK too.
//if (! defined('NOIPCHECK'))                define('NOIPCHECK', '1');					// Do not check IP defined into conf $dolibarr_main_restrict_ip
//if (! defined("MAIN_LANG_DEFAULT"))        define('MAIN_LANG_DEFAULT', 'auto');					// Force lang to a particular value
//if (! defined("MAIN_AUTHENTICATION_MODE")) define('MAIN_AUTHENTICATION_MODE', 'aloginmodule');	// Force authentication handler
//if (! defined("NOREDIRECTBYMAINTOLOGIN"))  define('NOREDIRECTBYMAINTOLOGIN', 1);		// The main.inc.php does not make a redirect if not logged, instead show simple error message
//if (! defined("FORCECSP"))                 define('FORCECSP', 'none');				// Disable all Content Security Policies
//if (! defined('CSRFCHECK_WITH_TOKEN'))     define('CSRFCHECK_WITH_TOKEN', '1');		// Force use of CSRF protection with tokens even for GET
//if (! defined('NOBROWSERNOTIF'))     		 define('NOBROWSERNOTIF', '1');				// Disable browser notification
//if (! defined('NOSESSION'))     		     define('NOSESSION', '1');				    // Disable session

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

require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
dol_include_once('/productstockgroup/class/stockgroup.class.php');
dol_include_once('/productstockgroup/lib/productstockgroup_stockgroup.lib.php');

// Load translation files required by the page
$langs->loadLangs(array("productstockgroup@productstockgroup", "other"));


// Get parameters
$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$lineid   = GETPOST('lineid', 'int');

$action = GETPOST('action', 'aZ09');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : str_replace('_', '', basename(dirname(__FILE__)).basename(__FILE__, '.php')); // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');
$dol_openinpopup = GETPOST('dol_openinpopup', 'aZ09');

// Initialize technical objects
$object = new StockGroup($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->productstockgroup->dir_output.'/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('stockgroupcard', 'globalcard')); // Note that conf->hooks_modules contains array

// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

$search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');

// Initialize array of search criterias
$search_all = GETPOST("search_all", 'alpha');
$search = array();
foreach ($object->fields as $key => $val) {
	if (GETPOST('search_'.$key, 'alpha')) {
		$search[$key] = GETPOST('search_'.$key, 'alpha');
	}
}

if (empty($action) && empty($id) && empty($ref)) {
	$action = 'view';
}

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php'; // Must be include, not include_once.

// There is several ways to check permission.
// Set $enablepermissioncheck to 1 to enable a minimum low level of checks
$enablepermissioncheck = 0;
if ($enablepermissioncheck) {
	$permissiontoread = $user->rights->productstockgroup->stockgroup->read;
	$permissiontoadd = $user->rights->productstockgroup->stockgroup->write; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
	$permissiontodelete = $user->rights->productstockgroup->stockgroup->delete || ($permissiontoadd && isset($object->status) && $object->status == $object::STATUS_DRAFT);
	$permissionnote = $user->rights->productstockgroup->stockgroup->write; // Used by the include of actions_setnotes.inc.php
	$permissiondellink = $user->rights->productstockgroup->stockgroup->write; // Used by the include of actions_dellink.inc.php
} else {
	$permissiontoread = 1;
	$permissiontoadd = 1; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
	$permissiontodelete = 1;
	$permissionnote = 1;
	$permissiondellink = 1;
}

$upload_dir = $conf->productstockgroup->multidir_output[isset($object->entity) ? $object->entity : 1].'/stockgroup';

// Security check (enable the most restrictive one)
//if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
//$isdraft = (isset($object->status) && ($object->status == $object::STATUS_DRAFT) ? 1 : 0);
//restrictedArea($user, $object->element, $object->id, $object->table_element, '', 'fk_soc', 'rowid', $isdraft);
if (empty($conf->productstockgroup->enabled)) accessforbidden();
if (!$permissiontoread) accessforbidden();


/*
 * Actions
 */

 $parameters = array();
 $reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
 if ($reshook < 0) {
	 setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
 }

 
if (empty($reshook)) {
	$error = 0;

	$backurlforlist = dol_buildpath('/productstockgroup/stockgroup_list.php', 1);

	if (empty($backtopage) || ($cancel && empty($id))) {
		if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
			if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) {
				$backtopage = $backurlforlist;
			} else {
				$backtopage = dol_buildpath('/productstockgroup/stockgroup_card.php', 1).'?id='.((!empty($id) && $id > 0) ? $id : '__ID__');
			}
		}
	}

	$triggermodname = 'PRODUCTSTOCKGROUP_STOCKGROUP_MODIFY'; // Name of trigger action code to execute when we modify record

	// Actions cancel, add, update, update_extras, confirm_validate, confirm_delete, confirm_deleteline, confirm_clone, confirm_close, confirm_setdraft, confirm_reopen
	include DOL_DOCUMENT_ROOT.'/core/actions_addupdatedelete.inc.php';

	// Actions when linking object each other
	include DOL_DOCUMENT_ROOT.'/core/actions_dellink.inc.php';

	// Actions when printing a doc from card
	include DOL_DOCUMENT_ROOT.'/core/actions_printing.inc.php';

	// Action to move up and down lines of object
	//include DOL_DOCUMENT_ROOT.'/core/actions_lineupdown.inc.php';

	// Action to build doc
	include DOL_DOCUMENT_ROOT.'/core/actions_builddoc.inc.php';

	if ($action == 'set_thirdparty' && $permissiontoadd) {
		$object->setValueFrom('fk_soc', GETPOST('fk_soc', 'int'), '', '', 'date', '', $user, $triggermodname);
	}
	if ($action == 'classin' && $permissiontoadd) {
		$object->setProject(GETPOST('projectid', 'int'));
	}

	// Actions to send emails
	$triggersendname = 'PRODUCTSTOCKGROUP_STOCKGROUP_SENTBYMAIL';
	$autocopy = 'MAIN_MAIL_AUTOCOPY_STOCKGROUP_TO';
	$trackid = 'stockgroup'.$object->id;
	include DOL_DOCUMENT_ROOT.'/core/actions_sendmails.inc.php';
}


/*
 * View
 *
 * Put here all code to build page
 */

 $form = new Form($db);
 $formfile = new FormFile($db);
 $formproject = new FormProjets($db);
 $tmpproduct = new Product($db);

 $title = $langs->trans("StockGroup");
 $help_url = '';
 llxHeader('', $title, $help_url);
 

$limit = GETPOST('limit', 'int') ?GETPOST('limit', 'int') : $conf->liste_limit;
$sortfield = GETPOST('sortfield', 'aZ09comma');
$sortorder = GETPOST('sortorder', 'aZ09comma');
$page = GETPOSTISSET('pageplusone') ? (GETPOST('pageplusone') - 1) : GETPOST("page", 'int');
$param = '&id='.$object->id;
if (empty($page) || $page == -1) {
	$page = 0;
}     // If $page is not defined, or '' or -1
if (!$sortfield) {
	$sortfield = "label";
}
if (!$sortorder) {
	$sortorder = "DESC";
}
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;


// Part to show record
if ($object->id > 0 && (empty($action) || ($action != 'edit' && $action != 'create'))) {
	$res = $object->fetch_optionals();

	$head = stockgroupPrepareHead($object);
	print dol_get_fiche_head($head, 'product', $langs->trans("StockGroup"), -1, $object->picto);

	// Array of liens to show
	$infoprod = array();
	
	// Add lines for object
	$sql = "SELECT p.rowid, p.label as label, p.ref, p.fk_product_type  as type, p.stock, p.seuil_stock_alerte ";
	$sql .= " FROM ".MAIN_DB_PREFIX."product as p ";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."product_extrafields as ef ON p.rowid = ef.fk_object ";
	$sql .= ' WHERE ef.stock_group = ' .$id;

	
	$num = 0;
	$totalnboflines = 0;

	$result = $db->query($sql);
	if ($result) {
		$totalnboflines = $db->num_rows($result);
	}
	$sql .= $db->order($sortfield, $sortorder);
	$sql .= $db->plimit($limit , $offset);
	
	$resql = $db->query($sql);
	if ($resql) {	
		$num = $db->num_rows($resql);
		
		$i = 0;

		while ($i < $num ) {
			$objp = $db->fetch_object($resql);

			$infoprod[$objp->rowid] = array('ref'=>$objp->ref, 'label'=>$objp->label, 'stock'=>$objp->stock, 'seuil_stock_alerte'=>$objp->seuil_stock_alerte);

			$i++;
		}
		$db->free($resql);
	} else {
		dol_print_error($db);
	}

	$stocktotal = 0;

	$sqlcount = "SELECT sum(p.stock) as totalstock ";
	$sqlcount .= " FROM ".MAIN_DB_PREFIX."product as p ";
	$sqlcount .= " LEFT JOIN ".MAIN_DB_PREFIX."product_extrafields as ef ON p.rowid = ef.fk_object ";
	$sqlcount .= ' WHERE ef.stock_group = ' .$id;

	$resultcount = $db->query($sqlcount);
	
	if ($resultcount) {
		$objforcount = $db->fetch_object($resultcount);
		$stocktotal = $objforcount->totalstock;
	} else {
		dol_print_error($db);
	}

	// update group stock
	$sqlupdate = " UPDATE ".MAIN_DB_PREFIX."productstockgroup_stockgroup as sf ";
	$sqlupdate .= ' SET sf.seuil_stock = ' .$stocktotal;
	$sqlupdate .= ' WHERE sf.rowid = ' .$id;
	$db->query($sqlupdate);


	print '<form method="GET" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="id" value="'.$object->id.'">';

	print_barre_liste(
		$langs->trans("ProductsOfStockGroup"), 	// titre
	 	$page, 						// page 
		$_SERVER["PHP_SELF"], 		// file
		$param, 					// options
		$sortfield, 				// sortfield
		$sortorder, 				// sortorder
		"", 						// morehtmlcenter
		$num + 1, 						// num
		$totalnboflines, 			// totalnboflines
		'', 						// picto
		0, 							// pictoisfullpath
		'', 						// morehtmlright
		'', 						// morecss
		$limit, 					// limit
		0, 							// hideselectlimit
		0, 							// hidenavigation
		1							// morehtmlrightbeforearrow
	);

	

	print '<table class="noborder centpercent">';

	print '<tr class="liste_titre">';
	print_liste_field_titre('Ref', $_SERVER["PHP_SELF"], 'p.ref', '', $param, '', $sortfield, $sortorder);
	print_liste_field_titre('Label', $_SERVER["PHP_SELF"], 'p.label', '', $param, '', $sortfield, $sortorder);
	print_liste_field_titre('Stock real', $_SERVER["PHP_SELF"], 'p.stock', '', $param, '', $sortfield, $sortorder);
	print_liste_field_titre('Stock límite para alertas', $_SERVER["PHP_SELF"], 'p.seuil_stock_alerte', '', $param, '', $sortfield, $sortorder);
	print "</tr>\n";

	foreach ($infoprod as $prodid => $vals) {

		$tmpproduct->id = $prodid;
		$tmpproduct->ref = $vals['ref'];
		$tmpproduct->label = $vals['label'];
		$tmpproduct->stock_reel = $vals['stock'];
		$tmpproduct->seuil_stock_alerte = $vals['seuil_stock_alerte'];

		print "<tr>";
		print '<td>';
		print $tmpproduct->getNomUrl(1);
		print '</td>';
		print '<td>';
		print dol_escape_htmltag($vals['label']);
		print '</td>';
		print '<td>';
		print $tmpproduct->stock_reel;
		print '</td>';
		print '<td>';
		if($tmpproduct->seuil_stock_alerte > 0){
			print $tmpproduct->seuil_stock_alerte;
		}
		print '</td>';
		print "</tr>\n";
	}

	print "</table>";

	print '</form>';

	print dol_get_fiche_end();
}

 // End of page
llxFooter();
$db->close();
