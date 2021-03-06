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

/**
 * Returns the state
 *
 * @author kundi
 * @param $args['tid'] tid
 * @param $args['id'] pid
 * @param $args['assign'] optional
 *
 * @return string
 */
function smarty_function_clip_getstate($params, &$view)
{
    $dom = ZLanguage::getModuleDomain('Clip');

    $tid = (int)$params['tid'];
    $id  = (int)$params['id'];

    if (!$tid) {
        return LogUtil::registerError(__f('Error! Missing argument [%s].', 'tid', $dom));
    }

    if (!$id) {
        return LogUtil::registerError(__f('Error! Missing argument [%s].', 'id', $dom));
    }

    $tablename = 'clip_pubdata'.$tid;
    $pub       = array('id' => $id);

    Zikula_Workflow_Util::getWorkflowForObject($pub, $tablename, 'id', 'Clip');

    if (isset($params['assign'])) {
        $view->assign($params['assign'], $pub['__WORKFLOW__']);
    } else {
        return $pub['__WORKFLOW__'];
    }
}
