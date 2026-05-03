<?php
/**
 *  \file       htdocs/custom/furs/core/modules/modFurs.class.php
 *  \ingroup    furs
 *  \brief      Description and activation file for the module FURS
 */

include_once DOL_DOCUMENT_ROOT .'/core/modules/DolibarrModules.class.php';

class modFurs extends DolibarrModules
{
    /**
     *   Constructor. Define names, constants, directories, boxes, permissions
     *
     *   @param      DoliDB      $db      Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;
        $this->numero = 105000;
        $this->rights_class = 'furs';
        $this->family = 'financial';
        $this->module_position = 50;
        $this->name = preg_replace('/^mod/i', '', get_class($this));
        $this->description = "Davčno potrjevanje računov (FURS Slovenija)";
        $this->version = '1.0.0';
        $this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
        $this->picto = 'technic';

        // Data directories to create when module is enabled
        $this->dirs = array("/furs/class", "/furs/admin", "/furs/sql", "/furs/core/triggers");
        $this->config_page_url = array("setup.php@furs");

        $this->depends = array('modFacture');
        $this->requiredby = array();
        $this->phpmin = array(7,4);
        $this->need_dolibarr_version = array(15,0);

        // Constants
        $this->const = array(
            0 => array('FURS_ENVIRONMENT', 'chaine', 'test', 'Test or production environment for FURS', 0),
            1 => array('FURS_VALIDATION_MODE', 'chaine', 'cash_only', 'Validate all or cash_only', 0),
            2 => array('FURS_P12_PASSWORD', 'chaine', '', 'Password for p12 certificate', 0)
        );

        // Rights
        $this->rights = array();
        $this->rights[1][0] = array('105001', 'Preberi FURS loge', 'r');
        $this->rights[2][0] = array('105002', 'Upravljaj FURS nastavitve', 'm');
    }

    /**
     *  Function called when module is enabled.
     *  Create tables, directories, constants, etc.
     *
     *  @param      string      $options    Options when enabling module ('', 'noboxes')
     *  @return     int                     1 if OK, 0 if KO
     */
    public function init($options = '')
    {
        $sql = array();
        $result = $this->load_tables();
        return $this->_init($sql, $options);
    }
}
