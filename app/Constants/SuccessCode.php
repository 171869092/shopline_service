<?php

declare(strict_types=1);

namespace App\Constants;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * @Constants
 * @method static getMessage($success_code) SuccessCode::getMessage(SuccessCode::SERVER_ERROR)
 */
class SuccessCode extends AbstractConstants
{
    /**
     * @Message("ok.")
     */
    const ALL_SUCCESS = 200;
}
