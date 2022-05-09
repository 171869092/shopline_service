<?php

declare(strict_types=1);

namespace App\Constants;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * @Constants
 * @method static getMessage(int $error_code, array $arg = null) ErrorCode::getMessage(ErrorCode::SERVER_ERROR)
 */
class ErrorCode extends AbstractConstants
{
    /**
     * @Message("Server Error！")
     */
    const SERVER_ERROR = 500;
    /**
     * @Message("Unauthorized.")
     */
    const TOKEN_ERROR = 302;
    /**
     * @Message("Account or password error.")
     */
    const LOGIN_ERROR = 1001;
    /**
     * @Message("Param Error.")
     */
    const PARAM_ERROR = 1002;
    /**
     * @Message("%s is not exist error.")
     */
    const NOT_EXIST_ERROR = 1003;
    /**
     * @Message("Repeat submission error.")
     */
    const REPEAT_SUBMISSION_ERROR = 1004;

    /**
     * @Message("User does not exist error.")
     */
    const USER_NOT_EXIST_ERROR = 1005;

    const NORMAL_ERROR = 400;

    /**
     * @Message("The store not install.")
     */
    const NOT_INSTALL_ERROR = -1;
}
