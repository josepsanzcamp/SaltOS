<?php

/**
 * This file is part of the WarnockPDF library.
 * @license LGPL-3.0
 * @deprecated 7.0.0 This file will be removed in next major version
 */

// static font methods and data
require_once __DIR__ . '/include/tcpdf_font_data.php';
// static font methods and data
require_once __DIR__ . '/include/tcpdf_fonts.php';
// static color methods and data
require_once __DIR__ . '/include/tcpdf_colors.php';
// static image methods and data
require_once __DIR__ . '/include/tcpdf_images.php';
// static methods and data
require_once __DIR__ . '/include/tcpdf_static.php';

// WarnockPDF class dependencies
require_once __DIR__ . '/src/StaticUtils.php';
require_once __DIR__ . '/src/Fonts.php';
require_once __DIR__ . '/src/FontData.php';
require_once __DIR__ . '/src/Colors.php';
require_once __DIR__ . '/src/Images.php';

require_once __DIR__ . '/src/WarnockPDF.php';

/**
 * This is a legacy support class
 * @deprecated 7.0.0 This file will be removed in next major version
 */
class TCPDF extends \WarnockPDF\WarnockPDF
{
}
