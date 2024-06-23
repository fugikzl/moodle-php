<?php

declare(strict_types=1);

namespace Fugikzl\Moodle;

use Fugikzl\Moodle\Entities\UserInfo;
use Fugikzl\Moodle\Enums\CourseEnrolledClassification as EnrollClassification;

interface WrapperInterface
{
    public function getUserInfo(): UserInfo;

    /**
     * @param \Fugikzl\Moodle\Enums\CourseEnrolledClassification $classification
     * @return \Fugikzl\Moodle\Entities\Course[]
     */
    public function getUserCourses(EnrollClassification $classification): array;

    /**
     * @param int $courseId
     * @param null|int $userId
     * @return \Fugikzl\Moodle\Entities\Grade[]
     */
    public function getCourseGrades(int $courseId, ?int $userId = null): array;

    /**
    * Receives deadline from moodle api
    * @param null|int $from - time start
    * @param null|int $to - time due
    * @return \Fugikzl\Moodle\Entities\Event[]
    */
    public function getEvents(?int $from = null, ?int $to = null, bool $withAssignments = true): array;

    /**
     * @param int $courseId
     * @param bool $withGrades
     * @return \Fugikzl\Moodle\Entities\Assignment[]
     */
    public function getCourseAssignments(int $courseId, bool $withGrades = true): array;

    /**
     * @param $courseIds
     * @return \Fugikzl\Moodle\Entities\Assignment[]
     */
    public function getCoursesAssignments(int ...$courseIds): array;
}
