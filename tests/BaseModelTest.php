<?php

declare(strict_types=1);

namespace WebBase\Core\tests;

use PHPUnit\Framework\TestCase;
use WebBase\Core\BaseModel;
use WebBase\Core\model\SystemBase;
use WebBase\Core\model\SystemUser;

final class SoftDeleteModelFixture extends BaseModel
{
    public function timestampForTest(): string
    {
        return $this->autoWriteTimestamp();
    }
}

class BaseModelTest extends TestCase
{
    public function testSoftDeleteContractUsesNullableDeletedAt(): void
    {
        $defaults = (new \ReflectionClass(BaseModel::class))->getDefaultProperties();

        self::assertSame('deleted_at', $defaults['deleteTime']);
        self::assertNull($defaults['defaultSoftDelete']);
        self::assertContains(\think\model\concern\SoftDelete::class, class_uses(BaseModel::class));

        $model = (new \ReflectionClass(SoftDeleteModelFixture::class))->newInstanceWithoutConstructor();
        self::assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $model->timestampForTest());
    }

    public function testLegacySoftDeleteModelsUseBaseModel(): void
    {
        self::assertTrue(is_subclass_of(SystemBase::class, BaseModel::class));
        self::assertTrue(is_subclass_of(SystemUser::class, BaseModel::class));
    }
}
