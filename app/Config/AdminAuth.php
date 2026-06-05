<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class AdminAuth extends BaseConfig
{
    public string $sessionKey = 'admin_auth';
    public string $username = 'admin';
    public string $password = 'admin12345';
}
