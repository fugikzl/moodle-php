<?php

declare(strict_types=1);

namespace Fugikzl\Moodle\Entities;

final class UserInfo extends BaseEntity
{
    public function __construct(
        public readonly int $moodleId,
        public readonly string $username,
        public readonly string $fullname,
        public readonly string $firstname,
        public readonly string $lastname,
        public readonly string $sitename,
        public readonly string $lang,
        public readonly string $siteurl,
        public readonly string $userpictureurl,
    ) {
    }
}
