<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage View_Plugins
 */

class ClipFormPluginType extends Zikula_Form_Plugin_DropdownList
{
    function getFilename()
    {
        return __FILE__;
    }

    function __construct($view, &$params)
    {
        $this->autoPostBack = true;
        $plugins = Clip_Util::getPluginsOptionList();

        foreach ($plugins as $id => $plugin) {
            $items[] = array (
                'text'  => $plugin['plugin']->pluginTitle,
                'value' => $id
            );
        }
        $this->items = $items;

        parent::__construct($view, $params);
    }

    function setup()
    {
        $this->setDomain(ZLanguage::getModuleDomain('Clip'));
    }

    function render($render)
    {
        $this->cssClass = strpos($this->cssClass, 'clip-plugintypeselector') === false ? $this->cssClass.' clip-plugintypeselector' : $this->cssClass;
        $result = parent::render($render);

        $typeDataHtml = '';
        if (!empty($this->selectedValue) && !empty($this->items)) {
            PageUtil::addVar('javascript', 'zikula.ui');
            $script =  "<script type=\"text/javascript\">\n//<![CDATA[\n";
            $plugin = Clip_Util::getPlugin($this->selectedValue);
            if (method_exists($plugin, 'getTypeHtml')) {
                if (method_exists($plugin, 'getSaveTypeDataFunc')) {
                    $script .= $plugin->getSaveTypeDataFunc($this)."\n";
                } else {
                    $script .= 'function saveTypeData() { closeTypeData(); }'."\n";
                }
                // init functions for modalbox and unobtrusive buttons
                $script .= '
                var clip_pluginwindow = null;
                var clip_plugincallback = function(button) {
                    switch (button.name) {
                        case \'save\':
                            saveTypeData();
                            break;
                        case \'cancel\':
                            closeTypeData();
                    }
                }
                function closeTypeData() {
                    clip_pluginwindow.closeHandler();
                }
                function clip_enablePluginConfig() {
                    clip_pluginwindow = new Zikula.UI.Dialog($(\'showTypeButton\'),
                                                             [
                                                                 {name:\'save\', class:\'z-bt-ok z-btgreen\', label:\''.$this->__('Save').'\'},
                                                                 {name:\'cancel\', class:\'z-bt-cancel z-btred\', label:\''.$this->__('Cancel').'\'}
                                                             ],
                                                             {callback: clip_plugincallback, modal:true, title:\''.$this->__('Plugin configuration').'\', width: 600, overlayOpacity: 0.6});
                }
                Event.observe( window, \'load\', clip_enablePluginConfig, false);
                ';

                $typeDataHtml  = '
                <a id="showTypeButton" class="tooltips" href="#typeDataDiv" title="'.$this->__('Open the plugin configuration popup').'"><img src="images/icons/extrasmall/configure.png" alt="'.$this->__('Configuration').'" /></a>
                <div id="typeDataDiv" class="z-form" style="display: none">
                    '.$plugin->getTypeHtml($this, $render).'
                </div>';
            } else {
                $script .= 'Event.observe( window, \'load\', function() { $(\'typedata_wrapper\').hide(); }, false);';
            }
            $script .= "\n// ]]>\n</script>";
            PageUtil::addVar('header', $script);
        }

        return $result . $typeDataHtml;
    }
}

function smarty_function_clip_form_plugintype($params, &$render) {
    return $render->registerPlugin('ClipFormPluginType', $params);
}
