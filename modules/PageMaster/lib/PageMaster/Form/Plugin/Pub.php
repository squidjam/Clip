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

class PageMaster_Form_Plugin_Pub extends Form_Plugin_DropdownList
{
    public $columnDef = 'I4';
    public $title;

    public $config;

    function __construct()
    {
        $dom = ZLanguage::getModuleDomain('PageMaster');
        //! field type name
        $this->title = __('Publication', $dom);

        parent::__construct();
    }

    function getFilename()
    {
        return __FILE__;
    }

    function postRead($data, $field)
    {
        $dom = ZLanguage::getModuleDomain('PageMaster');

        $this->parseConfig($field['typedata']);

        $pub = array();

        if (!empty($this->config['tid']) && !empty($data)) {
            $pub = ModUtil::apiFunc('PageMaster', 'user', 'get',
                                array('tid'                => $this->config['tid'],
                                      'pid'                => (int)$data,
                                      'checkPerm'          => true,
                                      'getApprovalState'   => true,
                                      'handlePluginFields' => true));

            if (!$pub) {
                $pub = array('core_error' => __('No such publication found.', $dom));
            }
        }

        return $pub;    
    }

    function load($view)
    {
        $dom = ZLanguage::getModuleDomain('PageMaster');

        $this->parseConfig($view->eventHandler->pubfields[$this->id]['typedata']);

        if (!empty($this->config['tid'])) {
            $pubarr = ModUtil::apiFunc('PageMaster', 'user', 'getall',
                                   array('tid'                => $this->config['tid'],
                                         'countmode'          => 'no',
                                         'filter'             => $this->config['filter'],
                                         'orderby'            => $this->config['orderby'],
                                         'checkPerm'          => true,
                                         'handlePluginFields' => false));

            $titleField = PageMaster_Util::getTitleField($this->config['tid']);

            $items = array();
            $items[] = array('text'  => '- - -',
                             'value' => '');

            foreach ($pubarr['publist'] as $pub ) {
                $items[] = array('text'  => $pub[$titleField],
                                 'value' => $pub['core_pid']);
            }
            $this->items = $items;
        } else {
            $this->items = array(
                               array('text'  => __('Plugin not configured.', $dom),
                                     'value' => '')
                           );
        }

        parent::load($view);
    }

    static function getSaveTypeDataFunc($field)
    {
        // TODO Implement effects for the checkbox enabled
        // TODO Implement postBack to check if the fields are correct?
        $saveTypeDataFunc = 'function saveTypeData()
                             {
                                 $(\'typedata\').value = $F(\'pmplugin_pubtid\')+\';\'+$F(\'pmplugin_pubfilter\')+\';\'+$F(\'pmplugin_pubjoin\')+\';\'+$F(\'pmplugin_pubjoinfields\')+\';\'+$F(\'pmplugin_puborderbyfield\');
                                 closeTypeData();
                             }';

        return $saveTypeDataFunc;
    }

    function getTypeHtml($field, $view)
    {
        $dom = ZLanguage::getModuleDomain('PageMaster');

        $typedata = isset($view->_tpl_vars['typedata']) ? $view->_tpl_vars['typedata'] : '';
        $this->parseConfig($typedata);

        $pubtypes = DBUtil::selectFieldArray('pagemaster_pubtypes', 'title', '', '', false, 'tid');
        foreach ($pubtypes as $tid => $title) {
            $pubtypes[$tid] = __($title, $dom);
        }
        asort($pubtypes);

        $html = ' <div class="z-formrow">
                      <label for="pmplugin_pubtid">'.__('Publication', $dom).':</label>
                      <select id="pmplugin_pubtid" name="pmplugin_pubtid">';

        foreach ($pubtypes as $tid => $title) {
            $selectedText = ($tid == $this->config['tid']) ? 'selected="selected"' : '';

            $html .= "<option{$selectedText} value=\"{$tid}\">{$title}</option>\n";
        }

        $html .= '    </select>
                  </div>
                  <div class="z-formrow">
                      <label for="pmplugin_pubfilter">'.__('Filter', $dom).':</label>
                      <input type="text" id="pmplugin_pubfilter" name="pmplugin_pubfilter" value="'.$this->config['filter'].'" />
                  </div>
                  <div class="z-formrow">
                      <label for="pmplugin_pubjoin">'.__('Join', $dom).':</label>
                      <input type="checkbox" id="pmplugin_pubjoin" name="pmplugin_pubjoin" '.($this->config['join'] == 'on' ? 'checked="checked"' : '').' />
                  </div>
                  <div class="z-formrow">
                      <label for="pmplugin_pubjoinfields">'.__('Join fields', $dom).':</label>
                      <input type="text" id="pmplugin_pubjoinfields" name="pmplugin_pubjoinfields" value="'.$this->config['alias'].'" >
                      <span class="z-formnote z-sub">'.__('format: fieldname:alias,fieldname:alias', $dom).'</span>
                  </div>
                  <div class="z-formrow">
                      <label for="pmplugin_puborderbyfield">'.__('Orderby field', $dom).':</label>
                      <input type="text" id="pmplugin_puborderbyfield" name="pmplugin_puborderbyfield" value="'.$this->config['orderby'].'" >
                  </div>';

        return $html;
    }

    /**
     * Parse configuration
     */
    function parseConfig($typedata='', $args=array())
    {
        $this->config = explode(';', $typedata);

        $this->config = array(
            'tid'     => (int)$this->config[0],
            'filter'  => isset($this->config[1]) ? $this->config[1] : '',
            'join'    => isset($this->config[2]) ? $this->config[2] : '',
            'alias'   => isset($this->config[3]) ? $this->config[3] : '',
            'orderby' => isset($this->config[4]) ? $this->config[4] : ''
        );
    }
}
