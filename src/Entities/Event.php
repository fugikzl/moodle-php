<?php

declare(strict_types=1);

namespace Fugikzl\Moodle\Entities;

final class Event extends BaseEntity
{
    /**
     * @param int $event_id
     * @param int $timestart
     * @param int $instance
     * @param string $name
     * @param bool $visible
     * @param int $course_id
     * @param string $course_name
     */
    public function __construct(
        public readonly int $event_id,
        public readonly int $timestart,
        public readonly int $instance,
        public readonly string $name,
        public readonly bool $visible,
        public readonly int $course_id,
        public readonly string $course_name,
        public readonly ?Assignment $assignment = null
    ) {
    }
}
