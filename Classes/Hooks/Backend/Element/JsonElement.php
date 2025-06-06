<?php

/**
 * This file is part of the package georgringer/news-importicsxml.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace GeorgRinger\NewsImporticsxml\Hooks\Backend\Element;

use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Core\Utility\DebugUtility;

use function is_array;

class JsonElement extends AbstractNode
{
    public function render()
    {
        $parameterArray = $this->data['parameterArray'];
        $resultArray    = $this->initializeResultArray();

        $json                = $parameterArray['itemFormElValue'];
        $resultArray['html'] = '';
        if (!empty($json)) {
            $data = json_decode($json, true);
            if (is_array($data) && !empty($data)) {
                $resultArray['html'] = DebugUtility::viewArray($data);
            }
        }

        return $resultArray;
    }
}
