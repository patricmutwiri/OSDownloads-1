<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2015 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

require_once JPATH_SITE . '/components/com_osdownloads/models/item.php';

use Alledia\OSDownloads\Free\Joomla\View\Legacy as LegacyView;


class OSDownloadsViewDownloads extends LegacyView
{

    public function display($tpl = null)
    {
        $app                 = JFactory::getApplication();
        $db                  = JFactory::getDBO();
        $params              = clone($app->getParams('com_osdownloads'));
        $categoryIDs         = (array) $params->get("category_id");
        $includeChildFiles   = (bool) $params->get('include_child_files', 0);
        $showChildCategories = (bool) $params->get('show_child_categories', 1);

        // Load the extension
        $extension = Alledia\Framework\Factory::getExtension('OSDownloads', 'component');
        $extension->loadLibrary();

        $limit      = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limitstart = $app->getUserStateFromRequest('osdownloads.request.limitstart', 'limitstart', 0, 'int');

        // Paths
        $paths = array();

        $id = JRequest::getVar("id", null, 'default', 'int');

        if (empty($id)) {
            if (count($categoryIDs) == 1) {
                $id = $categoryIDs[0];
            }
        }

        if (!empty($id)) {
            $this->buildPath($paths, $id);

            $categoryIDs = (array) $id;
        }
        $categoryIDsStr = implode(',', $categoryIDs);

        $model = JModelLegacy::getInstance('OSDownloadsModelItem');

        $query = $model->getItemQuery();

        $query->select('cat.access as cat_access');

        if ($includeChildFiles) {
            $query->where("(cate_id IN ({$categoryIDsStr}) OR cat.parent_id IN ({$categoryIDsStr}))");
        } else {
            $query->where("cate_id IN ({$categoryIDsStr})");
        }

        $query->order('doc.ordering');

        $db->setQuery($query);


        // Pagination
        $db->setQuery($query);
        $db->query();

        $total = $db->getNumRows();

        jimport('joomla.html.pagination');
        $pagination = new JPagination($total, $limitstart, $limit);

        // Items
        $db->setQuery($query, $pagination->limitstart, $pagination->limit);
        $items = $db->loadObjectList();

        $user = JFactory::getUser();
        $groups = $user->getAuthorisedViewLevels();

        if (!isset($items) || (count($items) && !in_array($items[0]->cat_access, $groups))) {
            JError::raiseWarning(404, JText::_("COM_OSDOWNLOADS_THIS_CATEGORY_ISNT_AVAILABLE"));

            return;
        }

        // Categories
        $extraWhere = '';
        if ($showChildCategories) {
            $extraWhere = " OR c.parent_id IN ({$categoryIDsStr}) ";
        }

        $groupsStr = implode(',', $groups);
        $query = "SELECT *
                  FROM `#__categories` AS c
                  WHERE extension='com_osdownloads'
                    AND published = 1
                    AND (id IN ({$categoryIDsStr})
                        {$extraWhere}
                    )
                  ORDER BY c.lft ASC";
        $db->setQuery($query);
        $categories = $db->loadObjectList();

        // Category filter
        $showCategoryFilter = $params->get('show_category_filter', false);

        $this->buildBreadcrumbs($paths);

        $this->assignRef("categories", $categories);
        $this->assignRef("showCategoryFilter", $showCategoryFilter);
        $this->assignRef("items", $items);
        $this->assignRef("paths", $paths);
        $this->assignRef("pagination", $pagination);
        $this->assign("isPro", $extension->isPro());

        parent::display($tpl);
    }

    public function buildPath(&$paths, $categoryID)
    {
        if (empty($categoryID)) {
            return;
        }

        $db = JFactory::getDBO();
        $db->setQuery("SELECT *
                       FROM `#__categories`
                       WHERE extension='com_osdownloads'
                           AND id = " . $db->q((int) $categoryID));
        $category = $db->loadObject();

        if ($category) {
            $paths[] = $category;
        }

        if ($category && $category->parent_id) {
            $this->buildPath($paths, $category->parent_id);
        }
    }

    protected function buildBreadcrumbs($paths) {
        $app     = JFactory::getApplication();
        $pathway = $app->getPathway();
        $itemID  = JRequest::getVar("Itemid", null, 'default', 'int');

        $countPaths = count($paths) - 1;
        for ($i = $countPaths; $i >= 0; $i--) {
            $pathway->addItem(
                $paths[$i]->title,
                JRoute::_("index.php?option=com_osdownloads&view=downloads&id={$paths[$i]->id}"."&Itemid={$itemID}")
            );
        }
    }
}
