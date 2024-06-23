<?php

declare(strict_types=1);

namespace Fugikzl\Moodle\Entities;

final class Grade extends BaseEntity
{
    public function __construct(
        public readonly int $grade_id,
        public readonly string $name,
        public readonly int $moodle_id,
        public readonly string $itemtype,
        public readonly int $grademin,
        public readonly int $grademax,
        public readonly int $feedbackformat,
        public readonly ?string $itemmodule = null,
        public readonly ?int $iteminstance = null,
        public readonly ?int $cmid = null,
        public readonly ?float $graderaw = null,
        public readonly ?string $feedback = null,
        public readonly ?int $percentage = null,
    ) {
    }
}
