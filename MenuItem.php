<?php

namespace dokuwiki\plugin\ismsaddons;

use dokuwiki\Menu\Item\AbstractItem;

/**
 * Class MenuItem
 *
 * Implements the risks refresh button
 *
 * @package dokuwiki\plugin\ismsaddons
 */
class MenuItem extends AbstractItem {

    /** @var string do action for this plugin */
    protected $type = 'update_risk_data';

    /** @var string icon file */
    protected $svg = __DIR__ . '/icon.svg';

    /**
     * MenuItem constructor.
     */
    public function __construct() {
        parent::__construct();
        global $REV;
        if($REV) $this->params['rev'] = $REV;
    }

    /**
     * Get label from plugin language file
     *
     * @return string
     */
    public function getLabel() {
/*        $hlp = plugin_load('action', 'dw2pdf');
        return $hlp->getLang('export_pdf_button'); */
	return "Update risk data";
    }
}

