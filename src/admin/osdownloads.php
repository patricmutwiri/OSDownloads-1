<?php
/**
 * @version 1.0.0
 * @author Open Source Training (www.ostraining.com)
 * @copyright (C) 2011- Open Source Training
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
// no direct access
defined('_JEXEC') or die;
jimport('joomla.application.component.controller');
$controller = JControllerLegacy::getInstance('osdownloads');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
