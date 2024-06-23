<?php

declare(strict_types=1);

namespace Fugikzl\Moodle\Entities;

final class Course extends BaseEntity
{
    /**
     * @param int $course_id
     * @param string $name
     * @param string $coursecategory
     * @param int $start_date
     * @param int $end_date
     * @param string $url
     */
    public function __construct(
        public readonly int $course_id,
        public readonly string $name,
        public readonly string $coursecategory,
        public readonly int $start_date,
        public readonly int $end_date,
        public readonly string $url,
    ) {
    }
}
