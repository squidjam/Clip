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

class PageMaster_Form_Plugin_Int extends Form_Plugin_IntInput
{
    public $pluginTitle;
    public $columnDef = 'I4';

    function setup()
    {
        $dom = ZLanguage::getModuleDomain('PageMaster');
        $this->setDomain($dom);

        //! field type name
        $this->pluginTitle = $this->__('Integer Value');
    }

    function getFilename()
    {
        return __FILE__;
    }
}
