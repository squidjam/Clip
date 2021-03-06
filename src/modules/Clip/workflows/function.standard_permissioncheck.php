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
 * Permission check for workflow schema 'standard'
 *
 * @param array $obj
 * @param int $permLevel
 * @param int $currentUser
 * @param string $actionId
 * @return bool
 */
function Clip_workflow_standard_permissioncheck($obj, $permLevel, $currentUser, $actionId)
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

function Clip_workflow_standard_gettextstrings()
{
    no__('Are you sure you want to delete this publication?');

    return array(
        'title' => no__('Standard'),
        'description' => no__('This is a two staged workflow with stages for untrusted submissions and finally approved publications. It does not allow untrusted corrections to published pages.'),

        // state titles
        'states' => array(
            no__('Waiting') => no__('Content has been submitted and is waiting for acceptance'),
            no__('Approved') => no__('Content has been approved is available online')
        ),

        // action titles and descriptions for each state
        'actions' => array(
            'initial' => array(
                no__('Submit and Approve') => no__('Submit a publication and approve immediately'),
                no__('Submit') => no__('Submit a publication for acceptance by a moderator')
            ),
            'waiting' => array(
                no__('Update and Approve') => no__('Update the content and approve for immediate publishing'),
                no__('Approve') => no__('Approve the publication for immediate publishing'),
                no__('Update') => no__('Update the content for later publishing'),
                no__('Delete') => no__('Delete the publication')
            ),
            'approved' => array(
                no__('Update') => no__('Update the publication'),
                no__('Publish') => no__('Make the publication available'),
                no__('Unpublish') => no__('Hide the publication'),
                no__('Move to depot') => no__('Move the publication to the depot'),
                no__('Delete') => no__('Delete the publication')
            )
        )
    );
}
