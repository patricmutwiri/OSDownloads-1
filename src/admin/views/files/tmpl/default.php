<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2015 Open Source Training, LLC. All rights reserved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

JHtml::_('formbehavior.chosen', 'select');

$listOrder = $this->lists['order'];
$listDirn  = $this->lists['order_Dir'];
$saveOrder = $listOrder === 'doc.ordering';

if ($saveOrder) {
    $saveOrderingUrl = 'index.php?option=com_osdownloads&task=files.saveOrderAjax&tmpl=component';
    JHtml::_('sortablelist.sortable', 'documentList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

function category($name, $extension, $selected = null, $javascript = null, $order = null, $size = 1, $sel_cat = 1)
{
    // Deprecation warning.
    JLog::add('JList::category is deprecated.', JLog::WARNING, 'deprecated');

    $categories = JHtml::_('category.options', $extension);
    if ($sel_cat) {
        array_unshift($categories, JHtml::_('select.option', '0', JText::_('JOPTION_SELECT_CATEGORY')));
    }

    $category = JHtml::_(
        'select.genericlist', $categories, $name, 'class="inputbox chosen" size="' . $size . '" ' . $javascript, 'value', 'text',
        $selected
    );

    return $category;
}

?>

<script type="text/javascript">
    Joomla.orderTable = function()
    {
        table = document.getElementById("sortTable");
        direction = document.getElementById("directionTable");
        order = table.options[table.selectedIndex].value;
        if (order != '<?php echo $listOrder; ?>')
        {
            dirn = 'asc';
        }
        else
        {
            dirn = direction.options[direction.selectedIndex].value;
        }
        Joomla.tableOrdering(order, dirn, '');
    }
</script>

<form action="index.php?option=com_osdownloads" method="post" name="adminForm" id="adminForm">
    <table width="100%">
        <tr>
            <td>
                <div class="js-stools clearfix">
                    <div class="clearfix">
                        <div class="btn-wrapper input-append">
                            <input type="text" name="search" id="search" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" value="<?php echo htmlspecialchars($this->filter->search);?>" class="text_area" onchange="document.adminForm.submit();" />
                            <button class="btn hasTooltip" title="" type="submit" data-original-title="Search">
                                <?php echo JText::_( 'COM_OSDOWNLOADS_GO' ); ?>
                            </button>
                        </div>
                        <div class="btn-wrapper">
                            <button onclick="document.getElementById('search').value='';this.form.getElementById('cate_id').value='';this.form.submit();" class="btn hasTooltip js-stools-btn-clear">
                                <?php echo JText::_( 'COM_OSDOWNLOADS_RESET' ); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </td>
            <td align="right">
                <div class="js-stools clearfix">
                    <?php echo category('flt_cate_id', 'com_osdownloads', $this->filter->categoryId, "onchange='this.form.submit();'", 'title', $size = 1, $sel_cat = 1); ?>
                </div>
            </td>
        </tr>
    </table>
    <table class="adminlist table table-striped" id="documentList" width="100%" border="0">
        <thead>
            <tr>
                <th width="1%" class="nowrap center hidden-phone">
                    <?php echo JHtml::_('searchtools.sort', '', 'doc.ordering', @$this->lists['order_Dir'], @$this->lists['order'], null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
                </th>
                <th width="1%" class="hidden-phone">
                    <input type="checkbox" onclick="Joomla.checkAll(this)" title="<?php echo JText::_('COM_OSDOWNLOADS_CHECK_All'); ?>" value="" name="checkall-toggle" />
                </th>
                <th width="1%" style="min-width:55px" class="nowrap center">
                    <?php echo JHTML::_('grid.sort', 'COM_OSDOWNLOADS_PUBLISHED', 'doc.published', @$this->lists['order_Dir'], @$this->lists['order'] ); ?>
                </th>
                <th class="has-context span6">
                    <?php echo JHTML::_('grid.sort', 'COM_OSDOWNLOADS_NAME', 'doc.name', @$this->lists['order_Dir'], @$this->lists['order'] ); ?>
                </th>
                <th class="">
                    <?php echo JHTML::_('grid.sort', 'COM_OSDOWNLOADS_ACCESS', 'doc.access', @$this->lists['order_Dir'], @$this->lists['order'] ); ?>
                </th>
                <th class="center nowrap">
                    <?php echo JHTML::_('grid.sort', 'COM_OSDOWNLOADS_DOWNLOADED', 'doc.downloaded', @$this->lists['order_Dir'], @$this->lists['order'] ); ?>
                </th>
                <?php if ($this->extension->isPro()) : ?>
                    <?php echo $this->loadTemplate('pro_headers'); ?>
                <?php endif; ?>
                <th class="hidden-phone center">
                    <?php echo JHTML::_('grid.sort', 'COM_OSDOWNLOADS_ID', 'doc.id', @$this->lists['order_Dir'], @$this->lists['order'] ); ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($this->items as $i => $item) :
                $item->checked_out = false;
                $ordering   = ($listOrder == 'doc.ordering');
                $checked    = JHTML::_('grid.checkedout', $item, $i );
                // $canChange  = $user->authorise('core.edit.state', 'com_content.article.'.$item->id) && $canCheckin;
                $canChange  = true;
            ?>
                <tr class="row<?php echo $i % 2; ?>">
                    <td class="order nowrap center hidden-phone">
                        <?php
                        $iconClass = '';
                        if (!$canChange)
                        {
                            $iconClass = ' inactive';
                        }
                        elseif (!$saveOrder)
                        {
                            $iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
                        }
                        ?>
                        <span class="sortable-handler<?php echo $iconClass ?>">
                            <i class="icon-menu"></i>
                        </span>
                        <?php if ($canChange && $saveOrder) : ?>
                            <input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order " />
                        <?php endif; ?>
                    </td>
                    <td class="hidden-phone"><?php echo $checked; ?></td>
                    <td class="center">
                        <div class="btn-group">
                            <?php echo JHtml::_('jgrid.published', $item->published, $i, 'files.', $canChange, 'cb', $item->publish_up, $item->publish_down); ?>
                            <?php
                            // // Create dropdown items
                            // $action = $archived ? 'unarchive' : 'archive';
                            // JHtml::_('actionsdropdown.' . $action, 'cb' . $i, 'articles');

                            // $action = $trashed ? 'untrash' : 'trash';
                            // JHtml::_('actionsdropdown.' . $action, 'cb' . $i, 'articles');

                            // // Render dropdown list
                            // echo JHtml::_('actionsdropdown.render', $this->escape($item->title));
                            ?>
                        </div>
                    </td>
                    <td class="has-context span6">
                        <a href="index.php?option=com_osdownloads&view=file&cid[]=<?php echo($item->id);?>"><?php echo ($item->name); ?></a>
                        <span class="small">
                            <?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
                        </span>
                        <div class="small">
                            <?php echo JText::_('JCATEGORY') . ": " . $this->escape($item->cat_title); ?>
                        </div>
                    </td>
                    <td class="small">
                        <?php echo($item->access_title); ?>
                    </td>
                    <td class="center nowrap"><?php echo($item->downloaded);?></td>

                    <?php if ($this->extension->isPro()) : ?>
                        <?php
                        $this->item = $item;
                        echo $this->loadTemplate('pro_columns');
                        ?>
                    <?php endif; ?>
                    <td class="hidden-phone center"><?php echo($item->id);?></td>
                </tr>
            <?php endforeach;?>
        </tbody>
        <tfoot>
            <tr>
                <?php
                    $colspan = 7;
                    if ($this->extension->isPro()) {
                        $colspan += 2;
                    }
                ?>
                <td colspan="<?php echo $colspan; ?>">
                    <?php echo $this->pagination->getListFooter(); ?>
                </td>
            </tr>
        </tfoot>
    </table>
    <input type="hidden" name="option" value="com_osdownloads" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
    <?php echo JHTML::_( 'form.token' ); ?>
</form>

<?php echo $this->extension->getFooterMarkup(); ?>
