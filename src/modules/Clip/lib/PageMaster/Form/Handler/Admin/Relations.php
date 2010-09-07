<?php
/**
 * PageMaster
 *
 * @copyright   (c) PageMaster Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package     Zikula_3rdParty_Modules
 * @subpackage  pagemaster
 */

/**
 * Form handler to manage the relations.
 */
class PageMaster_Form_Handler_Admin_Relations extends Form_Handler
{
    var $id;
    var $returnurl;

    /**
     * Initialize function
     */
    function initialize($view)
    {
        $id  = FormUtil::getPassedValue('id', 0);
        $tid = FormUtil::getPassedValue('tid', 0);

        $tableObj = Doctrine_Core::getTable('PageMaster_Model_Pubrelation');

        if (!empty($id)) {
            $this->id = $id;
            $relation = $tableObj->find($id);

            $relation->mapValue('type1', $relation->type < 2 ? 0 : 1);
            $relation->mapValue('type2', $relation->type%2 == 0 ? 0 : 1);

            $view->assign('relation', $relation->toArray());
        }

        $where     = $tid ? "tid1 = '$tid'" : '';
        $relations = $tableObj->selectCollection($where, 'tid1 ASC, tid2 ASC', -1, -1, 'id');

        $reltype1 = array(
            array(
                'text'  => $this->__('One'),
                'value' => 0
            ),
            array(
                'text'  => $this->__('Many'),
                'value' => 1
            )
        );
        $reltype2 = array(
            array(
                'text'  => $this->__('has One'),
                'value' => 0
            ),
            array(
                'text'  => $this->__('has Many'),
                'value' => 1
            )
        );

        $view->assign('pubtypes', PageMaster_Util::getPubType(-1))
             ->assign('typeselector', PageMaster_Util::getPubtypesSelector(true, false))
             ->assign('relations', $relations)
             ->assign('reltypes', array($reltype1, $reltype2))
             ->assign('tid', $tid);

        // stores the return URL
        if (empty($this->returnurl)) {
            $returnurl = ModUtil::url('PageMaster', 'admin', 'relations',
                                      array('id'  => $id,
                                            'tid' => $tid));
            $this->returnurl = System::serverGetVar('HTTP_REFERER', $returnurl);
        }

        return true;
    }

    /**
     * Command handler
     */
    function handleCommand($view, &$args)
    {
        if ($args['commandName'] == 'cancel') {
            return $view->redirect($this->returnurl);
        }

        $data = $view->getValues();

        // creates and fill a Pubfield instance
        $relation = new PageMaster_Model_Pubrelation();
        if (!empty($this->id)) {
            $relation->assignIdentifier($this->id);
        }
        $relation->fromArray($data['relation']);

        // fill default data
        $relation->type = bindec("{$data['relation']['type1']}{$data['relation']['type2']}");

        // handle the commands
        switch ($args['commandName'])
        {
            // create a field
            case 'create':
                if (!$view->isValid()) {
                    return false;
                }

                $tableObj = Doctrine_Core::getTable('PageMaster_Model_Pubrelation');

                // check it's unique
                $where = array(
                    array('type = ?', $relation->type),
                    array('tid1 = ?', $relation->tid1),
                    array('tid2 = ?', $relation->tid2)
                );

                $isUnique = (int)$tableObj->selectFieldFunction('id', 'COUNT', $where);
                if ($isUnique > 0) {
                    $plugin = $view->getPluginById('tid1');
                    $plugin->setError($this->__('This relation already exists.'));
                    return false;
                }

                // update the implied pubdata tables
                Doctrine_Core::getTable('PageMaster_Model_Pubdata'.$relation->tid1)->changeTable();
                Doctrine_Core::getTable('PageMaster_Model_Pubdata'.$relation->tid2)->changeTable();

                // create/edit status messages
                if (empty($this->id)) {
                    LogUtil::registerStatus($this->__('Done! Relation created.'));
                } else {
                    LogUtil::registerStatus($this->__('Done! Relation updated.'));
                }
                $relation->save();
                break;

            // delete the field
            case 'delete':
                if ($relation->delete()) {
                    LogUtil::registerStatus($this->__('Done! Relation deleted.'));
                } else {
                    return LogUtil::registerError($this->__('Error! Deletion attempt failed.'));
                }
                break;
        }

        // TODO update both pubtypes tables

        return $view->redirect($this->returnurl);
    }
}