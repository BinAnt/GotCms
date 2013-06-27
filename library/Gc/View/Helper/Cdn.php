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
 * @category   Gc_Library
 * @package    Library
 * @subpackage View\Helper
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\View\Helper;

use Gc\Core\Config as CoreConfig;
use Zend\View\Helper\AbstractHelper;
use Zend\Http\PhpEnvironment\Request;

/**
 * Generate url with specific base path for cdn frontend stored in database.
 *
 * @category   Gc_Library
 * @package    Library
 * @subpackage View\Helper
 * @example In view: $this->cdn('path/to/file');
 */
class Cdn extends AbstractHelper
{
    /**
     * Base path
     *
     * @var string
     */
    protected $basePath = null;

    /**
     * Request
     *
     * @var Request
     */
    protected $request = null;

    /**
     * Constructor
     *
     * @param Request $request Http request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Generates an url with the given path.
     *
     * @param string $path Path
     *
     * @return string  Url
     */
    public function __invoke($path)
    {
        if ($this->basePath === null) {
            $scheme = $this->request->getUri()->getScheme();
            if (CoreConfig::getValue('force_frontend_ssl') or $scheme === 'https') {
                $basePath = CoreConfig::getValue('secure_cdn_base_path');
            } else {
                $basePath = CoreConfig::getValue('unsecure_cdn_base_path');
            }

            $this->basePath = rtrim($basePath, '/');
        }

        return $this->basePath . '/' . ltrim($path, '/');
    }
}
