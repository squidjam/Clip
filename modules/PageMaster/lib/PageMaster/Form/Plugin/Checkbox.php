<?php
/**
 * PageMaster
 *
 * @copyright   (c) PageMaster Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @version     $ Id $
 * @package     Zikula_3rdParty_Modules
 * @subpackage  pagemaster
 */

class PageMaster_Form_Plugin_Checkbox extends Form_Plugin_Checkbox
{
    public $pluginTitle;
    public $columnDef = 'L';

    function setup()
    {
        $dom = ZLanguage::getModuleDomain('PageMaster');
        $this->setDomain($dom);

        //! field type name
        $this->pluginTitle = $this->__('Checkbox');
    }

    function getFilename()
    {
        return __FILE__;
    }
}
