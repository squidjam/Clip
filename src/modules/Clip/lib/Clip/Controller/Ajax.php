<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Controller
 */

/**
 * Ajax Controller.
 */
class Clip_Controller_Ajax extends Zikula_Controller_AbstractAjax
{
    public function __call($func, $args)
    {
        $this->checkAjaxToken();

        // try to get a method checking both controllers
        $response = false;
        if (method_exists('Clip_Controller_Admin', $func)) {
            $response = ModUtil::func('Clip', 'admin', $func, $args);
        } elseif (method_exists('Clip_Controller_User', $func)) {
            $response = ModUtil::func('Clip', 'user', $func, $args);
        }

        $this->throwNotFoundUnless($response);

        return new Zikula_Response_Ajax_Plain($response);
    }

    public function editgroup()
    {
        $this->checkAjaxToken();

        $mode = $this->request->getPost()->get('mode', 'add');
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Clip::', '::', ($mode == 'edit') ? ACCESS_EDIT : ACCESS_ADD));

        $gid    = $this->request->getPost()->get('gid', 0);
        $pos    = $this->request->getPost()->get('pos', 'root');
        $parent = $this->request->getPost()->get('parent', 1);

        if ($mode == 'edit') {
            // edit mode of an existing item
            if (!$gid) {
                return new Zikula_Response_Ajax_BadData($this->__f("Error! Cannot determine valid '%s' for edit in '%s'.", array('gid', 'editgroup')));
            }
            $group = Doctrine_Core::getTable('Clip_Model_Grouptype')->find($gid);
            $this->throwNotFoundUnless($group, $this->__('Sorry! No such group found.'));
        } else {
            // new item mode
            $group = new Clip_Model_Grouptype();
            $group->mapValue('parent', $parent);
        }

        Zikula_AbstractController::configureView();
        $this->view->setCaching(false);

        $this->view->assign('mode', $mode)
                   ->assign('pos', $pos)
                   ->assign('group', $group)
                   ->assign('languages', ZLanguage::getInstalledLanguages());

        $result = array(
            'action' => $mode,
            'result' => $this->view->fetch('clip_ajax_groupedit.tpl')
        );
        return new Zikula_Response_Ajax($result);
    }

    /**
     * Resequence group/pubtypes
     */
    public function treeresequence()
    {
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Clip::', '::', ACCESS_EDIT));

        // build a map of the input data
        $data = json_decode($this->request->getPost()->get('data'), true);

        $tids = array();
        $map  = array(1 => array());
        foreach ($data as $id => $item) {
            $id = explode('-', $id);
            $item['parent'] = $item['parent'] == 0 ? 1 : $item['parent'];
            if (!isset($id[1])) {
                // grouptype
                $map[$item['parent']][] = $id[0];
                $map[$id[0]] = array();
            } else {
                // pubtype
                $tids[$item['parent']][] = $id[1];
            }
        }
        unset($data);

        // build a map of the existing tree
        $grouptypes = Doctrine_Core::getTable('Clip_Model_Grouptype')->getTree()->fetchTree();

        $parents  = array(0 => 1);
        $original = array(1 => array());

        foreach ($grouptypes as $item) {
            $original[$item['gid']] = array();
            if ($item['level'] > 0) {
                $parentid = $parents[$item['level'] - 1];
                $original[$parentid][] = $item['gid'];
                // assign and link its pubtypes
                $item->order = $tids[$item['gid']];
                $item->link('pubtypes', $tids[$item['gid']]);
            }
            $parents[$item['level']] = $item['gid'];
        }
        $grouptypes->save();
        unset($grouptypes);
        unset($tids);

        // check the differences between maps
        $diffs  = array();
        $udiffs = array();
        foreach (array_keys($original) as $gid) {
            $diffs[$gid]  = array_diff($map[$gid], $original[$gid]);
            $udiffs[$gid] = array_diff_assoc($map[$gid], $original[$gid]);
        }

        $result = true;

        // for move between trees
        $diffs = array_filter($diffs);
        if (count($diffs)) {
            $keys = array_keys($diffs);
            // validate that there's only one change at time
            if (count($keys) == 1 && count($diffs[$keys[0]]) == 1) {
                $tbl = Doctrine_Core::getTable('Clip_Model_Grouptype');

                foreach ($diffs as $gid => $diff) {
                    $newpos = key($diff);
                    $maxpos = count($map[$gid]) - 1;
                    switch ($newpos) {
                        case 0:
                            $method = 'moveAsFirstChildOf';
                            break;
                        case $maxpos:
                            $method = 'moveAsLastChildOf';
                            break;
                        default:
                            $gid = $map[$gid][$newpos-1];
                            $method = 'moveAsNextSiblingOf';
                    }
                    $refer = $tbl->find($gid);
                    $moved = $tbl->find(current($diff));
                    $moved->getNode()->$method($refer);
                }
            } elseif (count($keys) > 1) {
                // TODO error message because it has more than one change
                $result = false;
            }
        }

        // for leaf reorder
        $udiffs = array_filter($udiffs);
        if (count($udiffs) == 1) {
            // validate that there's only one change at time
            $tbl = Doctrine_Core::getTable('Clip_Model_Grouptype');

            foreach ($udiffs as $gid => $udiff) {
                $maxpos = count($original[$gid]) - 1;
                // check the first item
                $ufirst = reset($udiff);
                $kfirst = key($udiff);
                $pfirst = array_search($ufirst, $original[$gid]);
                // check the last item
                $ulast = end($udiff);
                $klast = key($udiff);
                $plast = array_search($ulast, $original[$gid]);
                if ($pfirst == $maxpos || $original[$gid][$pfirst+1] != $udiff[$kfirst+1]) {
                    // check if it was the last one or moved up
                    $rel = $udiff[$kfirst+1];
                    $gid = $ufirst;
                    $method = 'moveAsPrevSiblingOf';
                } elseif ($plast == 0 || $original[$gid][$plast-1] != $udiff[$klast-1]) {
                    // check if it was the first or the original order doesn't match
                    $rel = $udiff[$klast-1];
                    $gid = $ulast;
                    $method = 'moveAsNextSiblingOf';
                }
                $refer = $tbl->find($rel);
                $moved = $tbl->find($gid);
                $moved->getNode()->$method($refer);
            }
        } elseif (count($udiffs) > 1) {
            // TODO error message because it has more than one change
            $result = false;
        }

        $result = array(
            'response' => $result
        );
        return new Zikula_Response_Ajax($result);
    }
}
