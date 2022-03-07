<?php

namespace ZnDatabase\Tool;

use ZnCore\Base\Libs\App\Base\BaseBundle;

class Bundle extends BaseBundle
{

    public function console(): array
    {
        return [
            'ZnDatabase\Tool\Commands',
        ];
    }
}
