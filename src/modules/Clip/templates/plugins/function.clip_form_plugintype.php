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

class ClipFormPluginType extends Form_Plugin_DropdownList
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

    function render($render)
    {
        $this->cssClass = strpos($this->cssClass, 'clip-plugintypeselector') === false ? $this->cssClass.' clip-plugintypeselector' : $this->cssClass;
        $result = parent::render($render);

        $typeDataHtml = '';
        if (!empty($this->selectedValue) && !empty($this->items)) {
            PageUtil::addVar('javascript', 'livepipe');
            $script =  "<script type=\"text/javascript\">\n//<![CDATA[\n";
            $plugin = Clip_Util::getPlugin($this->selectedValue);
            if (method_exists($plugin, 'getTypeHtml'))
            {
                if (method_exists($plugin, 'getSaveTypeDataFunc')) {
                    $script .= $plugin->getSaveTypeDataFunc($this);
                } else {
                    $script .= 'function saveTypeData(){ closeTypeData(); }';
                }
                // init functions for modalbox and unobtrusive buttons
                $script .= '
                function closeTypeData() {
                    clip_modalbox.close();
                }
                function clip_enablePluginConfig(){
                    $(\'saveTypeButton\').observe(\'click\', saveTypeData);
                    $(\'cancelTypeButton\').observe(\'click\', closeTypeData);
                    clip_modalbox = new Control.Modal($(\'showTypeButton\'), {
                        overlayOpacity: 0.6,
                        className: \'clip-modalpopup\',
                        fade: true,
                        iframeshim: false,
                        closeOnClick: false
                    });
                    $(document.body).insert($(\'typeDataDiv\'));
                }
                Event.observe( window, \'load\', clip_enablePluginConfig, false);
                ';

                $typeDataHtml  = '
                <a id="showTypeButton" href="#typeDataDiv"><img src="images/icons/extrasmall/utilities.gif" alt="'.$this->__('Modify config').'" /></a>
                <div id="typeDataDiv" class="clip-modalpopup z-form">
                    '.$plugin->getTypeHtml($this, $render).'
                    <div class="z-formbuttons">
                        <button type="button" id="saveTypeButton" name="saveTypeButton"><img src="images/icons/small/filesave.gif" alt="'.$this->__('Save').'" /></button>&nbsp;
                        <button type="button" id="cancelTypeButton" name="cancelTypeButton"><img src="images/icons/small/button_cancel.gif" alt="'.$this->__('Cancel').'" /></button>
                    </div>
                </div>';
            } else {
                $script .= 'Event.observe( window, \'load\', function() { $(\'typedata_wrapper\').hide(); }, false);';
            }
            $script .= "\n// ]]>\n</script>";
            PageUtil::addVar('rawtext', $script);
        }

        return $result . $typeDataHtml;
    }
}

function smarty_function_clip_form_plugintype($params, &$render) {
    return $render->registerPlugin('ClipFormPluginType', $params);
}
