<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Workflows
 */

/**
 * Permission check for workflow schema 'none'
 *
 * @param array $obj
 * @param int $permLevel
 * @param int $currentUser
 * @param string $actionId
 * @return bool
 */
function Clip_workflow_none_permissioncheck($obj, $permLevel, $currentUser, $actionId)
{
    if (!empty($obj)) {
        // process $obj and calculate an instance
        $pid = $obj['core_pid'];

        $tid     = Clip_Util::getTidFromString($obj['__WORKFLOW__']['obj_table']);
        $pubtype = Clip_Util::getPubType($tid);

        if ($pubtype['enableeditown'] == 1 and $obj['core_author'] == $currentUser) {
            return true;
        } else {
            return SecurityUtil::checkPermission('clip:input:', "$tid:$pid:$obj[__WORKFLOW__][state]", $permLevel, $currentUser);
        }
    } else {
        // no object passed - user wants to create a new one
        $tid = FormUtil::getPassedValue('tid');

        return SecurityUtil::checkPermission('clip:input:', "$tid::", $permLevel, $currentUser);
    }
}

function Clip_workflow_none_gettextstrings()
{
    return array(
        'title' => no__('None'),
        'description' => no__('This is an almost non-existing workflow. Everything is online immediately after creation.'),

        // state titles
        'states' => array(
            no__('Approved') => no__('Content has been approved is available online'),
            no__('Deleted') => no__('Content has been deleted')
        ),

        // action titles and descriptions for each state
        'actions' => array(
            'initial' => array(
                no__('Submit') => no__('Submit a publication')
            ),
            'approved' => array(
                no__('Update') => no__('Update the publication'),
                no__('Delete') => no__('Delete the publication')
            )
        )
    );
}
