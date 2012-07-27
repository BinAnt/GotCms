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
 * @category Gc
 * @package  Datatype
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link     http://www.got-cms.com
 */

namespace Datatypes\Upload;

use Gc\Datatype\AbstractDatatype
    , Gc\Property\Model as PropertyModel;

class Datatype extends AbstractDatatype
{
    /**
     * @var string name of datatype
     */
    protected $_name = 'upload';

    /**
     * Retrieve editor
     * @return \Gc\Datatype\AbstractDatatype\AbstractEditor
     */
    public function getEditor(PropertyModel $property)
    {
        $this->_property = $property;
        if($this->_editor === null)
        {
            $this->_editor = new Editor($this);
        }

        return $this->_editor;
    }

    /**
     * Retrieve prevalue editor
     * @return \Gc\Datatype\AbstractDatatype\AbstractPrevalueEditor
     */
    public function getPrevalueEditor()
    {
        if($this->_prevalueEditor === null)
        {
            $this->_prevalueEditor = new PrevalueEditor($this);
        }

        return $this->_prevalueEditor;
    }
}
