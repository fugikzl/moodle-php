<?php

namespace Fugikzl\MoodleWrapper;

use Exception;

class Moodle
{
    private function constructRequest(string $wsFunction, array $params = []): Request
    {
        return new Request($this->wsToken, $wsFunction, $this->webservicesUrl, $params);
    }

    private function ensureUserIdIsSet()
    {
        if ($this->userId === null) {
            throw new Exception("User Id is required. Attribure userId is required for that operation.");
        }
    }

    public function __construct(
        private string $webservicesUrl,
        private string $wsToken,
        private ?int $userId = null
    ) {
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getUserInfo(): array
    {
        $request = $this->constructRequest("core_webservice_get_site_info");

        return $request->send();
    }

    public function getCalendarActionByTimesort(int $timeSortFrom, int $timeSortTo): array
    {
        $this->ensureUserIdIsSet();
        $request = $this->constructRequest("core_calendar_get_action_events_by_timesort", [
            "timesortfrom" => $timeSortFrom,
            "timesortto" => $timeSortTo
        ]);

        return $request->send();
    }

    public function getUserCourses(): array
    {
        $this->ensureUserIdIsSet();
        $request = $this->constructRequest("core_enrol_get_users_courses", ["userid" => $this->userId]);

        return $request->send();
    }

    public function getCoursesInfo(int $courseid): array
    {
        $request = $this->constructRequest("core_course_get_contents", ["courseid" => $courseid]);

        return $request->send();
    }

    public function getUserCoursesGrade(): array
    {
        $this->ensureUserIdIsSet();
        $request = $this->constructRequest("gradereport_overview_get_course_grades", ["userid" => $this->userId]);

        return $request->send();
    }

    public function getCourseGrades(int $courseid): array
    {
        $this->ensureUserIdIsSet();
        $request = $this->constructRequest("gradereport_user_get_grade_items", [
            "courseid" => $courseid,
            "userid" => $this->userId
        ]);

        return $request->send();
    }

    public function getAssignmentsByCourse(int $courseid): array
    {
        $request = $this->constructRequest("gradereport_user_get_grade_items", [
            "courseids[0]" => $courseid,
        ]);

        return $request->send();
    }

    public function getAssignmentsByCourses(int $courseid, array $courseids): array
    {
        $param = [];
        foreach($courseids as $i => $id) {
            $param["courseids[$i]"] = $id;
        }

        $request = $this->constructRequest("gradereport_user_get_grade_items", $param);

        return $request->send();
    }

    public function getUserPreferences(): array
    {
        $request = $this->constructRequest("core_user_get_user_preferences");
        return $request->send();
    }

    public function getEnrolledCoursesByTimelineClassification(string $classification = "inprogress"): array
    {
        $allowed = ["future", "inprogress", "past"];
        if(!in_array($classification, $allowed)) {
            throw new Exception("Classification is invalid");
        }
        $request = $this->constructRequest("core_course_get_enrolled_courses_by_timeline_classification", ["classification" => $classification]);
        return $request->send();
    }

    public function uploadFile(string $filePath, string $fileName, string $url): array
    {
        $body = [
            [
                'name'     => 'name',
                'contents' => 'file'
            ],
            [
                'name'     => 'filename',
                'contents' => $fileName
            ],
            [
                'name'     => 'filearea',
                'contents' => 'private'
            ],
            [
                'name'     => 'file',
                'contents' => fopen($filePath, 'r')
            ],
        ];
        $params = ["userid" => $this->userId, "filearea" => "private"];
        $request = new UploadFileRequest($this->wsToken, $url, $params, $body);
        $response = $request->send();

        return $response;
    }

    public function submitAssignmentWithFile(int $assignmentId, int $fileItemId): array
    {
        $params = [
            'plugindata[files_filemanager]' => $fileItemId,
            "assignmentid" => $assignmentId
        ];

        $request = $this->constructRequest("mod_assign_save_submission", $params);

        return $request->send();
    }

    /**
     * https://github.com/totara/moodle/blob/master/mod/assign/externallib.php#L253
     */
    public function getAssignments(array $coursesId = []): array
    {
        $request = $this->constructRequest("mod_assign_get_assignments", ["courseids" => $coursesId, "capabilities" => ["mod/assign:submit"]]);

        return $request->send();
    }

    /**
     * NOT TESTED
     */
    public function viewAssign(): array
    {
        $request = $this->constructRequest("mod_assign_view_assign", []);

        return $request->send();
    }

    /**
     * NOT TESTED
     */
    public function searchCourses(string $search): array
    {
        $request = $this->constructRequest("core_course_search_courses", ["criterianame" => "title", "criteriavalue" => $search]);

        return $request->send();
    }
}
