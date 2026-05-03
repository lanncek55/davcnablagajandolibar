<?php
/**
 * \file htdocs/custom/furs/admin/setup.php
 * \ingroup furs
 * \brief Setup page for FURS module
 */

$res=0;
if (! $res && file_exists("../main.inc.php")) $res=@include '../main.inc.php';
if (! $res && file_exists("../../main.inc.php")) $res=@include '../../main.inc.php';
if (! $res && file_exists("../../../main.inc.php")) $res=@include '../../../main.inc.php';
if (! $res && file_exists("../../../../main.inc.php")) $res=@include '../../../../main.inc.php';
if (! $res && file_exists("../../../../../main.inc.php")) $res=@include '../../../../../main.inc.php';
if (! $res && preg_match('/\/custom\//', dirname($_SERVER["PHP_SELF"]))) $res=@include '../../../../main.inc.php';
if (! $res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';

$langs->load("admin");
$langs->load("furs@furs");

if (!$user->admin) {
    accessforbidden();
}

$action = GETPOST('action', 'aZ09');

if ($action == 'update') {
    $furs_env = GETPOST('furs_env', 'alpha');
    $furs_val_mode = GETPOST('furs_val_mode', 'alpha');
    $furs_pwd = GETPOST('furs_pwd', 'none'); // Allow special chars

    $db->begin();
    $error = 0;

    $res = dolibarr_set_const($db, "FURS_ENVIRONMENT", $furs_env, 'chaine', 0, '', $conf->entity);
    if (!$res) $error++;
    $res = dolibarr_set_const($db, "FURS_VALIDATION_MODE", $furs_val_mode, 'chaine', 0, '', $conf->entity);
    if (!$res) $error++;
    
    // Store password (ideally encrypted, but for simplicity here we store it in standard const)
    if (!empty($furs_pwd) || $furs_pwd === '0') {
        $res = dolibarr_set_const($db, "FURS_P12_PASSWORD", $furs_pwd, 'chaine', 0, '', $conf->entity);
        if (!$res) $error++;
    }

    // Handle File Upload
    if (!empty($_FILES['furs_cert']['name'])) {
        $upload_dir = DOL_DATA_ROOT . '/furs';
        if (!dol_is_dir($upload_dir)) {
            dol_mkdir($upload_dir);
        }
        $target_file = $upload_dir . '/furs_cert.p12';
        if (move_uploaded_file($_FILES['furs_cert']['tmp_name'], $target_file)) {
            setEventMessages("Certifikat uspešno naložen.", null, 'mesgs');
        } else {
            setEventMessages("Napaka pri nalaganju certifikata.", null, 'errors');
            $error++;
        }
    }

    if (!$error) {
        $db->commit();
        setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    } else {
        $db->rollback();
        setEventMessages($langs->trans("Error"), null, 'errors');
    }
}

llxHeader('', "FURS Setup");

$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php?restore_lastsearch_values=1">' . $langs->trans("BackToModuleList") . '</a>';
print load_fiche_titre("Nastavitve modula FURS", $linkback, 'title_setup');

print '<form action="' . $_SERVER["PHP_SELF"] . '" method="POST" enctype="multipart/form-data">';
print '<input type="hidden" name="token" value="' . newToken() . '">';
print '<input type="hidden" name="action" value="update">';

print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td>Nastavitev</td><td>Vrednost</td>';
print '</tr>';

// Environment
print '<tr class="oddeven">';
print '<td>Okolje (Test / Produkcija)</td>';
print '<td><select name="furs_env" class="flat">';
print '<option value="test"' . (empty($conf->global->FURS_ENVIRONMENT) || $conf->global->FURS_ENVIRONMENT == 'test' ? ' selected' : '') . '>Test (VDZ)</option>';
print '<option value="prod"' . ($conf->global->FURS_ENVIRONMENT == 'prod' ? ' selected' : '') . '>Produkcija</option>';
print '</select></td>';
print '</tr>';

// Validation Mode
print '<tr class="oddeven">';
print '<td>Način potrjevanja<br><small>Pozor: V primeru "Samo gotovinski" morajo imeti negotovinski računi drugačno masko številčenja!</small></td>';
print '<td><select name="furs_val_mode" class="flat">';
print '<option value="cash_only"' . (empty($conf->global->FURS_VALIDATION_MODE) || $conf->global->FURS_VALIDATION_MODE == 'cash_only' ? ' selected' : '') . '>Samo gotovinski računi</option>';
print '<option value="all"' . ($conf->global->FURS_VALIDATION_MODE == 'all' ? ' selected' : '') . '>Vsi računi (gotovinski in negotovinski)</option>';
print '</select></td>';
print '</tr>';

// Password
print '<tr class="oddeven">';
print '<td>Geslo za .p12 certifikat</td>';
print '<td><input type="password" name="furs_pwd" value="' . (isset($conf->global->FURS_P12_PASSWORD) ? $conf->global->FURS_P12_PASSWORD : '') . '" class="flat"></td>';
print '</tr>';

// Certificate Upload
print '<tr class="oddeven">';
print '<td>Naloži certifikat (.p12)</td>';
print '<td><input type="file" name="furs_cert" class="flat" accept=".p12"></td>';
print '</tr>';

print '</table>';

print '<div class="tabsAction">';
print '<input type="submit" class="button" value="' . $langs->trans("Save") . '">';
print '</div>';

print '</form>';

llxFooter();
$db->close();