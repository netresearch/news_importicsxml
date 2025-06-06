<?php

/**
 * This file is part of the package georgringer/news-importicsxml.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace GeorgRinger\NewsImporticsxml\Backend\Form\Element;

use JsonException;
use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Core\Utility\DebugUtility;

use function is_array;

/**
 * Class JsonElement
 */
class JsonElement extends AbstractNode
{
    /**
     * @return array<mixed>
     *
     * @throws JsonException
     */
    public function render(): array
    {
        $parameterArray = $this->data['parameterArray'];
        $resultArray    = $this->initializeResultArray();

        $json                = $parameterArray['itemFormElValue'];
        $resultArray['html'] = '';

        if ($json !== '') {
            $data = json_decode(
                $json,
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            if (is_array($data) && ($data !== [])) {
                $resultArray['html'] = DebugUtility::viewArray($data);
            }
        }

        return $resultArray;
    }
}
