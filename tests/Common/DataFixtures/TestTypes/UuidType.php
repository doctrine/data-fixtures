<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\TestTypes;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrine\Tests\Common\DataFixtures\TestValueObjects\Uuid;

class UuidType extends Type
{
    public const NAME = 'uuid';

    /** @param mixed[] $fieldDeclaration */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        $fieldDeclaration['length'] = 36;
        $fieldDeclaration['fixed']  = true;

        return $platform->getStringTypeDeclarationSQL($fieldDeclaration);
    }

    /** @param string|null $value */
    public function convertToPHPValue($value, AbstractPlatform $platform): Uuid|null
    {
        return $value === null ? null : new Uuid($value);
    }

    /** @param Uuid|null $value */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): string|null
    {
        return $value === null ? null : (string) $value;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
