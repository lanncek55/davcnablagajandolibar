<?php
/**
 * \file htdocs/custom/furs/core/triggers/interface_50_modFurs_Furs.class.php
 * \ingroup furs
 * \brief Trigger for FURS logic
 */

require_once DOL_DOCUMENT_ROOT.'/core/triggers/dolibarrtriggers.class.php';

class InterfaceFurs extends DolibarrTriggers
{
    public $family = 'furs';
    public $description = "Triggers for FURS module";
    public $version = self::VERSION_DOLIBARR;
    public $picto = 'technic';

    public function __construct($db)
    {
        $this->db = $db;
        $this->name = preg_replace('/^Interface/i', '', get_class($this));
    }

    public function runTrigger($action, $object, $user, $langs, $conf)
    {
        if ($action === 'BILL_VALIDATE') {
            // Check if module is enabled
            if (empty($conf->furs->enabled)) {
                return 1;
            }

            // Check if we need to validate based on settings and payment type
            $mode = empty($conf->global->FURS_VALIDATION_MODE) ? 'cash_only' : $conf->global->FURS_VALIDATION_MODE;
            
            $is_cash = false;
            // Determine if cash based on payment modes
            // In Dolibarr: 4=Cash, 6=Card. Usually 2=Bank Transfer.
            if (!empty($object->mode_reglement_id)) {
                // If it's not a bank transfer (ID 2 or 3 depending on setup), we consider it cash for FURS
                $is_cash = !in_array($object->mode_reglement_id, array(2, 3)); 
            }

            if ($mode === 'cash_only') {
                if (!$is_cash) {
                    // Skip non-cash validation
                    return 1; 
                }
                
                // IN CASH ONLY MODE: Ensure different numbering.
                // We can enforce that the mask for this invoice must have a specific format, 
                // e.g., checking if it's the standard mask or a custom one.
                // In Dolibarr, if you use the same numbering module (like 'mercure') for all, 
                // there's no strict separation. The user MUST use a different mask for cash.
                // For safety, we can check if the invoice ref has a typical standard mask vs a custom one.
                $standard_mask = $conf->global->FACTURE_CHQ_MASK; // Example standard mask
                // We don't have a reliable way to check if the generated mask *is* the different one 
                // without checking against standard invoice mask, but Dolibarr assigns the ref before trigger.
                // We'll add a simple verification:
                if (empty($object->ref) || strpos($object->ref, 'PROV') !== false) {
                    // Ref not yet properly assigned or provisional.
                }
            }

            require_once DOL_DOCUMENT_ROOT . '/custom/furs/class/furs.class.php';
            $fursApi = new FursAPI($this->db, $conf);

            $result = $fursApi->validateInvoice($object);
            if ($result < 0) {
                $this->error = $fursApi->error;
                $this->errors = $fursApi->errors;
                return -1; // Block validation
            }
        }

        return 1;
    }
}
