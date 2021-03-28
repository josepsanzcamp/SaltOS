<?php

/**
 * This file is part of the WarnockPDF library.
 * @license LGPL-3.0
 * @deprecated 7.0.0 This file will be removed in next major version
 */

// Dependencies of StaticUtils class
require_once __DIR__ . '/../src/Colors.php';
require_once __DIR__ . '/../src/Version.php';

require_once __DIR__ . '/../src/StaticUtils.php';

/**
 * This is a legacy support class
 * @deprecated 7.0.0 This file will be removed in next major version
 */
class TCPDF_STATIC extends \WarnockPDF\StaticUtils
{

    /**
     * @deprecated use getVersion
     */
    public static function getTCPDFVersion() {
        return self::getVersion();
    }

    /**
     * @deprecated use getProducerString
     */
    public static function getTCPDFProducer() {
        return parent::getProducerString();
    }

}
