<?php

declare(strict_types=1);

namespace Fugikzl\Moodle;

use Fugikzl\Moodle\Client\Client;
use Fugikzl\Moodle\Entities\Assignment;
use Fugikzl\Moodle\Entities\Course;
use Fugikzl\Moodle\Entities\Event;
use Fugikzl\Moodle\Entities\Grade;
use Fugikzl\Moodle\Entities\IntroAttachment;
use Fugikzl\Moodle\Entities\UserInfo;
use Fugikzl\Moodle\Enums\CourseEnrolledClassification as EnrollClassification;

final class Wrapper implements WrapperInterface
{
    private const TIME_WEEK = 604800;

    /**
     * @param Client $client
     */
    public function __construct(
        private Client $client
    ) {
    }

    /**
     * @return \Fugikzl\Moodle\Entities\UserInfo
     */
    public function getUserInfo(): UserInfo
    {
        $assocUserInfo = $this->client->getUserInfo();
        return new UserInfo(
            moodleId: $assocUserInfo['userid'],
            username: $assocUserInfo['username'],
            fullname: $assocUserInfo['fullname'],
            firstname: $assocUserInfo['firstname'],
            lastname: $assocUserInfo['lastname'],
            sitename: $assocUserInfo['sitename'],
            lang: $assocUserInfo['lang'],
            siteurl: $assocUserInfo['siteurl'],
            userpictureurl: $assocUserInfo['userpictureurl'],
        );
    }

    /**
     * @param \Fugikzl\Moodle\Enums\CourseEnrolledClassification $classification
     * @return \Fugikzl\Moodle\Entities\Course[]
     */
    public function getUserCourses(EnrollClassification $classification = EnrollClassification::INPROGRESS): array
    {
        $coursesRaw = $this->client->getEnrolledCoursesByTimelineClassification($classification->value)["courses"];
        $courses = [];

        foreach($coursesRaw as $course) {
            $courses[] = new Course(
                course_id: (int)$course["id"],
                url: $course["viewurl"],
                coursecategory: $course["coursecategory"],
                name: $course["fullname"] ?? $course["shortname"],
                end_date: $course["startdate"],
                start_date: $course["enddate"],
            );
        }

        return $courses;
    }

    /**
     * @param int $courseId
     * @param null|int $userId
     * @return \Fugikzl\Moodle\Entities\Grade[]
     */
    public function getCourseGrades(int $courseId, ?int $userId = null): array
    {
        $rawGrades = $this->client->getCourseGrades($courseId);
        $grades = [];

        foreach($rawGrades["usergrades"][0]['gradeitems'] as $gradeitem) {
            $gradeitem['percentageformatted'] = str_replace([' ', '%'], '', $gradeitem['percentageformatted']);
            $grade = is_numeric($gradeitem['percentageformatted'])
                ? (int) $gradeitem['percentageformatted']
                : null;

            $grades[] = new Grade(
                grade_id: $gradeitem['id'],
                cmid: $gradeitem['cmid'] ?? null,
                name: $gradeitem['itemname'] ?? "",
                percentage: $grade,
                moodle_id: $userId ?? $this->client->getUserId(),
                itemtype: $gradeitem['itemtype'],
                itemmodule: $gradeitem['itemmodule'],
                iteminstance: $gradeitem['iteminstance'],
                grademin: $gradeitem['grademin'],
                grademax: $gradeitem['grademax'],
                feedbackformat: $gradeitem['feedbackformat'],
                graderaw: $gradeitem['graderaw'],
                feedback: $gradeitem['feedback'],
            );
        }

        return $grades;
    }

    /**
     * Receives deadline from moodle api
     * @param null|int $from - time start
     * @param null|int $to - time due
     * @return \Fugikzl\Moodle\Entities\Event[]
     */
    public function getEvents(?int $from = null, ?int $to = null, bool $withAssignments = true): array
    {
        $from ??= time();
        $to ??= time() + self::TIME_WEEK * 4;

        $deadlines = $this->client->getCalendarActionByTimesort($from, $to);
        $events = [];
        $courses = [];

        foreach($deadlines["events"] as $event) {
            $events[] = new Event(
                event_id: $event['id'],
                name: $event['name'],
                instance: $event['instance'],
                timestart: $event['timestart'],
                visible: (bool)$event['visible'],
                course_name: $event["course"]["shortname"] ?? $event["course"]["fullname"],
                course_id: $event['course']['id']
            );
            $courses[$event['course']['id']] = true;
        }

        if($withAssignments) {
            $assignments = $this->getCoursesAssignments(...array_keys($courses));
            $assignmentsByCmid = [];
            foreach($assignments as $assignment) {
                $assignmentsByCmid[$assignment->cmid] = $assignment;
            }

            foreach($events as &$event) {
                if(isset($assignmentsByCmid[$event->instance])) {
                    $event = $event->with(assignment: $assignmentsByCmid[$event->instance]);
                }
            }
        }
        return $events;
    }

    /**
     * @param int $courseId
     * @param bool $withGrades
     * @return \Fugikzl\Moodle\Entities\Assignment[]
     */
    public function getCourseAssignments(int $courseId, bool $withGrades = true): array
    {
        /**
         * @var \Fugikzl\Moodle\Entities\Assignment[]
         */
        $assignments = [];
        foreach($this->client->getAssignments([$courseId])['courses'][0]['assignments'] as $assignment) {
            $assignments[] = new Assignment(
                assignment_id: $assignment['id'],
                course_id: $courseId,
                cmid: $assignment['cmid'],
                name: $assignment['name'],
                nosubmissions: (bool) $assignment['nosubmissions'],
                allowsubmissionsfromdate: (int) $assignment['allowsubmissionsfromdate'],
                duedate: (int) $assignment['duedate'],
                grade: (int) $assignment['grade'],
                intro: $assignment['intro'],
                introformat: (int) $assignment['introformat'],
                introattachments: array_map(function ($introattachment): IntroAttachment {
                    return new IntroAttachment(
                        filename: $introattachment['filename'],
                        filepath: $introattachment['filepath'],
                        filesize: $introattachment['filesize'],
                        fileurl: $introattachment['fileurl'],
                        timemodified: $introattachment['timemodified'],
                        mimetype: $introattachment['mimetype'],
                        isexternalfile: $introattachment['isexternalfile']
                    );
                }, $assignment['introattachments'])
            );
        }

        if($withGrades) {
            $grades = $this->getCourseGrades($courseId);
            /**
             * @var \Fugikzl\Moodle\Entities\Grade[]
             */
            $gradesByCmid = [];
            foreach($grades as $grade) {
                $gradesByCmid[$grade->cmid] = $grade;
            }

            foreach($assignments as &$assignment) {
                if(isset($gradesByCmid[$assignment->cmid])) {
                    $assignment = $assignment->with(grade: $gradesByCmid[$assignment->cmid]);
                }
            }
        }

        return $assignments;
    }

    /**
     * @param $courseIds
     * @return \Fugikzl\Moodle\Entities\Assignment[]
     */
    public function getCoursesAssignments(int ...$courseIds): array
    {
        $assignments = [];
        foreach($this->client->getAssignments($courseIds)['courses'] as $course) {
            foreach($course['assignments'] as $assignment) {
                $assignments[] = new Assignment(
                    assignment_id: $assignment['id'],
                    course_id: $course['id'],
                    cmid: $assignment['cmid'],
                    name: $assignment['name'],
                    nosubmissions: (bool) $assignment['nosubmissions'],
                    allowsubmissionsfromdate: (int) $assignment['allowsubmissionsfromdate'],
                    duedate: (int) $assignment['duedate'],
                    grade: (int) $assignment['grade'],
                    intro: $assignment['intro'],
                    introformat: (int) $assignment['introformat'],
                    introattachments: array_map(function ($introattachment): IntroAttachment {
                        return new IntroAttachment(
                            filename: $introattachment['filename'],
                            filepath: $introattachment['filepath'],
                            filesize: $introattachment['filesize'],
                            fileurl: urldecode($introattachment['fileurl']),
                            timemodified: $introattachment['timemodified'],
                            mimetype: $introattachment['mimetype'],
                            isexternalfile: $introattachment['isexternalfile']
                        );
                    }, $assignment['introattachments'])
                );
            }
        }

        return $assignments;
    }

}
