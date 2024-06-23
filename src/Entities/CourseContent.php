<?php

declare(strict_types=1);

namespace Fugikzl\Moodle\Entities;

final class CourseContent extends BaseEntity
{
    /**
     * @param int $id
     * @param string $name
     * @param int $visible
     * @param int $section
     * @param bool $uservisible
     * @param int $summaryformat
     * @param int $hiddenbynumsections
     * @param null|CourseModule[] $modules
     * @param null|string $summary
     */
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly int $visible,
        public readonly int $section,
        public readonly bool $uservisible,
        public readonly int $summaryformat,
        public readonly int $hiddenbynumsections,
        public readonly ?array $modules = null,
        public readonly ?string $summary = null,
    ) {
    }
}
