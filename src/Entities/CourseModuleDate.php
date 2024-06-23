<?php

declare(strict_types=1);

namespace Fugikzl\Moodle\Entities;

final class CourseModuleDate extends BaseEntity
{
    /**
     * @param int $cmid
     * @param string $label
     * @param int $timestamp
     */
    public function __construct(
        public readonly int $cmid,
        public readonly string $label,
        public readonly int $timestamp,
    ) {
    }
}
