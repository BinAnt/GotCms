<?php
/**
 * This source file is part of GotCms.
 *
 * GotCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GotCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with GotCms. If not, see <http://www.gnu.org/licenses/lgpl-3.0.html>.
 *
 * PHP Version >=5.3
 *
 * @category Controller
 * @package  Content\Controller
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link     http://www.got-cms.com
 */

namespace Content\Controller;

use Gc\Mvc\Controller\Action,
    Gc\Document,
    Gc\Property,
    Gc\Media\File,
    Zend\Json\Json,
    Zend\File\Transfer\Adapter\Http as FileTransfer;

class MediaController extends Action
{
    /**
     * Contains information about acl
     * @var array $_acl_page
     */
    protected $_acl_page = array('resource' => 'Content', 'permission' => 'media');

    /**
     *
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function indexAction()
    {

    }

    /**
     * Upload file action
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function uploadAction()
    {
        $property = Property\Model::fromId($this->getRouteMatch()->getParam('property_id'));
        $document = Document\Model::fromId($this->getRouteMatch()->getParam('document_id'));
        if(!$this->getRequest()->isPost() or empty($document) or empty($property))
        {
            return $this->_returnJson(array('error' => TRUE));
        }

        $file_class = new File();
        $file_class->init($property, $document);
        $files = array();
        if($file_class->upload())
        {
            $files = $file_class->getFiles();
        }

        if(!empty($files))
        {
            return $this->_returnJson($files);
        }

        return $this->_returnJson(array('error' => TRUE));
    }

    /**
     * Delete file
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function removeAction()
    {
        $property = Property\Model::fromId($this->getRouteMatch()->getParam('property_id'));
        $document = Document\Model::fromId($this->getRouteMatch()->getParam('document_id'));
        if($this->getRequest()->getMethod() != 'DELETE' or empty($document) or empty($property))
        {
            return $this->_returnJson(array('error' => TRUE));
        }

        $file_class = new File();
        $file_class->init($property, $document);
        return $this->_returnJson(array($file_class->remove($this->getRouteMatch()->getParam('file'))));
    }
}
