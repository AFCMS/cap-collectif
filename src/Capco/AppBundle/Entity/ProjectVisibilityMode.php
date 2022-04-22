<?php
namespace Capco\AppBundle\Entity;

final class ProjectVisibilityMode
{
    public const VISIBILITY_ME = 0;
    public const VISIBILITY_ADMIN = 1;
    public const VISIBILITY_PUBLIC = 2;

    public const VISIBILITY = [
        'myself' => self::VISIBILITY_ME,
        'private' => self::VISIBILITY_ADMIN,
        'public' => self::VISIBILITY_PUBLIC,
    ];

    public const VISIBILITY_WITH_HELP_TEXT = [
        'myself-visibility-only-me' => self::VISIBILITY_ME,
        'private-visibility-private' => self::VISIBILITY_ADMIN,
        'public-everybody' => self::VISIBILITY_PUBLIC,
    ];

    public const REVERSE_KEY_VISIBILITY = [
        self::VISIBILITY_ME => 'myself',
        self::VISIBILITY_ADMIN => 'private',
        self::VISIBILITY_PUBLIC => 'public',
    ];
}
