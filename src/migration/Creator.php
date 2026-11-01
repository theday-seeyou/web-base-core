<?php

declare(strict_types=1);

namespace WebBase\Core\migration;

/** Uses the WebBase table contract for explicitly generated migration files. */
final class Creator extends \think\migration\Creator
{
    protected function getTemplate()
    {
        return __DIR__ . '/migration.stub';
    }
}
