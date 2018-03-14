<?php

namespace Doctrine\Tests\Common\DataFixtures\TestType;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\StringType;
use Doctrine\Tests\Common\DataFixtures\TestEntity\RoleId;

class RoleIdType extends StringType
{
    const NAME = 'role_id';

    public function getName()
    {
        return static::NAME;
    }

    /**
     * @param string $value
     * @param AbstractPlatform $platform
     *
     * @return null|RoleId
     *
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (empty($value)) {
            return null;
        }

        return new RoleId($value);
    }

    /**
     * {@inheritdoc}
     *
     * @param RoleId|null $value
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (empty($value)) {
            return null;
        }

        if (!$value instanceof RoleId) {
            throw ConversionException::conversionFailed($value, static::NAME);
        }

        return $value->getValue();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
     *
     * @return boolean
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}

