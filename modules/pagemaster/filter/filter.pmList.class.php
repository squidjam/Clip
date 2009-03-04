<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id: $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula
 * @subpackage FilterUtil
*/

Loader::loadClass('filter.category', FILTERUTIL_CLASS_PATH.'/filter');

class FilterUtil_Filter_pmList
  extends FilterUtil_Filter_category
{
	/**
	 * return SQL code
	 *
	 * @access public
	 * @param string $field Field name
	 * @param string $op Operator
	 * @param string $value Test value
	 * @return string SQL code
	 */
    
    
	function getSQL($field, $op, $value)
	{
	    if (array_search($op, $this->availableOperators()) === false || array_search($field,$this->fields) === false) {
			return '';
		}
        Loader :: loadClass('CategoryUtil');
		$cats = CategoryUtil :: getSubCategories($value);
		$items = array();
		$items[] = $value;
		foreach ($cats as $item) {
			$items[] = $item['id'];
		}
		if (count($items) == 1)
			$where = $this->column[$field]." = " . implode("", $items);
		else
			$where = $this->column[$field]." IN (" . implode(",", $items) . ")";
        if ($op == 'ne') {
		    $where = 'NOT '.$where;
		}
		return array('where' => $where);
	}
}