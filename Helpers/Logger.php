<?php
/**
 * 2018 Alma / Nabla SAS
 *
 * THE MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and
 * to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @author    Alma / Nabla SAS <contact@getalma.eu>
 * @copyright 2018 Alma / Nabla SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 *
 */

namespace Alma\MonthlyPayments\Helpers;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Logger\Monolog;
use Monolog\Handler\StreamHandler;

use Alma\MonthlyPayments\Gateway\Config\Config;

class Logger extends Monolog {
    /** @var Config */
    private $config;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    public function __construct(Config $config, DirectoryList $directoryList, string $name, $handlers = [], $processors = [])
    {
        $this->config = $config;
        $this->directoryList = $directoryList;

        try {
            $handlers[] = new StreamHandler($directoryList->getPath('log') . '/alma.log', self::INFO);
        } catch (FileSystemException $e) {
        } catch (\Exception $e) {
        }

        parent::__construct($name, $handlers, $processors);
    }

    public function addRecord($level, $message, array $context = []): bool
    {
        if (!$this->config->canLog()) {
            return true;
        }

        return parent::addRecord($level, $message, $context);
    }
}
