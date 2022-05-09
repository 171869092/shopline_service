<?php
declare(strict_types=1);
namespace App\Service\UserAdmin;

use App\Model\UserAdmin;
use Hyperf\Di\Annotation\Inject;

class UserAdminService
{

    public function getUser(int $phone, string $passwd)
    {
        return UserAdmin::query()->where(['phone' => $phone, 'password' => $passwd])->first();
    }
}
